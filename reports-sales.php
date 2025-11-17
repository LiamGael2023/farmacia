<?php
$pageTitle = 'Reporte de Ventas - Sistema de Farmacia';
$currentPage = 'reports';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener fechas del filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Estadísticas generales
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_ventas,
        COALESCE(SUM(total), 0) as total_ingresos,
        COALESCE(AVG(total), 0) as promedio_venta
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ?
    AND estado = 'completada'
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$estadisticas = $stmt->fetch();

// Ventas por día
$stmt = $db->prepare("
    SELECT
        DATE(fecha_venta) as fecha,
        COUNT(*) as cantidad,
        SUM(total) as total
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ?
    AND estado = 'completada'
    GROUP BY DATE(fecha_venta)
    ORDER BY fecha DESC
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$ventasPorDia = $stmt->fetchAll();

// Ventas por método de pago
$stmt = $db->prepare("
    SELECT
        metodo_pago,
        COUNT(*) as cantidad,
        SUM(total) as total
    FROM ventas
    WHERE DATE(fecha_venta) BETWEEN ? AND ?
    AND estado = 'completada'
    GROUP BY metodo_pago
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$ventasPorMetodo = $stmt->fetchAll();

// Productos más vendidos
$stmt = $db->prepare("
    SELECT
        m.codigo,
        m.nombre,
        m.presentacion,
        SUM(vd.cantidad) as cantidad_vendida,
        SUM(vd.subtotal) as total_vendido
    FROM ventas_detalle vd
    INNER JOIN medicamentos m ON vd.medicamento_id = m.id
    INNER JOIN ventas v ON vd.venta_id = v.id
    WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
    AND v.estado = 'completada'
    GROUP BY vd.medicamento_id
    ORDER BY cantidad_vendida DESC
    LIMIT 20
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$productosMasVendidos = $stmt->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Reportes</div>
                    <h2 class="page-title">Reporte de Ventas</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Filtros -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-filter icon"></i>
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Total Ventas</div>
                            <div class="h1 mb-0"><?php echo $estadisticas['total_ventas']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Total Ingresos</div>
                            <div class="h1 mb-0 text-success">S/ <?php echo number_format($estadisticas['total_ingresos'], 2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Promedio por Venta</div>
                            <div class="h1 mb-0 text-primary">S/ <?php echo number_format($estadisticas['promedio_venta'], 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-deck row-cards">
                <!-- Ventas por día -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ventas por Día</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventasPorDia as $dia): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($dia['fecha'])); ?></td>
                                        <td class="text-end"><?php echo $dia['cantidad']; ?></td>
                                        <td class="text-end"><strong>S/ <?php echo number_format($dia['total'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ventas por método de pago -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ventas por Método de Pago</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Método</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventasPorMetodo as $metodo): ?>
                                    <tr>
                                        <td><span class="badge bg-blue"><?php echo ucfirst($metodo['metodo_pago']); ?></span></td>
                                        <td class="text-end"><?php echo $metodo['cantidad']; ?></td>
                                        <td class="text-end"><strong>S/ <?php echo number_format($metodo['total'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Productos más vendidos -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top 20 Productos Más Vendidos</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Medicamento</th>
                                        <th class="text-end">Cantidad Vendida</th>
                                        <th class="text-end">Total Vendido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productosMasVendidos as $prod): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prod['codigo']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($prod['presentacion']); ?></small>
                                        </td>
                                        <td class="text-end"><span class="badge bg-green"><?php echo $prod['cantidad_vendida']; ?></span></td>
                                        <td class="text-end"><strong class="text-success">S/ <?php echo number_format($prod['total_vendido'], 2); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
