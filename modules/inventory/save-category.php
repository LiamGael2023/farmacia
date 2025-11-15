<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../categories.php');
    exit();
}

$db = getDB();

try {
    $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
    $stmt->execute([$_POST['nombre'], $_POST['descripcion'] ?: null]);

    logAction('create_category', 'categorias', $db->lastInsertId(), 'Categoría creada: ' . $_POST['nombre']);
    $_SESSION['success'] = 'Categoría creada exitosamente';
} catch (PDOException $e) {
    error_log("Save category error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al guardar la categoría';
}

header('Location: ../../categories.php');
exit();
?>
