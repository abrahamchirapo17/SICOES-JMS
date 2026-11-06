<?php
// Configurar zona horaria por defecto para PHP (Venezuela UTC-4)
date_default_timezone_set('America/Caracas');

// Configuración de Seguridad de Sesiones y CSRF
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // ini_set('session.cookie_secure', 1); // Descomentar en producción con HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_header)) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'message' => 'Error de validación de seguridad (CSRF).']));
    }
}

/**
 * Conexión a la base de datos MySQL usando PDO.
 * Este archivo gestiona la conexión para el Sistema de Control de Estudios (sicoes_jms)
 */
class Conexion {
  private $host = "127.0.0.1"; // Servidor local (XAMPP)
    private $db_name = "sicoes_jms"; // Nombre de la base de datos
    private $username = "root"; // Usuario por defecto en XAMPP
    private $password = ""; // Sin contraseña por defecto en XAMPP
    public $conn;

    /**
     * Obtiene la conexión a la base de datos
     * @return PDO|null La instancia de conexión o nulo en caso de fallo
     */
    public function getConexion() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Usa sentencias preparadas nativas para mayor seguridad
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Forzar la zona horaria de MySQL a UTC-4 (Venezuela)
            $this->conn->exec("SET time_zone = '-04:00';");
        } catch(PDOException $exception) {
            // En un entorno de producción, es recomendable registrar el error en un log en vez de imprimirlo
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
