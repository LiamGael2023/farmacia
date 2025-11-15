<?php
/**
 * Guardar/Actualizar Medicamento
 * Sistema de Farmacia
 */

require_once __DIR__ . '/../../includes/session.php';
checkAuth();

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../medicines.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

try {
    $db->beginTransaction();

    $data = [
        'codigo' => $_POST['codigo'],
        'nombre' => $_POST['nombre'],
        'nombre_generico' => $_POST['nombre_generico'] ?? null,
        'categoria_id' => !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null,
        'presentacion' => $_POST['presentacion'] ?? null,
        'concentracion' => $_POST['concentracion'] ?? null,
        'laboratorio' => $_POST['laboratorio'] ?? null,
        'principio_activo' => $_POST['principio_activo'] ?? null,
        'descripcion' => $_POST['descripcion'] ?? null,
        'requiere_receta' => isset($_POST['requiere_receta']) ? 1 : 0,
        'precio_compra' => $_POST['precio_compra'],
        'precio_venta' => $_POST['precio_venta'],
        'stock_actual' => $_POST['stock_actual'] ?? 0,
        'stock_minimo' => $_POST['stock_minimo'] ?? 10,
        'ubicacion' => $_POST['ubicacion'] ?? null
    ];

    if ($id) {
        // Actualizar
        $stmt = $db->prepare("
            UPDATE medicamentos SET
                codigo = ?,
                nombre = ?,
                nombre_generico = ?,
                categoria_id = ?,
                presentacion = ?,
                concentracion = ?,
                laboratorio = ?,
                principio_activo = ?,
                descripcion = ?,
                requiere_receta = ?,
                precio_compra = ?,
                precio_venta = ?,
                stock_minimo = ?,
                ubicacion = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['nombre_generico'],
            $data['categoria_id'],
            $data['presentacion'],
            $data['concentracion'],
            $data['laboratorio'],
            $data['principio_activo'],
            $data['descripcion'],
            $data['requiere_receta'],
            $data['precio_compra'],
            $data['precio_venta'],
            $data['stock_minimo'],
            $data['ubicacion'],
            $id
        ]);

        logAction('update_medicine', 'medicamentos', $id, 'Medicamento actualizado: ' . $data['nombre']);
        $_SESSION['success'] = 'Medicamento actualizado exitosamente';
    } else {
        // Insertar nuevo
        $stmt = $db->prepare("
            INSERT INTO medicamentos (
                codigo, nombre, nombre_generico, categoria_id, presentacion,
                concentracion, laboratorio, principio_activo, descripcion,
                requiere_receta, precio_compra, precio_venta, stock_actual,
                stock_minimo, ubicacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['nombre_generico'],
            $data['categoria_id'],
            $data['presentacion'],
            $data['concentracion'],
            $data['laboratorio'],
            $data['principio_activo'],
            $data['descripcion'],
            $data['requiere_receta'],
            $data['precio_compra'],
            $data['precio_venta'],
            $data['stock_actual'],
            $data['stock_minimo'],
            $data['ubicacion']
        ]);

        $newId = $db->lastInsertId();

        // Si hay stock inicial, registrar movimiento
        if ($data['stock_actual'] > 0) {
            $stmtMov = $db->prepare("
                INSERT INTO movimientos_inventario (medicamento_id, tipo_movimiento, cantidad, motivo, usuario_id)
                VALUES (?, 'entrada', ?, 'Stock inicial', ?)
            ");
            $stmtMov->execute([$newId, $data['stock_actual'], $_SESSION['user_id']]);
        }

        logAction('create_medicine', 'medicamentos', $newId, 'Medicamento creado: ' . $data['nombre']);
        $_SESSION['success'] = 'Medicamento creado exitosamente';
    }

    $db->commit();
    header('Location: ../../medicines.php');
    exit();

} catch (PDOException $e) {
    $db->rollBack();
    error_log("Save medicine error: " . $e->getMessage());

    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $_SESSION['error'] = 'El código del medicamento ya existe';
    } else {
        $_SESSION['error'] = 'Error al guardar el medicamento';
    }

    header('Location: ../../medicines.php');
    exit();
}
?>
