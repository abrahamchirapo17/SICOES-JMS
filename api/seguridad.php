<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'listar_usuarios':
        try {
            $stmt = $conn->query("
                SELECT u.id, u.username, up.estado, up.created_at, r.nombre as rol
                FROM usuarios u
                JOIN usuarios_perfiles up ON u.id = up.usuario_id
                LEFT JOIN roles r ON up.rol_id = r.id
                ORDER BY u.id ASC
            ");
            send_json_response('success', 'Usuarios cargados', $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch(PDOException $e) {
            send_json_response('error', $e->getMessage());
        }
        break;

    case 'crear_usuario':
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol_id = intval($_POST['rol_id'] ?? 0);

        if (!$username || !$password || !$rol_id) {
            send_json_response('error', 'Faltan campos obligatorios');
        }

        try {
            // Verificar duplicados
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                send_json_response('error', 'El nombre de usuario ya está en uso');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $conn->beginTransaction();
            $stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $usuario_id = $conn->lastInsertId();
            
            $stmtPerf = $conn->prepare("INSERT INTO usuarios_perfiles (usuario_id, rol_id, estado) VALUES (?, ?, 'Activo')");
            $stmtPerf->execute([$usuario_id, $rol_id]);
            $conn->commit();
            
            $stmtRol = $conn->prepare("SELECT nombre FROM roles WHERE id = ?");
            $stmtRol->execute([$rol_id]);
            $rol_nombre = $stmtRol->fetchColumn() ?: 'Desconocido';
            
            registrar_log($conn, $_SESSION['usuario_id'], 'Crear Usuario', "Creó al usuario $username con rol de $rol_nombre");
            send_json_response('success', 'Usuario creado exitosamente');

        } catch(PDOException $e) {
            send_json_response('error', $e->getMessage());
        }
        break;

    case 'cambiar_estado':
        $id = intval($_POST['id'] ?? 0);
        $nuevo_estado = $_POST['estado'] ?? ''; // 'Activo' o 'Inactivo'

        if ($id === 1) {
            send_json_response('error', 'No se puede desactivar al Administrador Principal');
        }

        if (!$id || !in_array($nuevo_estado, ['Activo', 'Inactivo'])) {
            send_json_response('error', 'Datos inválidos');
        }

        try {
            $stmt = $conn->prepare("UPDATE usuarios_perfiles SET estado = ? WHERE usuario_id = ?");
            $stmt->execute([$nuevo_estado, $id]);
            registrar_log($conn, $_SESSION['usuario_id'], 'Estado Usuario', "Cambió el estado del usuario ID $id a $nuevo_estado");
            send_json_response('success', 'Estado actualizado');
        } catch(PDOException $e) {
            send_json_response('error', $e->getMessage());
        }
        break;

    case 'resetear_clave':
        $id = intval($_POST['id'] ?? 0);
        $nueva_clave = trim($_POST['password'] ?? '');

        if (!$id || !$nueva_clave) {
            send_json_response('error', 'Faltan datos');
        }

        try {
            $hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $id]);
            registrar_log($conn, $_SESSION['usuario_id'], 'Reset Clave', "Cambió la clave del usuario ID $id");
            send_json_response('success', 'Contraseña actualizada');
        } catch(PDOException $e) {
            send_json_response('error', $e->getMessage());
        }
        break;

    case 'listar_roles':
        try {
            $stmt = $conn->query("SELECT * FROM roles WHERE nombre IN ('Observador', 'Secretaria') ORDER BY id ASC");
            send_json_response('success', 'Roles cargados', $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch(PDOException $e) {
            send_json_response('error', $e->getMessage());
        }
        break;

    case 'crear_rol':
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$nombre) {
            send_json_response('error', 'El nombre del rol es obligatorio');
        }

        try {
            $stmt = $conn->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            registrar_log($conn, $_SESSION['usuario_id'], 'Crear Rol', "Creó el rol $nombre");
            send_json_response('success', 'Rol creado exitosamente');
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                send_json_response('error', 'Ya existe un rol con ese nombre');
            }
            send_json_response('error', $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción inválida');
}
