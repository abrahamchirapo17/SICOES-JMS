<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'verify_security':
        $username = sanitize_input($_POST['username'] ?? '');
        $respuesta = sanitize_input($_POST['respuesta'] ?? '');

        if (!$username || !$respuesta) {
            send_json_response('error', 'Faltan datos en el formulario.');
        }

        try {
            $stmt = $conn->prepare("SELECT u.id, up.rol_id, u.respuesta_seguridad FROM usuarios u JOIN usuarios_perfiles up ON u.id = up.usuario_id WHERE u.username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                send_json_response('error', 'Usuario no encontrado.');
            }
            if ((int)$user['rol_id'] !== 1) {
                send_json_response('error', 'Solo el administrador principal puede recuperar contraseña por esta vía.');
            }
            if (empty($user['respuesta_seguridad'])) {
                send_json_response('error', 'El administrador no tiene configurada una respuesta de seguridad en la base de datos.');
            }
            if (strtolower(trim($respuesta)) !== strtolower(trim($user['respuesta_seguridad']))) {
                send_json_response('error', 'La respuesta de seguridad es incorrecta.');
            }

            // Guardar autorización en sesión
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['recovery_user_id'] = $user['id'];
            $_SESSION['recovery_username'] = $username;

            send_json_response('success', 'Identidad verificada. Proceda a ingresar la nueva contraseña.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error del servidor.');
        }
        break;

    case 'recovery_reset':
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['recovery_user_id'])) {
            send_json_response('error', 'Acceso no autorizado o sesión expirada. Vuelva a realizar el Paso 1.');
        }

        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$new_password || !$confirm_password) {
            send_json_response('error', 'Faltan datos de la contraseña.');
        }
        if ($new_password !== $confirm_password) {
            send_json_response('error', 'Las contraseñas no coinciden.');
        }

        // Validar regex en backend
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[*,\.+\-\/]).{8,12}$/', $new_password)) {
            send_json_response('error', 'La contraseña no cumple con los requisitos de seguridad.');
        }

        try {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
            $stmt_update->execute([$new_hash, $_SESSION['recovery_user_id']]);

            registrar_log($conn, $_SESSION['recovery_user_id'], 'Recuperar Contraseña', 'El administrador recuperó/cambió su contraseña desde el login.');
            
            // Limpiar la sesión de recuperación
            unset($_SESSION['recovery_user_id']);
            unset($_SESSION['recovery_username']);

            send_json_response('success', 'Contraseña actualizada exitosamente. Ya puede iniciar sesión.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error del servidor.');
        }
        break;

    case 'login':
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!$username || !$password) {
            send_json_response('error', 'Por favor, ingrese usuario y contraseña.');
        }

        try {
            // Rate Limiting Check
            $stmt_check = $conn->prepare("SELECT intentos, TIMESTAMPDIFF(MINUTE, ultimo_intento, CURRENT_TIMESTAMP()) as minutos_transcurridos FROM login_attempts WHERE ip_address = ?");
            $stmt_check->execute([$ip]);
            $attempt = $stmt_check->fetch();

            if ($attempt) {
                $intentos = (int)$attempt['intentos'];
                $minutos_transcurridos = (int)$attempt['minutos_transcurridos'];

                if ($intentos >= 5 && $minutos_transcurridos < 15) {
                    $minutos_restantes = 15 - $minutos_transcurridos;
                    send_json_response('error', "Demasiados intentos fallidos. Por favor, espere $minutos_restantes minutos antes de volver a intentar.");
                }

                // Si pasaron 15 minutos, resetear
                if ($minutos_transcurridos >= 15) {
                    $stmt_reset = $conn->prepare("UPDATE login_attempts SET intentos = 0 WHERE ip_address = ?");
                    $stmt_reset->execute([$ip]);
                }
            }

            $stmt = $conn->prepare("SELECT u.id, u.username, u.password_hash FROM usuarios u WHERE u.username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $stmtProfile = $conn->prepare("SELECT up.estado, r.nombre as rol_nombre, r.id as rol_id FROM usuarios_perfiles up JOIN roles r ON up.rol_id = r.id WHERE up.usuario_id = ?");
                $stmtProfile->execute([$user['id']]);
                $profile = $stmtProfile->fetch();

                if (!$profile || $profile['estado'] !== 'Activo') {
                    send_json_response('error', 'Su cuenta está inactiva o no tiene perfil asignado. Contacte al administrador.');
                }
                // Exito - Resetear rate limit
                $stmt_del = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
                $stmt_del->execute([$ip]);

                // Inicializar variables de sesión
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['rol_id'] = $profile['rol_id'];
                $_SESSION['rol_nombre'] = $profile['rol_nombre'];

                registrar_log($conn, $user['id'], 'Inicio de Sesión', "El usuario $username ha iniciado sesión.");
                send_json_response('success', 'Sesión iniciada correctamente.');
            } else {
                // Registrar intento fallido
                $stmt_fail = $conn->prepare("INSERT INTO login_attempts (ip_address, intentos) VALUES (?, 1) ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = CURRENT_TIMESTAMP()");
                $stmt_fail->execute([$ip]);
                send_json_response('error', 'Usuario o contraseña incorrectos.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Error del servidor: ' . $e->getMessage());
        }
        break;

    case 'logout':
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        if ($usuario_id) {
            registrar_log($conn, $usuario_id, 'Cierre de Sesión', "El usuario ha cerrado sesión.");
        }
        session_destroy();
        send_json_response('success', 'Sesión cerrada correctamente.');
        break;

    case 'crear_usuario':
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol_id = sanitize_input($_POST['rol_id'] ?? '');

        if (!$username || !$password || !$rol_id) {
            send_json_response('error', 'Usuario, contraseña y rol son obligatorios.');
        }

        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            $conn->beginTransaction();
            $stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $password_hash]);
            $usuario_id = $conn->lastInsertId();
            
            $stmtPerf = $conn->prepare("INSERT INTO usuarios_perfiles (usuario_id, rol_id, estado) VALUES (?, ?, 'Activo')");
            $stmtPerf->execute([$usuario_id, $rol_id]);
            $conn->commit();
            
            $stmtRol = $conn->prepare("SELECT nombre FROM roles WHERE id = ?");
            $stmtRol->execute([$rol_id]);
            $rol_nombre = $stmtRol->fetchColumn() ?: 'Desconocido';
            
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Crear Usuario', "Creó el usuario $username con rol de $rol_nombre");
            send_json_response('success', 'Usuario creado exitosamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al crear usuario: ' . $e->getMessage());
        }
        break;

    case 'listar':
        try {
            $stmt = $conn->query("SELECT u.id, u.username, u.estado, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id");
            send_json_response('success', 'Lista de usuarios', $stmt->fetchAll());
        } catch (PDOException $e) {
            send_json_response('error', 'Error al listar usuarios: ' . $e->getMessage());
        }
        break;

    case 'listar_logs':
        try {
            $stmt = $conn->query("SELECT l.id, u.username, l.accion, l.detalle, l.fecha, l.ip FROM log_actividades l LEFT JOIN usuarios u ON l.usuario_id = u.id ORDER BY l.fecha DESC LIMIT 100");
            send_json_response('success', 'Logs de auditoría', $stmt->fetchAll());
        } catch (PDOException $e) {
            send_json_response('error', 'Error al obtener logs: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
