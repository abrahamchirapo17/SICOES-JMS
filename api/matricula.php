<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Obtener el ID del periodo activo
$periodoActivoId = $conn->query("SELECT id FROM periodos_escolares WHERE estado = 'Abierto' ORDER BY id DESC LIMIT 1")->fetchColumn();

switch ($action) {
    case 'listar_aulas':
        try {
            $query = "
                SELECT 
                    n.nombre as nivel,
                    g.id as grado_id,
                    g.nombre as grado,
                    s.id as seccion_id,
                    s.nombre as seccion,
                    t.id as turno_id,
                    t.nombre as turno,
                    (
                        SELECT COUNT(m.id) 
                        FROM matriculas m 
                        WHERE m.grado_id = g.id 
                          AND m.seccion_id = s.id 
                          AND m.turno_id = t.id 
                          AND m.periodo_id = :periodo_id
                          AND m.estado = 'Inscrito'
                    ) as inscritos,
                    (
                        SELECT CONCAT(d.nombres, ' ', d.apellidos)
                        FROM docente_asignacion da
                        JOIN docentes d ON da.docente_id = d.id
                        WHERE da.grado_id = g.id 
                          AND da.seccion_id = s.id 
                          AND da.periodo_id = :periodo_id2
                        LIMIT 1
                    ) as docente_asignado
                FROM grados_secciones_activas gsa
                JOIN grados g ON gsa.grado_id = g.id
                JOIN niveles n ON g.nivel_id = n.id
                JOIN secciones s ON gsa.seccion_id = s.id
                JOIN turnos t ON gsa.turno_id = t.id
                ORDER BY n.id, t.id, g.id, s.nombre
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':periodo_id' => $periodoActivoId,
                ':periodo_id2' => $periodoActivoId
            ]);
            $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            send_json_response('success', 'Aulas obtenidas', $aulas);
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    case 'listar_alumnos':
        $grado_id = $_POST['grado_id'] ?? null;
        $seccion_id = $_POST['seccion_id'] ?? null;
        $turno_id = $_POST['turno_id'] ?? null;

        if (!$grado_id || !$seccion_id || !$turno_id) {
            send_json_response('error', 'Faltan parámetros de aula');
        }

        try {
            $query = "
                SELECT 
                    m.id as matricula_id,
                    e.id as estudiante_id,
                    e.cedula_escolar,
                    e.nombres,
                    e.apellidos,
                    e.sexo,
                    m.condicion,
                    m.estado
                FROM matriculas m
                JOIN estudiantes e ON m.estudiante_id = e.id
                WHERE m.grado_id = :grado_id 
                  AND m.seccion_id = :seccion_id 
                  AND m.turno_id = :turno_id
                  AND m.periodo_id = :periodo_id
                ORDER BY e.apellidos ASC, e.nombres ASC
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':grado_id' => $grado_id,
                ':seccion_id' => $seccion_id,
                ':turno_id' => $turno_id,
                ':periodo_id' => $periodoActivoId
            ]);
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            send_json_response('success', 'Alumnos listados', $alumnos);
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    case 'cambiar_estado':
        if ($_SESSION['rol_id'] != 1) {
            send_json_response('error', 'No tienes permiso para modificar estatus.');
            break;
        }

        $matricula_id = $_POST['matricula_id'] ?? null;
        $nuevo_estado = $_POST['estado'] ?? null;

        if (!$matricula_id || !$nuevo_estado) {
            send_json_response('error', 'Faltan datos requeridos.');
        }

        try {
            // Obtener el nombre del estudiante para el log
            $stmtNombre = $conn->prepare("SELECT e.nombres, e.apellidos FROM matriculas m JOIN estudiantes e ON m.estudiante_id = e.id WHERE m.id = :id");
            $stmtNombre->execute([':id' => $matricula_id]);
            $estudianteInfo = $stmtNombre->fetch(PDO::FETCH_ASSOC);
            $nombre_estudiante = $estudianteInfo ? $estudianteInfo['nombres'] . ' ' . $estudianteInfo['apellidos'] : "Matrícula #$matricula_id";

            $stmt = $conn->prepare("UPDATE matriculas SET estado = :estado WHERE id = :id");
            $stmt->execute([
                ':estado' => $nuevo_estado,
                ':id' => $matricula_id
            ]);

            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Cambio de Estatus de Matrícula', "Actualizó el estado de $nombre_estudiante a $nuevo_estado");

            send_json_response('success', 'Estado actualizado correctamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida');
}
?>
