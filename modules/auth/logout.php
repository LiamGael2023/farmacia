<?php
/**
 * Módulo de Autenticación - Logout
 * Sistema de Farmacia
 */

session_start();

if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../config/database.php';

    try {
        $db = getDB();

        // Registrar log de cierre de sesión
        $stmt = $db->prepare("
            INSERT INTO logs (usuario_id, accion, detalles, ip_address)
            VALUES (?, 'logout', 'Cierre de sesión', ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destruir cookie de "recordarme"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time()-42000, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: ../../index.php');
exit();
?>
