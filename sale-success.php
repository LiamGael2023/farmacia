<?php
$pageTitle = 'Venta Exitosa - Sistema de Farmacia';
$currentPage = 'pos';
require_once 'includes/header.php';
require_once 'config/database.php';

$sale_id = $_GET['id'] ?? null;
if (!$sale_id) {
    header('Location: pos.php');
    exit();
}

$db = getDB();

// Obtener detalles de la venta
$stmt = $db->prepare("
    SELECT v.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
           u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
    FROM ventas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    LEFT JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = ?
");
$stmt->execute([$sale_id]);
$venta = $stmt->fetch();

if (!$venta) {
    header('Location: pos.php');
    exit();
}

// Obtener items de la venta
$stmt = $db->prepare("
    SELECT vd.*, m.nombre, m.codigo, m.presentacion
    FROM ventas_detalle vd
    INNER JOIN medicamentos m ON vd.medicamento_id = m.id
    WHERE vd.venta_id = ?
");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl d-flex flex-column justify-content-center" style="min-height: 400px;">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="text-success mb-3">
                                <i class="ti ti-circle-check icon" style="font-size: 64px;"></i>
                            </div>
                            <h2 class="text-success">¡Venta Procesada Exitosamente!</h2>
                            <p class="text-muted">La venta ha sido registrada correctamente en el sistema</p>

                            <div class="row mt-4">
                                <div class="col-md-8 offset-md-2">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Resumen de Venta</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-6 text-start"><strong>N° Venta:</strong></div>
                                                <div class="col-6 text-end"><?php echo htmlspecialchars($venta['numero_venta']); ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-6 text-start"><strong>Cliente:</strong></div>
                                                <div class="col-6 text-end">
                                                    <?php
                                                    echo $venta['cliente_nombre']
                                                        ? htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido'])
                                                        : 'Cliente General';
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-6 text-start"><strong>Fecha:</strong></div>
                                                <div class="col-6 text-end"><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-6 text-start"><strong>Vendedor:</strong></div>
                                                <div class="col-6 text-end">
                                                    <?php echo htmlspecialchars($venta['vendedor_nombre'] . ' ' . $venta['vendedor_apellido']); ?>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row mb-2">
                                                <div class="col-6 text-start">Subtotal:</div>
                                                <div class="col-6 text-end">S/ <?php echo number_format($venta['subtotal'], 2); ?></div>
                                            </div>
                                            <?php if ($venta['descuento'] > 0): ?>
                                            <div class="row mb-2">
                                                <div class="col-6 text-start">Descuento:</div>
                                                <div class="col-6 text-end text-danger">-S/ <?php echo number_format($venta['descuento'], 2); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <div class="row mb-2">
                                                <div class="col-6 text-start">IGV (18%):</div>
                                                <div class="col-6 text-end">S/ <?php echo number_format($venta['impuesto'], 2); ?></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-6 text-start"><h3>Total:</h3></div>
                                                <div class="col-6 text-end"><h2 class="text-primary">S/ <?php echo number_format($venta['total'], 2); ?></h2></div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-6 text-start"><strong>Método de Pago:</strong></div>
                                                <div class="col-6 text-end">
                                                    <span class="badge bg-blue"><?php echo ucfirst($venta['metodo_pago']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="btn-list mt-4">
                                <a href="print-ticket.php?id=<?php echo $sale_id; ?>" class="btn btn-primary" target="_blank">
                                    <i class="ti ti-printer icon"></i>
                                    Imprimir Ticket
                                </a>
                                <a href="pos.php" class="btn btn-success">
                                    <i class="ti ti-cash-register icon"></i>
                                    Nueva Venta
                                </a>
                                <a href="sales-list.php" class="btn btn-secondary">
                                    <i class="ti ti-list icon"></i>
                                    Ver Ventas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
