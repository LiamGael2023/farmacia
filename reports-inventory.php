<?php
$pageTitle = 'Reporte de Inventario - Sistema de Farmacia';
$currentPage = 'reports';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Estadísticas generales
$stats = $db->query("
    SELECT
        COUNT(*) as total_medicamentos,
        SUM(stock_actual) as total_stock,
        SUM(stock_actual * precio_compra) as valor_inventario_compra,
        SUM(stock_actual * precio_venta) as valor_inventario_venta
    FROM medicamentos
    WHERE estado = 'activo'
")->fetch();

// Medicamentos con stock bajo
$stockBajo = $db->query("
    SELECT m.*, c.nombre as categoria_nombre
    FROM medicamentos m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE m.stock_actual <= m.stock_minimo
    AND m.estado = 'activo'
    ORDER BY m.stock_actual ASC
")->fetchAll();

// Medicamentos sin stock
$sinStock = $db->query("
    SELECT m.*, c.nombre as categoria_nombre
    FROM medicamentos m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE m.stock_actual = 0
    AND m.estado = 'activo'
    ORDER BY m.nombre ASC
")->fetchAll();

// Inventario por categoría
$porCategoria = $db->query("
    SELECT c.nombre as categoria,
           COUNT(m.id) as cantidad_medicamentos,
           SUM(m.stock_actual) as total_stock,
           SUM(m.stock_actual * m.precio_venta) as valor_total
    FROM medicamentos m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE m.estado = 'activo'
    GROUP BY c.id
    ORDER BY valor_total DESC
")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Reportes</div>
                    <h2 class="page-title">Reporte de Inventario</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Estadísticas -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Total Medicamentos</div>
                            <div class="h1 mb-0"><?php echo $stats['total_medicamentos']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Stock Total</div>
                            <div class="h1 mb-0 text-primary"><?php echo number_format($stats['total_stock']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Valor (Compra)</div>
                            <div class="h1 mb-0 text-info">S/ <?php echo number_format($stats['valor_inventario_compra'], 2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Valor (Venta)</div>
                            <div class="h1 mb-0 text-success">S/ <?php echo number_format($stats['valor_inventario_venta'], 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-deck row-cards">
                <!-- Inventario por categoría -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Inventario por Categoría</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Categoría</th>
                                        <th class="text-end">Productos</th>
                                        <th class="text-end">Stock</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($porCategoria as $cat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['categoria'] ?? 'Sin categoría'); ?></td>
                                        <td class="text-end"><?php echo $cat['cantidad_medicamentos']; ?></td>
                                        <td class="text-end"><?php echo number_format($cat['total_stock']); ?></td>
                                        <td class="text-end"><strong>S/ <?php echo number_format($cat['valor_total'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Medicamentos con stock bajo -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-alert-triangle icon text-red me-2"></i>
                                Stock Bajo (<?php echo count($stockBajo); ?>)
                            </h3>
                        </div>
                        <div class="card-body card-body-scrollable" style="max-height: 400px;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th class="text-end">Stock</th>
                                        <th class="text-end">Mínimo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stockBajo as $item): ?>
                                    <tr class="low-stock">
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                        </td>
                                        <td class="text-end"><span class="badge bg-red"><?php echo $item['stock_actual']; ?></span></td>
                                        <td class="text-end"><?php echo $item['stock_minimo']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Medicamentos sin stock -->
                <?php if (count($sinStock) > 0): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-alert-circle icon text-red me-2"></i>
                                Medicamentos Sin Stock (<?php echo count($sinStock); ?>)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($sinStock as $item): ?>
                                <div class="col-md-3 mb-2">
                                    <div class="border p-2 rounded">
                                        <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
