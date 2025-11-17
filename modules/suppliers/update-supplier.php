<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../suppliers.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: ../../suppliers.php');
    exit();
}

try {
    $stmt = $db->prepare("
        UPDATE proveedores SET
            nombre = ?,
            ruc = ?,
            direccion = ?,
            telefono = ?,
            email = ?,
            contacto_nombre = ?,
            contacto_telefono = ?,
            estado = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['ruc'] ?: null,
        $_POST['direccion'] ?: null,
        $_POST['telefono'] ?: null,
        $_POST['email'] ?: null,
        $_POST['contacto_nombre'] ?: null,
        $_POST['contacto_telefono'] ?: null,
        $_POST['estado'],
        $id
    ]);

    logAction('update_supplier', 'proveedores', $id, 'Proveedor actualizado: ' . $_POST['nombre']);
    $_SESSION['success'] = 'Proveedor actualizado exitosamente';
    header('Location: ../../supplier-view.php?id=' . $id);
} catch (PDOException $e) {
    error_log("Update supplier error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar el proveedor';
    header('Location: ../../supplier-edit.php?id=' . $id);
}
exit();
?>
