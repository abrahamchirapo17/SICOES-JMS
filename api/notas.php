<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'registrar_literal':
        $matricula_id = sanitize_input($_POST['matricula_id'] ?? '');
        $literal = sanitize_input($_POST['literal'] ?? ''); // A, B, C, D, E

        if (!$matricula_id || !$literal) send_json_response('error', 'ID de matrícula y literal requeridos.');

        try {
            // Usa INSERT ... ON DUPLICATE KEY UPDATE para permitir modificar la nota después
            $stmt = $conn->prepare("INSERT INTO notas_finales (matricula_id, literal) VALUES (?, ?) ON DUPLICATE KEY UPDATE literal = ?");
            $stmt->execute([$matricula_id, $literal, $literal]);
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Registrar Nota', "Asignó literal $literal a la matrícula $matricula_id");
            send_json_response('success', 'Nota registrada exitosamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al registrar nota: ' . $e->getMessage());
        }
        break;
        
    case 'registrar_observacion':
        $matricula_id = sanitize_input($_POST['matricula_id'] ?? '');
        $lapso_id = sanitize_input($_POST['lapso_id'] ?? '');
        $observacion = sanitize_input($_POST['observacion'] ?? '');

        try {
            $stmt = $conn->prepare("INSERT INTO observaciones_lapsos (matricula_id, lapso_id, observacion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE observacion = ?");
            $stmt->execute([$matricula_id, $lapso_id, $observacion, $observacion]);
            send_json_response('success', 'Observación guardada exitosamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al registrar observación: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
