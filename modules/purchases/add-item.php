<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../purchases.php');
    exit();
}

$db = getDB();
$compra_id = $_POST['compra_id'] ?? null;

if (!$compra_id) {
    header('Location: ../../purchases.php');
    exit();
}

try {
    $db->beginTransaction();

    $cantidad = $_POST['cantidad'];
    $precio_unitario = $_POST['precio_unitario'];
    $subtotal = $cantidad * $precio_unitario;

    // Insertar detalle
    $stmt = $db->prepare("
        INSERT INTO compras_detalle (compra_id, medicamento_id, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $compra_id,
        $_POST['medicamento_id'],
        $cantidad,
        $precio_unitario,
        $subtotal
    ]);

    // Recalcular totales
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(subtotal), 0) as nuevo_subtotal
        FROM compras_detalle
        WHERE compra_id = ?
    ");
    $stmt->execute([$compra_id]);
    $totals = $stmt->fetch();

    $nuevo_subtotal = $totals['nuevo_subtotal'];
    $nuevo_impuesto = $nuevo_subtotal * 0.18;
    $nuevo_total = $nuevo_subtotal + $nuevo_impuesto;

    // Actualizar totales en la compra
    $stmt = $db->prepare("
        UPDATE compras
        SET subtotal = ?, impuesto = ?, total = ?
        WHERE id = ?
    ");
    $stmt->execute([$nuevo_subtotal, $nuevo_impuesto, $nuevo_total, $compra_id]);

    $db->commit();

    logAction('add_item_to_purchase', 'compras_detalle', $compra_id, 'Producto agregado a compra');
    $_SESSION['success'] = 'Producto agregado a la compra';
    header('Location: ../../purchase-view.php?id=' . $compra_id);
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Add item error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al agregar el producto';
    header('Location: ../../purchase-view.php?id=' . $compra_id);
}
exit();
?>
