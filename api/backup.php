<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1) {
    die("Acceso denegado. Solo el administrador principal puede generar copias de seguridad.");
}

require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) {
    die("Error de conexión a la base de datos.");
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sicoes_jms';

$fecha = date("Y-m-d_H-i-s");
$nombre_archivo = "backup_sicoes_jms_$fecha.sql";

// Usar mysqldump de XAMPP (ajustar la ruta según donde esté instalado XAMPP)
$mysqldump_path = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
$temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nombre_archivo;

$comando = "\"$mysqldump_path\" --opt -h $db_host -u $db_user $db_name > \"$temp_file\"";
exec($comando, $output, $return_var);

if ($return_var !== 0) {
    // Si falla mysqldump por X motivo, mostramos un error
    die("Error al generar la copia de seguridad. Código de error: $return_var. Asegúrese de que XAMPP esté instalado en C:\xampp");
}

registrar_log($conn, $_SESSION['usuario_id'], 'Copia de Seguridad', "Generó backup de la base de datos: $nombre_archivo");

// Forzar descarga del archivo
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($temp_file).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($temp_file));
readfile($temp_file);

// Eliminar archivo temporal después de descargarlo
unlink($temp_file);
exit;
