<?php
$pageTitle = 'Detalle de Compra - Sistema de Farmacia';
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
$stmt = $db->prepare("
    SELECT c.*, p.nombre as proveedor_nombre, p.ruc, p.telefono as proveedor_telefono,
           u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM compras c
    INNER JOIN proveedores p ON c.proveedor_id = p.id
    INNER JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$compra = $stmt->fetch();

if (!$compra) {
    $_SESSION['error'] = 'Compra no encontrada';
    header('Location: purchases.php');
    exit();
}

// Obtener detalles
$stmt = $db->prepare("
    SELECT cd.*, m.codigo, m.nombre, m.presentacion
    FROM compras_detalle cd
    INNER JOIN medicamentos m ON cd.medicamento_id = m.id
    WHERE cd.compra_id = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Obtener medicamentos disponibles
$medicamentos = $db->query("SELECT id, codigo, nombre, presentacion FROM medicamentos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Compras</div>
                    <h2 class="page-title">Compra N° <?php echo htmlspecialchars($compra['numero_compra']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <?php if ($compra['estado'] === 'pendiente' || $compra['estado'] === 'parcial'): ?>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-add-item">
                            <i class="ti ti-plus icon"></i>
                            Agregar Producto
                        </button>
                        <?php endif; ?>
                        <a href="purchase-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="ti ti-edit icon"></i>
                            Editar
                        </a>
                        <a href="purchases.php" class="btn btn-secondary">
                            <i class="ti ti-arrow-left icon"></i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información de la Compra</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">N° Compra</label>
                                <div><strong><?php echo htmlspecialchars($compra['numero_compra']); ?></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Compra</label>
                                <div><?php echo date('d/m/Y H:i', strtotime($compra['fecha_compra'])); ?></div>
                            </div>
                            <?php if ($compra['fecha_entrega']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Entrega</label>
                                <div><?php echo date('d/m/Y', strtotime($compra['fecha_entrega'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <?php
                                    $badge = 'bg-secondary';
                                    switch($compra['estado']) {
                                        case 'pendiente': $badge = 'bg-warning'; break;
                                        case 'recibida': $badge = 'bg-success'; break;
                                        case 'parcial': $badge = 'bg-info'; break;
                                        case 'cancelada': $badge = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($compra['estado']); ?></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Usuario</label>
                                <div><?php echo htmlspecialchars($compra['usuario_nombre'] . ' ' . $compra['usuario_apellido']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Proveedor</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre</label>
                                <div><strong><?php echo htmlspecialchars($compra['proveedor_nombre']); ?></strong></div>
                            </div>
                            <?php if ($compra['ruc']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">RUC</label>
                                <div><?php echo htmlspecialchars($compra['ruc']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($compra['proveedor_telefono']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Teléfono</label>
                                <div><?php echo htmlspecialchars($compra['proveedor_telefono']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Productos Comprados</h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($detalles) > 0): ?>
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>P. Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $det): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($det['nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($det['codigo'] . ' - ' . $det['presentacion']); ?></small>
                                        </td>
                                        <td><span class="badge bg-blue"><?php echo $det['cantidad']; ?></span></td>
                                        <td>S/ <?php echo number_format($det['precio_unitario'], 2); ?></td>
                                        <td><strong>S/ <?php echo number_format($det['subtotal'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <p>No hay productos agregados a esta compra</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-item">
                                    <i class="ti ti-plus icon"></i>
                                    Agregar Producto
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <table class="table">
                                        <tr>
                                            <td>Subtotal:</td>
                                            <td class="text-end"><strong>S/ <?php echo number_format($compra['subtotal'], 2); ?></strong></td>
                                        </tr>
                                        <?php if ($compra['impuesto'] > 0): ?>
                                        <tr>
                                            <td>IGV (18%):</td>
                                            <td class="text-end"><strong>S/ <?php echo number_format($compra['impuesto'], 2); ?></strong></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="fw-bold">
                                            <td>Total:</td>
                                            <td class="text-end h3 text-success">S/ <?php echo number_format($compra['total'], 2); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($compra['observaciones']): ?>
                            <div class="mt-3">
                                <label class="form-label text-muted">Observaciones</label>
                                <p><?php echo nl2br(htmlspecialchars($compra['observaciones'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Agregar Producto -->
<div class="modal modal-blur fade" id="modal-add-item" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/purchases/add-item.php">
                <input type="hidden" name="compra_id" value="<?php echo $id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label required">Medicamento</label>
                                <select name="medicamento_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($medicamentos as $med): ?>
                                        <option value="<?php echo $med['id']; ?>">
                                            <?php echo htmlspecialchars($med['codigo'] . ' - ' . $med['nombre'] . ' - ' . $med['presentacion']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Precio Unitario</label>
                                <input type="number" name="precio_unitario" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
