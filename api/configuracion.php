<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'crear_periodo':
        $nombre = sanitize_input($_POST['nombre'] ?? '');
        $nombre = str_ireplace('periodo', 'Período', $nombre);
        $fecha_inicio = sanitize_input($_POST['fecha_inicio'] ?? '');
        $fecha_fin = sanitize_input($_POST['fecha_fin'] ?? '');

        if ($nombre === '' || $fecha_inicio === '' || $fecha_fin === '') {
            send_json_response('error', 'Faltan datos del período.');
        }

        try {
            $stmt = $conn->prepare("INSERT INTO periodos_escolares (nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $fecha_inicio, $fecha_fin]);
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Crear Período', "Creó período $nombre");
            send_json_response('success', 'Período escolar creado exitosamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    case 'crear_lapso':
        $periodo_id = sanitize_input($_POST['periodo_id'] ?? '');
        $numero = sanitize_input($_POST['numero'] ?? '');
        $fecha_inicio = sanitize_input($_POST['fecha_inicio'] ?? '');
        $fecha_fin = sanitize_input($_POST['fecha_fin'] ?? '');

        if ($periodo_id === '' || $numero === '' || $fecha_inicio === '' || $fecha_fin === '') {
            send_json_response('error', 'Todos los campos del lapso son obligatorios.');
        }

        try {
            $stmt = $conn->prepare("INSERT INTO lapsos (periodo_id, numero, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)");
            $stmt->execute([$periodo_id, $numero, $fecha_inicio, $fecha_fin]);
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Crear Lapso', "Creó lapso $numero para el período $periodo_id");
            send_json_response('success', 'Lapso académico creado exitosamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al crear lapso: ' . $e->getMessage());
        }
        break;

    case 'listar_estructura':
        try {
            // Pre-poblar niveles si está vacío
            $niveles = $conn->query("SELECT * FROM niveles")->fetchAll();
            if (empty($niveles)) {
                $conn->query("INSERT INTO niveles (nombre) VALUES ('Preescolar'), ('Primaria')");
                $niveles = $conn->query("SELECT * FROM niveles")->fetchAll();
            }

            $data = [];
            $data['periodos'] = $conn->query("SELECT * FROM periodos_escolares ORDER BY fecha_inicio DESC")->fetchAll();
            $data['lapsos'] = $conn->query("SELECT * FROM lapsos ORDER BY periodo_id DESC, numero ASC")->fetchAll();
            $data['niveles'] = $niveles;
            $data['grados'] = $conn->query("SELECT * FROM grados")->fetchAll();
            $data['secciones'] = $conn->query("SELECT * FROM secciones")->fetchAll();
            
            // Tabla de mapeo
            $conn->query("CREATE TABLE IF NOT EXISTS grados_secciones_activas (grado_id INT, seccion_id INT, PRIMARY KEY(grado_id, seccion_id))");
            try {
                $conn->query("ALTER TABLE grados_secciones_activas ADD COLUMN turno_id INT DEFAULT 1");
                $conn->query("ALTER TABLE grados_secciones_activas DROP PRIMARY KEY, ADD PRIMARY KEY(grado_id, seccion_id, turno_id)");
            } catch(Exception $e) { /* Ya existe */ }

            $data['combinaciones'] = $conn->query("
                SELECT g.nivel_id, g.nombre AS grado, s.nombre AS seccion, t.nombre AS turno
                FROM grados_secciones_activas gsa 
                JOIN grados g ON gsa.grado_id = g.id 
                JOIN secciones s ON gsa.seccion_id = s.id
                LEFT JOIN turnos t ON gsa.turno_id = t.id
                ORDER BY g.nivel_id, t.nombre, g.nombre, s.nombre
            ")->fetchAll();

            try {
                // Si la tabla turnos no existe aún, esto no fallará brutalmente
                $data['turnos'] = $conn->query("SELECT * FROM turnos")->fetchAll();
            } catch(Exception $e) {
                $data['turnos'] = [];
            }
            send_json_response('success', 'Estructura académica', $data);
        } catch (PDOException $e) {
            send_json_response('error', 'Error al listar: ' . $e->getMessage());
        }
        break;

    case 'crear_grado_seccion':
        $nivel_id = sanitize_input($_POST['nivel_id'] ?? '');
        $grado_nombre = sanitize_input($_POST['grado_nombre'] ?? '');
        $seccion_nombre = sanitize_input($_POST['seccion_nombre'] ?? '');
        $turno_id = sanitize_input($_POST['turno_id'] ?? '');

        if ($nivel_id === '' || $grado_nombre === '' || $seccion_nombre === '' || $turno_id === '') send_json_response('error', 'Datos incompletos.');
        try {
            $conn->query("CREATE TABLE IF NOT EXISTS grados_secciones_activas (grado_id INT, seccion_id INT, PRIMARY KEY(grado_id, seccion_id))");
            try {
                $conn->query("ALTER TABLE grados_secciones_activas ADD COLUMN turno_id INT DEFAULT 1");
                $conn->query("ALTER TABLE grados_secciones_activas DROP PRIMARY KEY, ADD PRIMARY KEY(grado_id, seccion_id, turno_id)");
            } catch(Exception $e) {}

            // Insertar o ignorar Grado
            $stmt = $conn->prepare("INSERT IGNORE INTO grados (nivel_id, nombre) VALUES (?, ?)");
            $stmt->execute([$nivel_id, $grado_nombre]);
            $grado_id = $conn->query("SELECT id FROM grados WHERE nivel_id = $nivel_id AND nombre = '$grado_nombre'")->fetchColumn();

            // Insertar o ignorar Sección
            $stmt2 = $conn->prepare("INSERT IGNORE INTO secciones (nombre) VALUES (?)");
            $stmt2->execute([$seccion_nombre]);
            $seccion_id = $conn->query("SELECT id FROM secciones WHERE nombre = '$seccion_nombre'")->fetchColumn();

            // Verificar si la combinación ya existe
            $existe = $conn->query("SELECT 1 FROM grados_secciones_activas WHERE grado_id = $grado_id AND seccion_id = $seccion_id AND turno_id = $turno_id")->fetchColumn();
            if ($existe) {
                send_json_response('error', 'nivel/grado ya registrado');
                exit;
            }

            // Registrar la combinación
            $stmt3 = $conn->prepare("INSERT INTO grados_secciones_activas (grado_id, seccion_id, turno_id) VALUES (?, ?, ?)");
            $stmt3->execute([$grado_id, $seccion_id, $turno_id]);

            send_json_response('success', "Añadido '$grado_nombre Sección $seccion_nombre' correctamente.");
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
