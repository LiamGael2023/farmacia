<?php
/**
 * Procesar Venta
 * Sistema de Farmacia
 */

require_once __DIR__ . '/../../includes/session.php';
checkAuth();

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pos.php');
    exit();
}

$db = getDB();

try {
    $db->beginTransaction();

    $numero_venta = $_POST['numero_venta'];
    $cliente_id = !empty($_POST['cliente_id']) ? $_POST['cliente_id'] : null;
    $usuario_id = $_SESSION['user_id'];
    $metodo_pago = $_POST['metodo_pago'];
    $subtotal = $_POST['subtotal'];
    $descuento = $_POST['descuento'] ?? 0;
    $impuesto = $_POST['impuesto'];
    $total = $_POST['total'];
    $observaciones = $_POST['observaciones'] ?? null;
    $items = $_POST['items'] ?? [];

    if (empty($items)) {
        throw new Exception('No hay productos en la venta');
    }

    // Insertar venta
    $stmt = $db->prepare("
        INSERT INTO ventas (
            numero_venta, cliente_id, usuario_id, subtotal, descuento,
            impuesto, total, metodo_pago, observaciones, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completada')
    ");

    $stmt->execute([
        $numero_venta,
        $cliente_id,
        $usuario_id,
        $subtotal,
        $descuento,
        $impuesto,
        $total,
        $metodo_pago,
        $observaciones
    ]);

    $venta_id = $db->lastInsertId();

    // Insertar detalles de venta y actualizar stock
    foreach ($items as $item) {
        $medicamento_id = $item['medicamento_id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        $subtotal_item = $precio * $cantidad;

        // Verificar stock disponible
        $stmtStock = $db->prepare("SELECT stock_actual FROM medicamentos WHERE id = ?");
        $stmtStock->execute([$medicamento_id]);
        $medicine = $stmtStock->fetch();

        if (!$medicine || $medicine['stock_actual'] < $cantidad) {
            throw new Exception('Stock insuficiente para uno o más productos');
        }

        // Obtener lote disponible (FIFO - First In First Out)
        $stmtLote = $db->prepare("
            SELECT id, cantidad_actual
            FROM lotes
            WHERE medicamento_id = ? AND cantidad_actual > 0 AND estado = 'disponible'
            ORDER BY fecha_vencimiento ASC
            LIMIT 1
        ");
        $stmtLote->execute([$medicamento_id]);
        $lote = $stmtLote->fetch();
        $lote_id = $lote ? $lote['id'] : null;

        // Insertar detalle de venta
        $stmtDetalle = $db->prepare("
            INSERT INTO ventas_detalle (venta_id, medicamento_id, lote_id, cantidad, precio_unitario, descuento, subtotal)
            VALUES (?, ?, ?, ?, ?, 0, ?)
        ");
        $stmtDetalle->execute([$venta_id, $medicamento_id, $lote_id, $cantidad, $precio, $subtotal_item]);

        // Actualizar stock del medicamento
        $stmtUpdateStock = $db->prepare("
            UPDATE medicamentos
            SET stock_actual = stock_actual - ?
            WHERE id = ?
        ");
        $stmtUpdateStock->execute([$cantidad, $medicamento_id]);

        // Actualizar cantidad del lote si existe
        if ($lote_id) {
            $stmtUpdateLote = $db->prepare("
                UPDATE lotes
                SET cantidad_actual = cantidad_actual - ?
                WHERE id = ?
            ");
            $stmtUpdateLote->execute([$cantidad, $lote_id]);

            // Marcar lote como agotado si ya no tiene stock
            $stmtCheckLote = $db->prepare("
                UPDATE lotes
                SET estado = 'agotado'
                WHERE id = ? AND cantidad_actual = 0
            ");
            $stmtCheckLote->execute([$lote_id]);
        }

        // Registrar movimiento de inventario
        $stmtMov = $db->prepare("
            INSERT INTO movimientos_inventario (medicamento_id, lote_id, tipo_movimiento, cantidad, motivo, referencia, usuario_id)
            VALUES (?, ?, 'salida', ?, 'Venta', ?, ?)
        ");
        $stmtMov->execute([$medicamento_id, $lote_id, $cantidad, $numero_venta, $usuario_id]);
    }

    // Registrar log
    logAction('create_sale', 'ventas', $venta_id, "Venta {$numero_venta} procesada - Total: S/ {$total}");

    $db->commit();

    // Redirigir a página de éxito o imprimir ticket
    $_SESSION['success'] = 'Venta procesada exitosamente';
    $_SESSION['last_sale_id'] = $venta_id;
    header('Location: ../../sale-success.php?id=' . $venta_id);
    exit();

} catch (Exception $e) {
    $db->rollBack();
    error_log("Process sale error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../../pos.php');
    exit();
}
?>
