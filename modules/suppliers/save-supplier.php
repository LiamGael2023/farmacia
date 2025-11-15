<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../suppliers.php');
    exit();
}

$db = getDB();

try {
    $stmt = $db->prepare("
        INSERT INTO proveedores (nombre, ruc, direccion, telefono, email, contacto_nombre, contacto_telefono)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['ruc'] ?: null,
        $_POST['direccion'] ?: null,
        $_POST['telefono'] ?: null,
        $_POST['email'] ?: null,
        $_POST['contacto_nombre'] ?: null,
        $_POST['contacto_telefono'] ?: null
    ]);

    logAction('create_supplier', 'proveedores', $db->lastInsertId(), 'Proveedor creado: ' . $_POST['nombre']);
    $_SESSION['success'] = 'Proveedor creado exitosamente';
} catch (PDOException $e) {
    error_log("Save supplier error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al guardar el proveedor';
}

header('Location: ../../suppliers.php');
exit();
?>
