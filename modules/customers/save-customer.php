<?php
/**
 * Guardar Cliente
 * Sistema de Farmacia
 */

require_once __DIR__ . '/../../includes/session.php';
checkAuth();

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../customers.php');
    exit();
}

$db = getDB();

try {
    $stmt = $db->prepare("
        INSERT INTO clientes (
            dni, nombre, apellido, fecha_nacimiento, sexo,
            telefono, email, direccion, alergias, observaciones
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['dni'] ?: null,
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['fecha_nacimiento'] ?: null,
        $_POST['sexo'] ?: null,
        $_POST['telefono'] ?: null,
        $_POST['email'] ?: null,
        $_POST['direccion'] ?: null,
        $_POST['alergias'] ?: null,
        $_POST['observaciones'] ?: null
    ]);

    $cliente_id = $db->lastInsertId();
    logAction('create_customer', 'clientes', $cliente_id, 'Cliente creado: ' . $_POST['nombre'] . ' ' . $_POST['apellido']);

    $_SESSION['success'] = 'Cliente registrado exitosamente';
    header('Location: ../../customers.php');
    exit();

} catch (PDOException $e) {
    error_log("Save customer error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al guardar el cliente';
    header('Location: ../../customers.php');
    exit();
}
?>
