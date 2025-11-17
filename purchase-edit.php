<?php
$pageTitle = 'Editar Compra - Sistema de Farmacia';
$currentPage = 'purchases';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: purchases.php');
    exit();
}

// Obtener compra
$stmt = $db->prepare("SELECT * FROM compras WHERE id = ?");
$stmt->execute([$id]);
$compra = $stmt->fetch();

if (!$compra) {
    $_SESSION['error'] = 'Compra no encontrada';
    header('Location: purchases.php');
    exit();
}

// Obtener proveedores
$proveedores = $db->query("SELECT id, nombre, ruc FROM proveedores WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Compras</div>
                    <h2 class="page-title">Editar Compra</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="purchase-view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/purchases/update-purchase.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información de la Compra</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">N° Compra</label>
                                            <input type="text" name="numero_compra" class="form-control" value="<?php echo htmlspecialchars($compra['numero_compra']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Proveedor</label>
                                            <select name="proveedor_id" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($proveedores as $prov): ?>
                                                    <option value="<?php echo $prov['id']; ?>" <?php echo $prov['id'] == $compra['proveedor_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($prov['nombre'] . ($prov['ruc'] ? ' - ' . $prov['ruc'] : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Fecha de Entrega</label>
                                            <input type="date" name="fecha_entrega" class="form-control" value="<?php echo $compra['fecha_entrega'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estado</label>
                                            <select name="estado" class="form-select">
                                                <option value="pendiente" <?php echo $compra['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="recibida" <?php echo $compra['estado'] === 'recibida' ? 'selected' : ''; ?>>Recibida</option>
                                                <option value="parcial" <?php echo $compra['estado'] === 'parcial' ? 'selected' : ''; ?>>Parcial</option>
                                                <option value="cancelada" <?php echo $compra['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Observaciones</label>
                                            <textarea name="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($compra['observaciones'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Totales</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Subtotal</label>
                                    <input type="number" name="subtotal" class="form-control" step="0.01" value="<?php echo $compra['subtotal']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Impuesto (18%)</label>
                                    <input type="number" name="impuesto" class="form-control" step="0.01" value="<?php echo $compra['impuesto']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Total</label>
                                    <input type="number" name="total" class="form-control" step="0.01" value="<?php echo $compra['total']; ?>" readonly>
                                </div>
                                <small class="text-muted">Los totales se calculan automáticamente desde los productos</small>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-device-floppy icon"></i>
                                    Guardar Cambios
                                </button>
                                <a href="purchase-view.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 mt-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
