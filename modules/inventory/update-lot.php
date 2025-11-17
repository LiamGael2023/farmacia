<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../lots.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: ../../lots.php');
    exit();
}

try {
    // Obtener cantidad actual antigua para registrar movimiento si cambió
    $stmt = $db->prepare("SELECT cantidad_actual FROM lotes WHERE id = ?");
    $stmt->execute([$id]);
    $lote_old = $stmt->fetch();
    $cantidad_old = $lote_old['cantidad_actual'];
    $cantidad_new = $_POST['cantidad_actual'];

    $stmt = $db->prepare("
        UPDATE lotes SET
            numero_lote = ?,
            medicamento_id = ?,
            proveedor_id = ?,
            fecha_fabricacion = ?,
            fecha_vencimiento = ?,
            cantidad_actual = ?,
            precio_compra = ?,
            estado = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['numero_lote'],
        $_POST['medicamento_id'],
        $_POST['proveedor_id'] ?: null,
        $_POST['fecha_fabricacion'] ?: null,
        $_POST['fecha_vencimiento'],
        $cantidad_new,
        $_POST['precio_compra'],
        $_POST['estado'],
        $id
    ]);

    // Si la cantidad cambió, registrar movimiento de ajuste
    if ($cantidad_old != $cantidad_new) {
        $diferencia = $cantidad_new - $cantidad_old;
        $tipo = $diferencia > 0 ? 'entrada' : 'salida';
        $cantidad_mov = abs($diferencia);

        $stmt = $db->prepare("
            INSERT INTO movimientos_inventario (medicamento_id, lote_id, tipo_movimiento, cantidad, motivo, usuario_id)
            VALUES (?, ?, 'ajuste', ?, 'Ajuste manual de inventario', ?)
        ");
        $stmt->execute([
            $_POST['medicamento_id'],
            $id,
            $cantidad_mov,
            getCurrentUser()['id']
        ]);
    }

    logAction('update_lot', 'lotes', $id, 'Lote actualizado: ' . $_POST['numero_lote']);
    $_SESSION['success'] = 'Lote actualizado exitosamente';
    header('Location: ../../lot-view.php?id=' . $id);
} catch (PDOException $e) {
    error_log("Update lot error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar el lote';
    header('Location: ../../lot-edit.php?id=' . $id);
}
exit();
?>
