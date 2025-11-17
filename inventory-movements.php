<?php
$pageTitle = 'Movimientos de Inventario - Sistema de Farmacia';
$currentPage = 'inventory';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener movimientos
$movimientos = $db->query("
    SELECT mi.*, m.codigo, m.nombre as medicamento_nombre,
           u.nombre as usuario_nombre, u.apellido as usuario_apellido,
           l.numero_lote
    FROM movimientos_inventario mi
    INNER JOIN medicamentos m ON mi.medicamento_id = m.id
    INNER JOIN usuarios u ON mi.usuario_id = u.id
    LEFT JOIN lotes l ON mi.lote_id = l.id
    ORDER BY mi.fecha_movimiento DESC
    LIMIT 500
")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventario</div>
                    <h2 class="page-title">Movimientos de Inventario</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Historial de Movimientos</h3>
                </div>
                <div class="card-body">
                    <table id="tableMovements" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Motivo</th>
                                <th>Referencia</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])); ?></td>
                                <td>
                                    <?php
                                    $badge_color = 'bg-secondary';
                                    switch($mov['tipo_movimiento']) {
                                        case 'entrada': $badge_color = 'bg-success'; break;
                                        case 'salida': $badge_color = 'bg-danger'; break;
                                        case 'ajuste': $badge_color = 'bg-warning'; break;
                                        case 'devolucion': $badge_color = 'bg-info'; break;
                                        case 'vencimiento': $badge_color = 'bg-red'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_color; ?>">
                                        <?php echo ucfirst($mov['tipo_movimiento']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($mov['medicamento_nombre']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($mov['codigo']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($mov['numero_lote'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($mov['tipo_movimiento'] === 'entrada'): ?>
                                        <span class="text-success">+<?php echo $mov['cantidad']; ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">-<?php echo $mov['cantidad']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($mov['motivo'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($mov['referencia'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($mov['usuario_nombre'] . ' ' . $mov['usuario_apellido']); ?></td>
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

<?php $customJS = '<script>$("#tableMovements").DataTable({order: [[0, "desc"]]});</script>'; ?>
