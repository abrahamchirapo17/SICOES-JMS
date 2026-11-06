<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'C:/Users/abrah/Downloads/Sicoes-JMS AG/conexion.php';
$conexion = new Conexion();
$conn = $conexion->getConexion();
if($conn) {
    echo "DB CONNECTION OK\n";
    $stmt = $conn->query("SELECT COUNT(*) FROM estudiantes");
    echo "Estudiantes: " . $stmt->fetchColumn() . "\n";
} else {
    echo "DB FAILED";
}
?>
