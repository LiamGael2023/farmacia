<?php
$pageTitle = 'Historial de Ventas - Sistema de Farmacia';
$currentPage = 'sales';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener ventas
$ventas = $db->query("
    SELECT v.*,
           c.nombre as cliente_nombre,
           c.apellido as cliente_apellido,
           u.nombre as vendedor_nombre,
           u.apellido as vendedor_apellido
    FROM ventas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    LEFT JOIN usuarios u ON v.usuario_id = u.id
    ORDER BY v.fecha_venta DESC
")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Ventas</div>
                    <h2 class="page-title">Historial de Ventas</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="pos.php" class="btn btn-primary">
                        <i class="ti ti-plus icon"></i>
                        Nueva Venta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Ventas</h3>
                </div>
                <div class="card-body">
                    <table id="tableSales" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>N° Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Método Pago</th>
                                <th>Estado</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($venta['numero_venta']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></td>
                                <td>
                                    <?php
                                    if ($venta['cliente_nombre']) {
                                        echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']);
                                    } else {
                                        echo '<span class="text-muted">Cliente general</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($venta['vendedor_nombre'] . ' ' . $venta['vendedor_apellido']); ?></td>
                                <td><strong>S/ <?php echo number_format($venta['total'], 2); ?></strong></td>
                                <td>
                                    <span class="badge bg-blue">
                                        <?php echo ucfirst($venta['metodo_pago']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($venta['estado'] === 'completada'): ?>
                                        <span class="badge bg-success">Completada</span>
                                    <?php elseif ($venta['estado'] === 'cancelada'): ?>
                                        <span class="badge bg-danger">Cancelada</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="sales-view.php?id=<?php echo $venta['id']; ?>"
                                           class="btn btn-sm btn-icon btn-info"
                                           title="Ver detalles">
                                            <i class="ti ti-eye icon"></i>
                                        </a>
                                        <a href="print-ticket.php?id=<?php echo $venta['id']; ?>"
                                           class="btn btn-sm btn-icon btn-primary"
                                           title="Imprimir"
                                           target="_blank">
                                            <i class="ti ti-printer icon"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<?php
$customJS = <<<'JS'
<script>
$(document).ready(function() {
    $('#tableSales').DataTable({
        order: [[1, 'desc']]
    });
});
</script>
JS;
?>
