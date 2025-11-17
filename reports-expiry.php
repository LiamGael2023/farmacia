<?php
$pageTitle = 'Reporte de Vencimientos - Sistema de Farmacia';
$currentPage = 'reports';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Lotes vencidos
$vencidos = $db->query("
    SELECT l.*, m.codigo, m.nombre as medicamento_nombre
    FROM lotes l
    INNER JOIN medicamentos m ON l.medicamento_id = m.id
    WHERE l.fecha_vencimiento < CURDATE()
    AND l.cantidad_actual > 0
    ORDER BY l.fecha_vencimiento DESC
")->fetchAll();

// Próximos a vencer (30 días)
$proximosVencer = $db->query("
    SELECT l.*, m.codigo, m.nombre as medicamento_nombre,
           DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
    FROM lotes l
    INNER JOIN medicamentos m ON l.medicamento_id = m.id
    WHERE l.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND l.cantidad_actual > 0
    AND l.estado = 'disponible'
    ORDER BY l.fecha_vencimiento ASC
")->fetchAll();

// Por vencer (31-60 días)
$porVencer = $db->query("
    SELECT l.*, m.codigo, m.nombre as medicamento_nombre,
           DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
    FROM lotes l
    INNER JOIN medicamentos m ON l.medicamento_id = m.id
    WHERE l.fecha_vencimiento BETWEEN DATE_ADD(CURDATE(), INTERVAL 31 DAY) AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    AND l.cantidad_actual > 0
    AND l.estado = 'disponible'
    ORDER BY l.fecha_vencimiento ASC
")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Reportes</div>
                    <h2 class="page-title">Reporte de Vencimientos</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <!-- Estadísticas -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader text-red">Lotes Vencidos</div>
                            <div class="h1 mb-0 text-red"><?php echo count($vencidos); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader text-warning">Próximos 30 Días</div>
                            <div class="h1 mb-0 text-warning"><?php echo count($proximosVencer); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader text-info">31-60 Días</div>
                            <div class="h1 mb-0 text-info"><?php echo count($porVencer); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-deck row-cards">
                <!-- Lotes vencidos -->
                <?php if (count($vencidos) > 0): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-alert-circle icon text-red me-2"></i>
                                Lotes Vencidos
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>N° Lote</th>
                                        <th>Medicamento</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vencidos as $item): ?>
                                    <tr class="expired">
                                        <td><?php echo htmlspecialchars($item['numero_lote']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['medicamento_nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($item['fecha_vencimiento'])); ?></td>
                                        <td><span class="badge bg-red"><?php echo $item['cantidad_actual']; ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger">Dar de Baja</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Próximos a vencer -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-calendar-time icon text-warning me-2"></i>
                                Próximos a Vencer (30 días)
                            </h3>
                        </div>
                        <div class="card-body card-body-scrollable" style="max-height: 500px;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Lote</th>
                                        <th>Vence</th>
                                        <th>Días</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximosVencer as $item): ?>
                                    <tr class="near-expiry">
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['medicamento_nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['numero_lote']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($item['fecha_vencimiento'])); ?></td>
                                        <td><span class="badge bg-warning"><?php echo $item['dias_restantes']; ?> días</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Por vencer (31-60 días) -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-calendar icon text-info me-2"></i>
                                Por Vencer (31-60 días)
                            </h3>
                        </div>
                        <div class="card-body card-body-scrollable" style="max-height: 500px;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Lote</th>
                                        <th>Vence</th>
                                        <th>Días</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($porVencer as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['medicamento_nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['codigo']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['numero_lote']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($item['fecha_vencimiento'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo $item['dias_restantes']; ?> días</span></td>
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
