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

$fecha_actual = date('d/m/Y');

// Meses en español
$meses = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
$dia = date('d');
$mes = $meses[date('m')];
$anio = date('Y');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Estudio - <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            margin: 0;
            padding: 40px 60px;
            background: #fff;
            line-height: 1.5;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            font-weight: bold;
            position: relative;
        }
        .header-logo {
            position: absolute;
            top: 0;
            right: 0;
            width: 90px;
            height: auto;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            line-height: 1.3;
        }
        .titulo {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 40px;
            text-decoration: underline;
        }
        .contenido {
            font-size: 16px;
            text-align: justify;
            text-justify: inter-word;
            margin-bottom: 40px;
            line-height: 1.8;
        }
        .firma-box {
            margin-top: 80px;
            text-align: center;
            font-size: 16px;
        }
        .firma-box p {
            margin: 2px 0;
        }
        .footer {
            margin-top: 100px;
            text-align: center;
            font-size: 12px;
            line-height: 1.4;
        }
        .footer p {
            margin: 2px 0;
        }
        @media print {
            body { padding: 0; margin: 2.5cm 2cm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="../public/img/logo.png" class="header-logo" alt="Logo Institución" onerror="this.style.display='none'">
        <p>REPÚBLICA BOLIVARIANA DE VENEZUELA</p>
        <p>MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN</p>
        <p>UNIDAD EDUCATIVA NACIONAL "JESÚS MARÍA SIFONTES"</p>
        <p>LOS TEQUES - ESTADO BOLIVARIANO DE MIRANDA</p>
        <p>Código D.E.A: OD12021510</p>
        <p>Código: 13006564850</p>
    </div>

    <div class="titulo">
        CONSTANCIA DE ESTUDIOS
    </div>

    <div class="contenido">
        <p>
            Quien suscribe Lic. Freddy Moreno, Director de la Unidad Educativa Nacional Jesús María Sifontes, por medio de la presente hace constar que el (la) niño (a): 
            <strong><?= mb_strtoupper(htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']), 'UTF-8') ?></strong>, 
            titular de la Cédula Escolar/Identidad N° V <strong><?= htmlspecialchars($estudiante['cedula_escolar']) ?></strong>, 
            es estudiante de <strong><?= mb_strtoupper(htmlspecialchars($estudiante['grado']), 'UTF-8') ?></strong>, sección: 
            <strong>"<?= htmlspecialchars($estudiante['seccion']) ?>"</strong> de nuestra Institución, durante el período escolar <strong><?= htmlspecialchars($estudiante['periodo']) ?></strong>.
        </p>
        <p style="margin-top: 30px;">
            Constancia que se expide en la ciudad de Los Teques, a petición de la parte interesada, a los <strong><?= $dia ?></strong> días del mes de <strong><?= $mes ?></strong> del año <strong><?= $anio ?></strong>.
        </p>
    </div>

    <div class="firma-box">
        <p>Atentamente</p>
        <br><br><br>
        <p><strong>Lic. Freddy Moreno</strong></p>
        <p><strong>Director</strong></p>
    </div>

    <div class="footer">
        <p>Calle Ribas N° 41, Los Teques Estado Bolivariano de Miranda Rif: J-40505747-5</p>
        <p>cofopisifontes@gmail.com</p>
    </div>

    <script>
        setTimeout(() => {
            window.print();
        }, 500);
    </script>
</body>
</html>
