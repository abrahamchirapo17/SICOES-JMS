<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

try {
    $data = [];

    // Estudiantes Totales Activos
    $data['estudiantes'] = $conn->query("SELECT COUNT(*) FROM matriculas WHERE estado = 'Inscrito'")->fetchColumn();

    // Docentes Totales
    $data['docentes'] = $conn->query("SELECT COUNT(*) FROM docentes")->fetchColumn();

    // Periodo Escolar Abierto
    $periodo = $conn->query("SELECT nombre FROM periodos_escolares WHERE estado = 'Abierto' ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($periodo) {
        $periodo = str_ireplace('periodo', 'Período', $periodo);
    }
    $data['periodo'] = $periodo ? $periodo : 'No definido';

    // Distribución por Niveles
    $niveles = $conn->query("
        SELECT n.nombre as nivel, COUNT(m.id) as total 
        FROM matriculas m
        JOIN grados g ON m.grado_id = g.id
        JOIN niveles n ON g.nivel_id = n.id
        WHERE m.estado = 'Inscrito'
        GROUP BY n.id
    ")->fetchAll(PDO::FETCH_ASSOC);
    $data['distribucion_niveles'] = $niveles;

    // Actividad Reciente (últimos 6 registros)
    $actividad = $conn->query("
        SELECT l.accion, l.detalle, l.fecha_accion, u.username as usuario 
        FROM log_actividades l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        ORDER BY l.fecha_accion DESC 
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);
    $data['actividad_reciente'] = $actividad;

    send_json_response('success', 'Stats cargados', $data);

} catch (PDOException $e) {
    send_json_response('error', 'Error al cargar stats: ' . $e->getMessage());
}
?>
