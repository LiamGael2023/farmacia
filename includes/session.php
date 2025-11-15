<?php
/**
 * Gestión de Sesión
 * Sistema de Farmacia
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Verificar autorización
function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit();
    }
}

// Verificar rol de usuario
function hasRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return isLoggedIn() && in_array($_SESSION['rol'], $roles);
}

// Obtener información del usuario actual
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido'],
            'email' => $_SESSION['email'],
            'rol' => $_SESSION['rol']
        ];
    }
    return null;
}

// Registrar log de auditoría
function logAction($accion, $tabla = null, $registro_id = null, $detalles = null) {
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = getDB();

        $usuario_id = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $stmt = $db->prepare("
            INSERT INTO logs (usuario_id, accion, tabla, registro_id, detalles, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$usuario_id, $accion, $tabla, $registro_id, $detalles, $ip]);
    } catch (Exception $e) {
        error_log("Error logging action: " . $e->getMessage());
    }
}
?>
