<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'listas_matricula':
        $periodo_id = sanitize_input($_POST['periodo_id'] ?? $_GET['periodo_id'] ?? '');
        $nivel_id = sanitize_input($_POST['nivel_id'] ?? $_GET['nivel_id'] ?? '');
        $grado_id = sanitize_input($_POST['grado_id'] ?? $_GET['grado_id'] ?? '');
        $seccion_id = sanitize_input($_POST['seccion_id'] ?? $_GET['seccion_id'] ?? '');
        $turno_id = sanitize_input($_POST['turno_id'] ?? $_GET['turno_id'] ?? '');

        if (!$periodo_id || !$nivel_id) send_json_response('error', 'Periodo y Nivel son requeridos.');

        $sql = "SELECT e.cedula_escolar, e.nombres, e.apellidos, g.nombre AS grado, s.nombre AS seccion, m.estado
                FROM matriculas m
                JOIN estudiantes e ON m.estudiante_id = e.id
                JOIN grados g ON m.grado_id = g.id
                JOIN secciones s ON m.seccion_id = s.id
                WHERE m.periodo_id = :periodo AND e.nivel_id = :nivel";
        
        $params = [':periodo' => $periodo_id, ':nivel' => $nivel_id];

        if ($grado_id) {
            $sql .= " AND m.grado_id = :grado";
            $params[':grado'] = $grado_id;
        }
        if ($seccion_id) {
            $sql .= " AND m.seccion_id = :seccion";
            $params[':seccion'] = $seccion_id;
        }
        if ($turno_id) {
            $sql .= " AND m.turno_id = :turno";
            $params[':turno'] = $turno_id;
        }

        $sql .= " ORDER BY e.apellidos ASC";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $lista = $stmt->fetchAll();
            
            send_json_response('success', 'Lista de matrícula generada', $lista);
        } catch (PDOException $e) {
            send_json_response('error', 'Error al generar lista: ' . $e->getMessage() . '. Verifique si actualizó la BD con update_turnos.sql');
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
