<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}

require_once '../conexion.php';
$conexion = new Conexion();
$conn = $conexion->getConexion();

$id = intval($_GET['id'] ?? 0);
if (!$id) die("ID no válido");

$estudiante = $conn->query("
    SELECT e.*, 
           TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad,
           m.condicion, m.estado, m.fecha_inscripcion,
           g.nombre AS grado, s.nombre AS seccion, t.nombre AS turno, n.nombre AS nivel, pe.nombre as periodo
    FROM estudiantes e
    JOIN matriculas m ON e.id = m.estudiante_id
    JOIN grados g ON m.grado_id = g.id
    JOIN secciones s ON m.seccion_id = s.id
    LEFT JOIN turnos t ON m.turno_id = t.id
    JOIN niveles n ON g.nivel_id = n.id
    JOIN periodos_escolares pe ON m.periodo_id = pe.id
    WHERE e.id = $id
    ORDER BY m.id DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if (!$estudiante) die("Estudiante no encontrado");

$representante = $conn->query("
    SELECT r.* 
    FROM representantes r
    JOIN estudiante_representante er ON r.id = er.representante_id
    WHERE er.estudiante_id = $id AND er.es_principal = 1
")->fetch(PDO::FETCH_ASSOC);

$medicos = $conn->query("SELECT * FROM estudiante_antecedentes_medicos WHERE estudiante_id = $id")->fetch(PDO::FETCH_ASSOC);
$vacunas = $conn->query("SELECT * FROM estudiante_vacunas WHERE estudiante_id = $id")->fetch(PDO::FETCH_ASSOC);
$sociales = $conn->query("SELECT * FROM estudiante_sociales WHERE estudiante_id = $id")->fetch(PDO::FETCH_ASSOC);
$preescolar = $conn->query("SELECT * FROM estudiante_preescolar WHERE estudiante_id = $id")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Estudiantil - <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        h2 {
            font-size: 16px;
            background: #eee;
            padding: 5px;
            border: 1px solid #ccc;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        td {
            padding: 5px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 25%;
        }
        .firma-box {
            margin-top: 50px;
            text-align: center;
            width: 100%;
            display: flex;
            justify-content: space-around;
        }
        .firma-linea {
            border-top: 1px solid #000;
            width: 250px;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: bold;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
        .btn-print {
            background: #2563EB;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">Imprimir Ficha</button>

    <div class="header">
        <h1>SICOES JMS</h1>
        <p>FICHA DE INSCRIPCIÓN / EXPEDIENTE ESTUDIANTIL</p>
        <p>Período Escolar: <?= htmlspecialchars($estudiante['periodo']) ?></p>
    </div>

    <table>
        <tr>
            <td class="label">Nombres y Apellidos:</td>
            <td><?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></td>
            <td class="label">Cédula Escolar:</td>
            <td><?= htmlspecialchars($estudiante['cedula_escolar']) ?></td>
        </tr>
        <tr>
            <td class="label">Lugar de Nacimiento:</td>
            <td><?= htmlspecialchars($estudiante['lugar_nacimiento']) ?></td>
            <td class="label">Fecha de Nacimiento:</td>
            <td><?= htmlspecialchars($estudiante['fecha_nacimiento']) ?> (<?= $estudiante['edad'] ?> años)</td>
        </tr>
        <tr>
            <td class="label">Sexo:</td>
            <td><?= htmlspecialchars($estudiante['sexo']) ?></td>
            <td class="label">Grado/Sección:</td>
            <td><?= htmlspecialchars($estudiante['nivel'] . ' - ' . $estudiante['grado'] . ' "' . $estudiante['seccion'] . '"') ?></td>
        </tr>
    </table>

    <h2>1. DATOS DEL REPRESENTANTE PRINCIPAL</h2>
    <?php if ($representante): ?>
    <table>
        <tr>
            <td class="label">Nombres y Apellidos:</td>
            <td><?= htmlspecialchars($representante['nombres'] . ' ' . $representante['apellidos']) ?></td>
            <td class="label">Cédula:</td>
            <td><?= htmlspecialchars($representante['cedula']) ?></td>
        </tr>
        <tr>
            <td class="label">Parentesco:</td>
            <td><?= htmlspecialchars($representante['parentesco'] ?? 'N/A') ?></td>
            <td class="label">Teléfonos:</td>
            <td><?= htmlspecialchars($representante['telefono'] . ' / ' . ($representante['telefono_habitacion'] ?? 'N/A')) ?></td>
        </tr>
        <tr>
            <td class="label">Dirección:</td>
            <td colspan="3"><?= htmlspecialchars($representante['direccion']) ?></td>
        </tr>
        <tr>
            <td class="label">Caso Emergencia:</td>
            <td><?= htmlspecialchars($estudiante['representante_emergencia'] ?? 'N/A') ?></td>
            <td class="label">Telf. Emergencia:</td>
            <td><?= htmlspecialchars($estudiante['telefono_emergencia'] ?? 'N/A') ?></td>
        </tr>
    </table>
    <?php else: ?>
    <p>No se encontraron datos del representante principal.</p>
    <?php endif; ?>

    <h2>2. ANTECEDENTES MÉDICOS Y SOCIALES</h2>
    <?php if ($medicos): ?>
    <table>
        <tr>
            <td class="label">Alergias:</td>
            <td><?= $medicos['es_alergico'] === 'Si' ? 'Sí (' . htmlspecialchars($medicos['alergico_a']) . ')' : 'No' ?></td>
            <td class="label">Toma Medicamentos:</td>
            <td><?= $medicos['toma_medicamento'] === 'Si' ? 'Sí (' . htmlspecialchars($medicos['medicamentos_que_toma']) . ')' : 'No' ?></td>
        </tr>
        <tr>
            <td class="label">Padece Enfermedad:</td>
            <td><?= $medicos['tiene_patologia'] === 'Si' ? 'Sí (' . htmlspecialchars($medicos['patologia_que_padece']) . ')' : 'No' ?></td>
            <td class="label">Ha sido operado:</td>
            <td><?= $medicos['ha_sido_operado'] === 'Si' ? 'Sí (' . htmlspecialchars($medicos['de_que_operado']) . ')' : 'No' ?></td>
        </tr>
        <tr>
            <td class="label">Condición Neurodivergente:</td>
            <td colspan="3"><?= $medicos['condicion_neurodivergente'] === 'Si' ? 'Sí (' . htmlspecialchars($medicos['informe_neurologico']) . ')' : 'No' ?></td>
        </tr>
    </table>
    <?php endif; ?>
    <?php if ($sociales): ?>
    <table>
        <tr>
            <td class="label">Vive con:</td>
            <td><?= htmlspecialchars($sociales['con_quien_vive']) ?> (<?= htmlspecialchars($sociales['parentesco_con_quien_vive']) ?>)</td>
            <td class="label">Tipo de Vivienda:</td>
            <td><?= htmlspecialchars($sociales['tipo_vivienda']) ?></td>
        </tr>
    </table>
    <?php endif; ?>

    <?php if ($preescolar): ?>
    <h2>3. ÁREA DE PREESCOLAR</h2>
    <table>
        <tr>
            <td class="label">¿Quién lo cuida?:</td>
            <td><?= htmlspecialchars($preescolar['quien_lo_cuida']) ?></td>
            <td class="label">Actividades que prefiere:</td>
            <td><?= htmlspecialchars($preescolar['tipo_actividades']) ?></td>
        </tr>
        <tr>
            <td class="label">¿Con quién juega?:</td>
            <td><?= htmlspecialchars($preescolar['con_quien_juega']) ?></td>
            <td class="label">Ha estado en guardería:</td>
            <td><?= htmlspecialchars($preescolar['ha_estado_guarderia']) ?></td>
        </tr>
        <tr>
            <td class="label">¿Otro Preescolar?:</td>
            <td colspan="3"><?= $preescolar['otro_preescolar'] === 'Si' ? 'Sí (' . htmlspecialchars($preescolar['cual_preescolar']) . ')' : 'No' ?></td>
        </tr>
        <tr>
            <td class="label">Dificultad Observada:</td>
            <td colspan="3"><?= $preescolar['observado_dificultad'] === 'Si' ? 'Sí (' . htmlspecialchars($preescolar['dificultad_observada']) . ')' : 'No' ?></td>
        </tr>
        <tr>
            <td class="label">Consultas a:</td>
            <td colspan="3">
                Psicólogo: <?= $preescolar['consulta_psicologo'] ?> |
                Psicopedagogo: <?= $preescolar['consulta_psicopedagogo'] ?> |
                Neurólogo: <?= $preescolar['consulta_neurologo'] ?> |
                Terapeuta Leng.: <?= $preescolar['consulta_terapeuta_lenguaje'] ?>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <div class="firma-box">
        <div>
            <div class="firma-linea">Firma del Representante</div>
            <p style="margin: 5px 0; font-size: 12px;"><?= htmlspecialchars($representante['nombres'] . ' ' . $representante['apellidos']) ?></p>
        </div>
        <div>
            <div class="firma-linea">Firma Sello Institución</div>
            <p style="margin: 5px 0; font-size: 12px;">Director / Control de Estudios</p>
        </div>
    </div>

</body>
</html>
