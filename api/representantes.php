<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión a la BD.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'registrar':
        $nombres = sanitize_input($_POST['nombres'] ?? '');
        $apellidos = sanitize_input($_POST['apellidos'] ?? '');
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $parentesco = sanitize_input($_POST['parentesco'] ?? '');
        $profesion = sanitize_input($_POST['profesion'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo = sanitize_input($_POST['correo'] ?? '');

        if (!$nombres || !$cedula || !$parentesco) {
            send_json_response('error', 'Nombres, cédula y parentesco son obligatorios.');
        }

        try {
            $stmt = $conn->prepare("INSERT INTO representantes (nombres, apellidos, cedula, parentesco, profesion, telefono, correo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombres, $apellidos, $cedula, $parentesco, $profesion, $telefono, $correo]);
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Registro Representante', "Registró al representante C.I. $cedula");
            send_json_response('success', 'Representante registrado correctamente.', ['id' => $conn->lastInsertId()]);
        } catch (PDOException $e) {
            send_json_response('error', 'Error al registrar: ' . $e->getMessage());
        }
        break;

    case 'listar':
        try {
            $periodo_id = $_POST['periodo_id'] ?? $_GET['periodo_id'] ?? null;
            if (!$periodo_id) {
                $periodo_id = $conn->query("SELECT id FROM periodos_escolares WHERE estado = 'Abierto' ORDER BY id DESC LIMIT 1")->fetchColumn();
            }

            $query = "
                SELECT r.*, GROUP_CONCAT(DISTINCT CONCAT(e.nombres, ' ', e.apellidos) SEPARATOR ', ') AS estudiantes_asociados
                FROM representantes r
                JOIN estudiante_representante er ON r.id = er.representante_id
                JOIN estudiantes e ON er.estudiante_id = e.id
                JOIN matriculas m ON e.id = m.estudiante_id
                WHERE m.periodo_id = :periodo_id AND m.estado = 'Inscrito'
                GROUP BY r.id
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute([':periodo_id' => $periodo_id]);
            send_json_response('success', 'Lista de representantes', $stmt->fetchAll());
        } catch (PDOException $e) {
            send_json_response('error', 'Error al listar: ' . $e->getMessage());
        }
        break;

        case 'obtener_detalles':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$id) send_json_response('error', 'ID no proporcionado.');
        
        try {
            $stmt = $conn->prepare("SELECT * FROM representantes WHERE id = ?");
            $stmt->execute([$id]);
            $rep = $stmt->fetch();
            
            if (!$rep) send_json_response('error', 'Representante no encontrado.');

            $stmtEst = $conn->prepare("
                SELECT e.* 
                FROM estudiantes e
                JOIN estudiante_representante er ON e.id = er.estudiante_id
                WHERE er.representante_id = ?
            ");
            $stmtEst->execute([$id]);
            $estudiantes = $stmtEst->fetchAll();

            send_json_response('success', 'Detalles obtenidos', ['representante' => $rep, 'estudiantes' => $estudiantes]);
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
