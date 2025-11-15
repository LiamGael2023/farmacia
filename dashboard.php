<?php
$pageTitle = 'Dashboard - Sistema de Farmacia';
$currentPage = 'dashboard';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$usuario_id = $_SESSION['user_id'];

// Obtener estadísticas
try {
    // Ventas del día
    $stmt = $db->query("
        SELECT COUNT(*) as total_ventas, COALESCE(SUM(total), 0) as total_monto
        FROM ventas
        WHERE DATE(fecha_venta) = CURDATE() AND estado = 'completada'
    ");
    $ventasHoy = $stmt->fetch();

    // Ventas del mes
    $stmt = $db->query("
        SELECT COUNT(*) as total_ventas, COALESCE(SUM(total), 0) as total_monto
        FROM ventas
        WHERE MONTH(fecha_venta) = MONTH(CURDATE())
        AND YEAR(fecha_venta) = YEAR(CURDATE())
        AND estado = 'completada'
    ");
    $ventasMes = $stmt->fetch();

    // Medicamentos con stock bajo
    $stmt = $db->query("
        SELECT COUNT(*) as total
        FROM medicamentos
        WHERE stock_actual <= stock_minimo AND estado = 'activo'
    ");
    $stockBajo = $stmt->fetch();

    // Medicamentos próximos a vencer (30 días)
    $stmt = $db->query("
        SELECT COUNT(DISTINCT l.medicamento_id) as total
        FROM lotes l
        WHERE l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND l.fecha_vencimiento >= CURDATE()
        AND l.cantidad_actual > 0
        AND l.estado = 'disponible'
    ");
    $proximosVencer = $stmt->fetch();

    // Total de medicamentos
    $stmt = $db->query("SELECT COUNT(*) as total FROM medicamentos WHERE estado = 'activo'");
    $totalMedicamentos = $stmt->fetch();

    // Total de clientes
    $stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'");
    $totalClientes = $stmt->fetch();

    // Últimas ventas
    $stmt = $db->query("
        SELECT v.*, c.nombre, c.apellido, u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
        FROM ventas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        LEFT JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.estado = 'completada'
        ORDER BY v.fecha_venta DESC
        LIMIT 5
    ");
    $ultimasVentas = $stmt->fetchAll();

    // Productos más vendidos (del mes)
    $stmt = $db->query("
        SELECT m.nombre, m.presentacion, SUM(vd.cantidad) as cantidad_vendida, SUM(vd.subtotal) as total_vendido
        FROM ventas_detalle vd
        INNER JOIN medicamentos m ON vd.medicamento_id = m.id
        INNER JOIN ventas v ON vd.venta_id = v.id
        WHERE MONTH(v.fecha_venta) = MONTH(CURDATE())
        AND YEAR(v.fecha_venta) = YEAR(CURDATE())
        AND v.estado = 'completada'
        GROUP BY vd.medicamento_id
        ORDER BY cantidad_vendida DESC
        LIMIT 5
    ");
    $masVendidos = $stmt->fetchAll();

    // Medicamentos con stock bajo (detalle)
    $stmt = $db->query("
        SELECT m.codigo, m.nombre, m.presentacion, m.stock_actual, m.stock_minimo, c.nombre as categoria
        FROM medicamentos m
        LEFT JOIN categorias c ON m.categoria_id = c.id
        WHERE m.stock_actual <= m.stock_minimo AND m.estado = 'activo'
        ORDER BY m.stock_actual ASC
        LIMIT 10
    ");
    $stockBajoDetalle = $stmt->fetchAll();

    // Medicamentos próximos a vencer (detalle)
    $stmt = $db->query("
        SELECT m.codigo, m.nombre, m.presentacion, l.numero_lote, l.fecha_vencimiento, l.cantidad_actual,
               DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
        FROM lotes l
        INNER JOIN medicamentos m ON l.medicamento_id = m.id
        WHERE l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND l.fecha_vencimiento >= CURDATE()
        AND l.cantidad_actual > 0
        AND l.estado = 'disponible'
        ORDER BY l.fecha_vencimiento ASC
        LIMIT 10
    ");
    $proximosVencerDetalle = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Resumen</div>
                    <h2 class="page-title">Dashboard</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="pos.php" class="btn btn-primary d-none d-sm-inline-block">
                            <i class="ti ti-cash-register icon"></i>
                            Nueva Venta
                        </a>
                        <a href="pos.php" class="btn btn-primary d-sm-none btn-icon">
                            <i class="ti ti-cash-register icon"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <!-- Estadísticas principales -->
            <div class="row row-deck row-cards">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Ventas Hoy</div>
                            </div>
                            <div class="h1 mb-3">S/ <?php echo number_format($ventasHoy['total_monto'], 2); ?></div>
                            <div class="d-flex mb-2">
                                <div class="text-muted"><?php echo $ventasHoy['total_ventas']; ?> transacciones</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Ventas del Mes</div>
                            </div>
                            <div class="h1 mb-3">S/ <?php echo number_format($ventasMes['total_monto'], 2); ?></div>
                            <div class="d-flex mb-2">
                                <div class="text-muted"><?php echo $ventasMes['total_ventas']; ?> transacciones</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Stock Bajo</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-3 me-2"><?php echo $stockBajo['total']; ?></div>
                                <div class="me-auto">
                                    <span class="text-red d-inline-flex align-items-center lh-1">
                                        <i class="ti ti-alert-triangle icon ms-1"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex mb-2">
                                <div class="text-muted">Requieren reabastecimiento</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Próximos a Vencer</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-3 me-2"><?php echo $proximosVencer['total']; ?></div>
                                <div class="me-auto">
                                    <span class="text-yellow d-inline-flex align-items-center lh-1">
                                        <i class="ti ti-calendar-time icon ms-1"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex mb-2">
                                <div class="text-muted">Próximos 30 días</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de estadísticas -->
            <div class="row row-deck row-cards mt-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Medicamentos</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-0 me-2"><?php echo $totalMedicamentos['total']; ?></div>
                                <div class="me-auto">
                                    <span class="text-green d-inline-flex align-items-center lh-1">
                                        <i class="ti ti-pill icon ms-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Clientes</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h1 mb-0 me-2"><?php echo $totalClientes['total']; ?></div>
                                <div class="me-auto">
                                    <span class="text-blue d-inline-flex align-items-center lh-1">
                                        <i class="ti ti-users icon ms-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Estado del Sistema</div>
                            </div>
                            <div class="d-flex align-items-baseline">
                                <div class="h2 mb-0 me-2 text-success">Operativo</div>
                                <div class="me-auto">
                                    <span class="badge bg-success"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas -->
            <?php if ($stockBajo['total'] > 0 || $proximosVencer['total'] > 0): ?>
            <div class="row row-deck row-cards mt-3">
                <?php if ($stockBajo['total'] > 0): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-alert-triangle icon text-red me-2"></i>
                                Medicamentos con Stock Bajo
                            </h3>
                        </div>
                        <div class="card-body card-body-scrollable" style="max-height: 300px;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Medicamento</th>
                                            <th>Stock</th>
                                            <th>Mínimo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stockBajoDetalle as $item): ?>
                                        <tr class="low-stock">
                                            <td><?php echo htmlspecialchars($item['codigo']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['presentacion']); ?></small>
                                            </td>
                                            <td><span class="badge bg-red"><?php echo $item['stock_actual']; ?></span></td>
                                            <td><?php echo $item['stock_minimo']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="medicines.php?filter=low_stock" class="btn btn-link">Ver todos</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($proximosVencer['total'] > 0): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-calendar-time icon text-yellow me-2"></i>
                                Medicamentos Próximos a Vencer
                            </h3>
                        </div>
                        <div class="card-body card-body-scrollable" style="max-height: 300px;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Medicamento</th>
                                            <th>Lote</th>
                                            <th>Vencimiento</th>
                                            <th>Días</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($proximosVencerDetalle as $item): ?>
                                        <tr class="near-expiry">
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['numero_lote']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($item['fecha_vencimiento'])); ?></td>
                                            <td><span class="badge bg-yellow"><?php echo $item['dias_restantes']; ?> días</span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="lots.php?filter=expiring" class="btn btn-link">Ver todos</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Últimas ventas y productos más vendidos -->
            <div class="row row-deck row-cards mt-3">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-shopping-cart icon me-2"></i>
                                Últimas Ventas
                            </h3>
                        </div>
                        <div class="card-body card-body-scrollable" style="max-height: 400px;">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>N° Venta</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimasVentas as $venta): ?>
                                        <tr>
                                            <td><a href="sales-view.php?id=<?php echo $venta['id']; ?>"><?php echo htmlspecialchars($venta['numero_venta']); ?></a></td>
                                            <td>
                                                <?php
                                                if ($venta['nombre']) {
                                                    echo htmlspecialchars($venta['nombre'] . ' ' . $venta['apellido']);
                                                } else {
                                                    echo '<span class="text-muted">Cliente general</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><strong>S/ <?php echo number_format($venta['total'], 2); ?></strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="sales-list.php" class="btn btn-link">Ver todas las ventas</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-trending-up icon me-2"></i>
                                Productos Más Vendidos (Este Mes)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Medicamento</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($masVendidos) > 0): ?>
                                            <?php foreach ($masVendidos as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($item['presentacion']); ?></small>
                                                </td>
                                                <td class="text-end"><?php echo $item['cantidad_vendida']; ?></td>
                                                <td class="text-end"><strong>S/ <?php echo number_format($item['total_vendido'], 2); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No hay ventas este mes</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="reports-sales.php" class="btn btn-link">Ver reporte completo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
