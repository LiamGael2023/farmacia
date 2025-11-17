<?php
$pageTitle = 'Detalle de Venta - Sistema de Farmacia';
$currentPage = 'sales';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: sales-list.php');
    exit();
}

// Obtener venta
$stmt = $db->prepare("
    SELECT v.*,
           c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni,
           u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
    FROM ventas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    INNER JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = ?
");
$stmt->execute([$id]);
$venta = $stmt->fetch();

if (!$venta) {
    $_SESSION['error'] = 'Venta no encontrada';
    header('Location: sales-list.php');
    exit();
}

// Obtener detalles de la venta
$stmt = $db->prepare("
    SELECT vd.*, m.codigo, m.nombre, m.presentacion
    FROM ventas_detalle vd
    INNER JOIN medicamentos m ON vd.medicamento_id = m.id
    WHERE vd.venta_id = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Ventas</div>
                    <h2 class="page-title">Venta <?php echo htmlspecialchars($venta['numero_venta']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="print-ticket.php?id=<?php echo $id; ?>" class="btn btn-primary" target="_blank">
                            <i class="ti ti-printer icon"></i>
                            Imprimir
                        </a>
                        <a href="sales-list.php" class="btn btn-secondary">
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
                            <h3 class="card-title">Información de la Venta</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">N° Venta</label>
                                <div><strong><?php echo htmlspecialchars($venta['numero_venta']); ?></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha</label>
                                <div><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Cliente</label>
                                <div>
                                    <?php if ($venta['cliente_nombre']): ?>
                                        <strong><?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?></strong>
                                        <?php if ($venta['dni']): ?>
                                            <br><small class="text-muted">DNI: <?php echo htmlspecialchars($venta['dni']); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Cliente general</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Vendedor</label>
                                <div><?php echo htmlspecialchars($venta['vendedor_nombre'] . ' ' . $venta['vendedor_apellido']); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Método de Pago</label>
                                <div><span class="badge bg-blue"><?php echo ucfirst($venta['metodo_pago']); ?></span></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <?php if ($venta['estado'] === 'completada'): ?>
                                        <span class="badge bg-success">Completada</span>
                                    <?php elseif ($venta['estado'] === 'cancelada'): ?>
                                        <span class="badge bg-danger">Cancelada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($venta['observaciones']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Observaciones</label>
                                <div><?php echo nl2br(htmlspecialchars($venta['observaciones'])); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Productos</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $det): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($det['codigo']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($det['nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($det['presentacion']); ?></small>
                                        </td>
                                        <td class="text-center"><span class="badge bg-blue"><?php echo $det['cantidad']; ?></span></td>
                                        <td class="text-end">S/ <?php echo number_format($det['precio_unitario'], 2); ?></td>
                                        <td class="text-end"><strong>S/ <?php echo number_format($det['subtotal'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Totales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col">Subtotal:</div>
                                <div class="col-auto"><strong>S/ <?php echo number_format($venta['subtotal'], 2); ?></strong></div>
                            </div>
                            <?php if ($venta['descuento'] > 0): ?>
                            <div class="row mb-2">
                                <div class="col">Descuento:</div>
                                <div class="col-auto text-danger">-S/ <?php echo number_format($venta['descuento'], 2); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="row mb-2">
                                <div class="col">IGV (18%):</div>
                                <div class="col-auto"><strong>S/ <?php echo number_format($venta['impuesto'], 2); ?></strong></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col"><h3>Total:</h3></div>
                                <div class="col-auto"><h2 class="text-success">S/ <?php echo number_format($venta['total'], 2); ?></h2></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
