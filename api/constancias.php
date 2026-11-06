<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'generar':
        $matricula_id = sanitize_input($_POST['matricula_id'] ?? '');
        $tipo = sanitize_input($_POST['tipo'] ?? ''); // 'Médica', 'Legal', 'Citación', 'Actividad Extracurricular'

        if (!$matricula_id || !$tipo) send_json_response('error', 'ID de matrícula y tipo requeridos.');

        try {
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $stmt = $conn->prepare("INSERT INTO constancias (matricula_id, tipo, usuario_emisor_id) VALUES (?, ?, ?)");
            $stmt->execute([$matricula_id, $tipo, $usuario_id]);
            
            registrar_log($conn, $usuario_id, 'Generar Constancia', "Emitió constancia $tipo para matrícula $matricula_id");
            send_json_response('success', 'Constancia generada y registrada exitosamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al registrar constancia: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
