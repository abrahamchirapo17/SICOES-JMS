<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) send_json_response('error', 'Error de conexión a la BD.');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'registrar':
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $nombres = sanitize_input($_POST['nombres'] ?? '');
        $apellidos = sanitize_input($_POST['apellidos'] ?? '');
        $grado_instruccion = sanitize_input($_POST['grado_instruccion'] ?? '');
        $especialidad = sanitize_input($_POST['especialidad'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo = sanitize_input($_POST['correo'] ?? '');
        $direccion = sanitize_input($_POST['direccion'] ?? '');
        $turno_id = sanitize_input($_POST['turno_id'] ?? '');
        $tiene_patologia = sanitize_input($_POST['tiene_patologia'] ?? 'No');
        $cual_patologia = sanitize_input($_POST['cual_patologia'] ?? '');
        $patologia = $tiene_patologia === 'Si' ? 'Si - ' . $cual_patologia : 'No';
        
        $talla_camisa = sanitize_input($_POST['talla_camisa'] ?? '');
        $talla_pantalon = sanitize_input($_POST['talla_pantalon'] ?? '');
        $talla_zapato = sanitize_input($_POST['talla_zapato'] ?? '');
        $edad = sanitize_input($_POST['edad'] ?? '');
        $peso = sanitize_input($_POST['peso'] ?? '');
        $estatura = sanitize_input($_POST['estatura'] ?? '');

        if (!$cedula || !$nombres || !$apellidos) {
            send_json_response('error', 'Campos obligatorios incompletos.');
        }

        try {
            $stmt = $conn->prepare("INSERT INTO docentes (cedula, nombres, apellidos, grado_instruccion, especialidad, telefono_personal, correo, direccion, turno_id, patologia, talla_camisa, talla_pantalon, talla_zapato, edad, peso, estatura) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cedula, $nombres, $apellidos, $grado_instruccion, $especialidad, $telefono, $correo, $direccion, $turno_id ?: null, $patologia, $talla_camisa, $talla_pantalon, $talla_zapato, $edad ?: null, $peso ?: null, $estatura ?: null]);
            
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Registro Docente', "Se registró al docente $nombres $apellidos C.I. $cedula");
            send_json_response('success', 'Docente registrado correctamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al registrar: ' . $e->getMessage());
        }
        break;

    case 'editar':
        $id = intval($_POST['docente_id'] ?? 0);
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        $nombres = sanitize_input($_POST['nombres'] ?? '');
        $apellidos = sanitize_input($_POST['apellidos'] ?? '');
        $grado_instruccion = sanitize_input($_POST['grado_instruccion'] ?? '');
        $especialidad = sanitize_input($_POST['especialidad'] ?? '');
        $telefono = sanitize_input($_POST['telefono'] ?? '');
        $correo = sanitize_input($_POST['correo'] ?? '');
        $direccion = sanitize_input($_POST['direccion'] ?? '');
        $turno_id = sanitize_input($_POST['turno_id'] ?? '');
        $tiene_patologia = sanitize_input($_POST['tiene_patologia'] ?? 'No');
        $cual_patologia = sanitize_input($_POST['cual_patologia'] ?? '');
        $patologia = $tiene_patologia === 'Si' ? 'Si - ' . $cual_patologia : 'No';
        
        $talla_camisa = sanitize_input($_POST['talla_camisa'] ?? '');
        $talla_pantalon = sanitize_input($_POST['talla_pantalon'] ?? '');
        $talla_zapato = sanitize_input($_POST['talla_zapato'] ?? '');
        $edad = sanitize_input($_POST['edad'] ?? '');
        $peso = sanitize_input($_POST['peso'] ?? '');
        $estatura = sanitize_input($_POST['estatura'] ?? '');

        if (!$id || !$cedula || !$nombres || !$apellidos) {
            send_json_response('error', 'Campos obligatorios incompletos.');
        }

        try {
            $stmt = $conn->prepare("UPDATE docentes SET cedula=?, nombres=?, apellidos=?, grado_instruccion=?, especialidad=?, telefono_personal=?, correo=?, direccion=?, turno_id=?, patologia=?, talla_camisa=?, talla_pantalon=?, talla_zapato=?, edad=?, peso=?, estatura=? WHERE id=?");
            $stmt->execute([$cedula, $nombres, $apellidos, $grado_instruccion, $especialidad, $telefono, $correo, $direccion, $turno_id ?: null, $patologia, $talla_camisa, $talla_pantalon, $talla_zapato, $edad ?: null, $peso ?: null, $estatura ?: null, $id]);
            
            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Edición Docente', "Actualizó al docente C.I. $cedula");
            send_json_response('success', 'Docente actualizado correctamente.');
        } catch (PDOException $e) {
            send_json_response('error', 'Error al actualizar: ' . $e->getMessage());
        }
        break;

    case 'listar':
        try {
            $docentes = $conn->query("
                SELECT d.*, t.nombre as turno_nombre, 
                (SELECT GROUP_CONCAT(CONCAT(g.nombre, ' \"', s.nombre, '\"')) 
                 FROM docente_asignacion da 
                 JOIN grados g ON da.grado_id = g.id 
                 JOIN secciones s ON da.seccion_id = s.id 
                 JOIN periodos_escolares p ON da.periodo_id = p.id
                 WHERE da.docente_id = d.id AND p.estado = 'Abierto'
                ) as clases_asignadas
                FROM docentes d
                LEFT JOIN turnos t ON d.turno_id = t.id
                ORDER BY d.nombres, d.apellidos
            ")->fetchAll();

            $periodos = $conn->query("SELECT id, nombre, estado FROM periodos_escolares ORDER BY id DESC")->fetchAll();
            $grados = $conn->query("SELECT id, nombre FROM grados ORDER BY id")->fetchAll();
            $secciones = $conn->query("SELECT id, nombre FROM secciones ORDER BY id")->fetchAll();
            $turnos = $conn->query("SELECT id, nombre FROM turnos ORDER BY id")->fetchAll();
            $clases_activas = $conn->query("
                SELECT gsa.grado_id, gsa.seccion_id, g.nombre as grado_nombre, s.nombre as seccion_nombre 
                FROM grados_secciones_activas gsa 
                JOIN grados g ON gsa.grado_id = g.id 
                JOIN secciones s ON gsa.seccion_id = s.id
                ORDER BY g.id, s.id
            ")->fetchAll();

            send_json_response('success', 'Lista de docentes', [
                'docentes' => $docentes,
                'periodos' => $periodos,
                'grados' => $grados,
                'secciones' => $secciones,
                'turnos' => $turnos,
                'clases_activas' => $clases_activas
            ]);
        } catch (PDOException $e) {
            send_json_response('error', 'Error al listar: ' . $e->getMessage());
        }
        break;

    case 'obtener_detalles':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) send_json_response('error', 'ID no proporcionado');
        try {
            $stmt = $conn->prepare("SELECT d.*, t.nombre as turno_nombre FROM docentes d LEFT JOIN turnos t ON d.turno_id = t.id WHERE d.id = ?");
            $stmt->execute([$id]);
            $docente = $stmt->fetch();
            if (!$docente) send_json_response('error', 'Docente no encontrado');

            $stmtAsig = $conn->prepare("
                SELECT p.nombre as periodo, g.nombre as grado, s.nombre as seccion
                FROM docente_asignacion da
                JOIN periodos_escolares p ON da.periodo_id = p.id
                JOIN grados g ON da.grado_id = g.id
                JOIN secciones s ON da.seccion_id = s.id
                WHERE da.docente_id = ?
                ORDER BY p.id DESC
            ");
            $stmtAsig->execute([$id]);
            $asignaciones = $stmtAsig->fetchAll();

            send_json_response('success', 'Detalles', ['docente' => $docente, 'asignaciones' => $asignaciones]);
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    case 'asignar_seccion':
        if (($_SESSION['rol_id'] ?? 0) != 1) {
            send_json_response('error', 'Solo los administradores pueden asignar secciones.');
            break;
        }
        $docente_id = sanitize_input($_POST['docente_id'] ?? '');
        $periodo_id = sanitize_input($_POST['periodo_id'] ?? '');
        $grado_id = sanitize_input($_POST['grado_id'] ?? '');
        $seccion_id = sanitize_input($_POST['seccion_id'] ?? '');

        if (!$docente_id || !$periodo_id || !$grado_id || !$seccion_id) {
            send_json_response('error', 'Todos los campos son obligatorios para asignar sección.');
            break;
        }

        try {
            // Verificar si el docente ya tiene una sección asignada en este periodo
            $stmt_check_docente = $conn->prepare("SELECT COUNT(*) FROM docente_asignacion WHERE docente_id = ? AND periodo_id = ?");
            $stmt_check_docente->execute([$docente_id, $periodo_id]);
            if ($stmt_check_docente->fetchColumn() > 0) {
                send_json_response('error', 'Este docente ya tiene una sección asignada en el periodo actual.');
                break;
            }

            $stmt = $conn->prepare("INSERT INTO docente_asignacion (docente_id, periodo_id, grado_id, seccion_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$docente_id, $periodo_id, $grado_id, $seccion_id]);

            $docenteNombre = $conn->query("SELECT CONCAT(nombres, ' ', apellidos) FROM docentes WHERE id = $docente_id")->fetchColumn();
            $gradoNombre = $conn->query("SELECT nombre FROM grados WHERE id = $grado_id")->fetchColumn();
            $seccionNombre = $conn->query("SELECT nombre FROM secciones WHERE id = $seccion_id")->fetchColumn();

            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Asignación Sección', "Se asignó al docente $docenteNombre a $gradoNombre sección $seccionNombre");
            send_json_response('success', 'Sección asignada al docente correctamente.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), '1062') !== false) {
                send_json_response('error', 'Sección ya se encuentra asignada a otro docente.');
            } else {
                send_json_response('error', 'Error al asignar: ' . $e->getMessage());
            }
        }
        break;

    case 'stats_docentes':
        try {
            $total = $conn->query("SELECT COUNT(*) FROM docentes")->fetchColumn();
            
            $turnos_data = $conn->query("
                SELECT t.nombre, COUNT(d.id) as cantidad
                FROM docentes d
                LEFT JOIN turnos t ON d.turno_id = t.id
                GROUP BY t.id
            ")->fetchAll();

            send_json_response('success', 'Estadísticas', ['total' => $total, 'turnos' => $turnos_data]);
        } catch (PDOException $e) {
            send_json_response('error', 'Error: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Acción no válida.');
}
?>
