<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Retorna respuestas JSON estandarizadas
function send_json_response($status, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Limpia las variables para evitar inyecciones HTML/JS
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')));
}

// Registra la acción del usuario en la tabla log_actividades
function registrar_log($conn, $usuario_id, $accion, $detalle) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $conn->prepare("INSERT INTO log_actividades (usuario_id, accion, detalle, ip_address) VALUES (:usuario_id, :accion, :detalle, :ip_address)");
        $stmt->execute([
            ':usuario_id' => $usuario_id, // Si es nulo, significa que fue una acción de sistema o no autenticada aún
            ':accion' => sanitize_input($accion),
            ':detalle' => sanitize_input($detalle),
            ':ip_address' => $ip
        ]);
    } catch (PDOException $e) {
        // En producción, podrías guardar esto en un archivo de texto si la DB falla.
    }
}
?>
