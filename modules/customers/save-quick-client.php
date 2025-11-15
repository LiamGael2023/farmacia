<?php
/**
 * Guardar Cliente Rápido (AJAX)
 * Sistema de Farmacia
 */

require_once __DIR__ . '/../../includes/session.php';
checkAuth();

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$db = getDB();

try {
    $stmt = $db->prepare("
        INSERT INTO clientes (dni, nombre, apellido, telefono)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['dni'] ?: null,
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['telefono'] ?: null
    ]);

    $cliente_id = $db->lastInsertId();
    logAction('create_customer', 'clientes', $cliente_id, 'Cliente rápido creado: ' . $_POST['nombre']);

    echo json_encode([
        'success' => true,
        'cliente' => [
            'id' => $cliente_id,
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Save quick client error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar el cliente']);
}
?>
