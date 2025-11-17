<?php
$pageTitle = 'Detalle de Lote - Sistema de Farmacia';
$currentPage = 'lots';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: lots.php');
    exit();
}

// Obtener lote
$stmt = $db->prepare("
    SELECT l.*, m.codigo, m.nombre as medicamento_nombre, m.presentacion,
           p.nombre as proveedor_nombre, p.telefono as proveedor_telefono,
           DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
    FROM lotes l
    INNER JOIN medicamentos m ON l.medicamento_id = m.id
    LEFT JOIN proveedores p ON l.proveedor_id = p.id
    WHERE l.id = ?
");
$stmt->execute([$id]);
$lote = $stmt->fetch();

if (!$lote) {
    $_SESSION['error'] = 'Lote no encontrado';
    header('Location: lots.php');
    exit();
}

// Obtener movimientos del lote
$stmt = $db->prepare("
    SELECT mi.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM movimientos_inventario mi
    LEFT JOIN usuarios u ON mi.usuario_id = u.id
    WHERE mi.lote_id = ?
    ORDER BY mi.fecha_movimiento DESC
    LIMIT 20
");
$stmt->execute([$id]);
$movimientos = $stmt->fetchAll();

// Calcular valor del lote
$valor_actual = $lote['cantidad_actual'] * $lote['precio_compra'];
$valor_inicial = $lote['cantidad_inicial'] * $lote['precio_compra'];
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Lotes</div>
                    <h2 class="page-title">Lote <?php echo htmlspecialchars($lote['numero_lote']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="lot-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="ti ti-edit icon"></i>
                            Editar
                        </a>
                        <a href="lots.php" class="btn btn-secondary">
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
                            <h3 class="card-title">Información del Lote</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">N° Lote</label>
                                <div><strong><?php echo htmlspecialchars($lote['numero_lote']); ?></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Medicamento</label>
                                <div><strong><?php echo htmlspecialchars($lote['medicamento_nombre']); ?></strong></div>
                                <small class="text-muted"><?php echo htmlspecialchars($lote['codigo'] . ' - ' . $lote['presentacion']); ?></small>
                            </div>
                            <?php if ($lote['proveedor_nombre']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Proveedor</label>
                                <div><?php echo htmlspecialchars($lote['proveedor_nombre']); ?></div>
                                <?php if ($lote['proveedor_telefono']): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($lote['proveedor_telefono']); ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($lote['fecha_fabricacion']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Fabricación</label>
                                <div><?php echo date('d/m/Y', strtotime($lote['fecha_fabricacion'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Vencimiento</label>
                                <div><?php echo date('d/m/Y', strtotime($lote['fecha_vencimiento'])); ?></div>
                                <?php if ($lote['dias_restantes'] < 0): ?>
                                    <span class="badge bg-danger">Vencido</span>
                                <?php elseif ($lote['dias_restantes'] <= 30): ?>
                                    <span class="badge bg-warning"><?php echo $lote['dias_restantes']; ?> días restantes</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?php echo $lote['dias_restantes']; ?> días restantes</span>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <?php if ($lote['estado'] === 'disponible'): ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php elseif ($lote['estado'] === 'vencido'): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Agotado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Ingreso</label>
                                <div><?php echo date('d/m/Y H:i', strtotime($lote['fecha_ingreso'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Resumen de Cantidades</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Cantidad Inicial</label>
                                        <div class="h3"><?php echo $lote['cantidad_inicial']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Cantidad Actual</label>
                                        <div class="h3 <?php echo $lote['cantidad_actual'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $lote['cantidad_actual']; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Unidades Vendidas</label>
                                        <div class="h3 text-info"><?php echo $lote['cantidad_inicial'] - $lote['cantidad_actual']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">% Disponible</label>
                                        <div class="h3 text-primary">
                                            <?php echo $lote['cantidad_inicial'] > 0 ? round(($lote['cantidad_actual'] / $lote['cantidad_inicial']) * 100, 1) : 0; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Información Económica</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Precio de Compra (Unit.)</label>
                                        <div class="h3 text-success">S/ <?php echo number_format($lote['precio_compra'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Valor Actual</label>
                                        <div class="h3 text-primary">S/ <?php echo number_format($valor_actual, 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Valor Inicial</label>
                                        <div class="h3 text-info">S/ <?php echo number_format($valor_inicial, 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Historial de Movimientos</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($movimientos) > 0): ?>
                                        <?php foreach ($movimientos as $mov): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])); ?></td>
                                            <td>
                                                <?php
                                                $badge = 'bg-secondary';
                                                switch($mov['tipo_movimiento']) {
                                                    case 'entrada': $badge = 'bg-success'; break;
                                                    case 'salida': $badge = 'bg-info'; break;
                                                    case 'ajuste': $badge = 'bg-warning'; break;
                                                    case 'devolucion': $badge = 'bg-primary'; break;
                                                    case 'vencimiento': $badge = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($mov['tipo_movimiento']); ?></span>
                                            </td>
                                            <td><strong><?php echo $mov['cantidad']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($mov['motivo'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars(($mov['usuario_nombre'] ?? 'Sistema') . ' ' . ($mov['usuario_apellido'] ?? '')); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay movimientos registrados</td>
                                        </tr>
                                    <?php endif; ?>
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
