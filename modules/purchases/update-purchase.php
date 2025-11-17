<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../purchases.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: ../../purchases.php');
    exit();
}

try {
    $stmt = $db->prepare("
        UPDATE compras SET
            numero_compra = ?,
            proveedor_id = ?,
            fecha_entrega = ?,
            estado = ?,
            observaciones = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['numero_compra'],
        $_POST['proveedor_id'],
        $_POST['fecha_entrega'] ?: null,
        $_POST['estado'],
        $_POST['observaciones'] ?: null,
        $id
    ]);

    logAction('update_purchase', 'compras', $id, 'Compra actualizada: ' . $_POST['numero_compra']);
    $_SESSION['success'] = 'Compra actualizada exitosamente';
    header('Location: ../../purchase-view.php?id=' . $id);
} catch (PDOException $e) {
    error_log("Update purchase error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar la compra';
    header('Location: ../../purchase-edit.php?id=' . $id);
}
exit();
?>
