<?php
$pageTitle = 'Detalle de Medicamento - Sistema de Farmacia';
$currentPage = 'inventory';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: medicines.php');
    exit();
}

// Obtener medicamento
$stmt = $db->prepare("
    SELECT m.*, c.nombre as categoria_nombre
    FROM medicamentos m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$medicamento = $stmt->fetch();

if (!$medicamento) {
    $_SESSION['error'] = 'Medicamento no encontrado';
    header('Location: medicines.php');
    exit();
}

// Obtener lotes del medicamento
$stmt = $db->prepare("
    SELECT l.*, p.nombre as proveedor_nombre,
           DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
    FROM lotes l
    LEFT JOIN proveedores p ON l.proveedor_id = p.id
    WHERE l.medicamento_id = ?
    ORDER BY l.fecha_vencimiento ASC
");
$stmt->execute([$id]);
$lotes = $stmt->fetchAll();

// Obtener últimos movimientos
$stmt = $db->prepare("
    SELECT mi.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM movimientos_inventario mi
    INNER JOIN usuarios u ON mi.usuario_id = u.id
    WHERE mi.medicamento_id = ?
    ORDER BY mi.fecha_movimiento DESC
    LIMIT 20
");
$stmt->execute([$id]);
$movimientos = $stmt->fetchAll();

// Obtener estadísticas de ventas
$stmt = $db->prepare("
    SELECT
        SUM(vd.cantidad) as total_vendido,
        COUNT(DISTINCT v.id) as num_ventas,
        SUM(vd.subtotal) as total_ingresos
    FROM ventas_detalle vd
    INNER JOIN ventas v ON vd.venta_id = v.id
    WHERE vd.medicamento_id = ?
    AND v.estado = 'completada'
");
$stmt->execute([$id]);
$estadisticas = $stmt->fetch();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventario</div>
                    <h2 class="page-title"><?php echo htmlspecialchars($medicamento['nombre']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="medicine-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="ti ti-edit icon"></i>
                            Editar
                        </a>
                        <a href="medicines.php" class="btn btn-secondary">
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
                <!-- Información General -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información General</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Código</label>
                                <div><strong><?php echo htmlspecialchars($medicamento['codigo']); ?></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre Comercial</label>
                                <div><strong><?php echo htmlspecialchars($medicamento['nombre']); ?></strong></div>
                            </div>
                            <?php if ($medicamento['nombre_generico']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre Genérico</label>
                                <div><?php echo htmlspecialchars($medicamento['nombre_generico']); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Categoría</label>
                                <div><span class="badge bg-blue"><?php echo htmlspecialchars($medicamento['categoria_nombre'] ?? 'Sin categoría'); ?></span></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Presentación</label>
                                <div><?php echo htmlspecialchars($medicamento['presentacion'] ?? '-'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Concentración</label>
                                <div><?php echo htmlspecialchars($medicamento['concentracion'] ?? '-'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Laboratorio</label>
                                <div><?php echo htmlspecialchars($medicamento['laboratorio'] ?? '-'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Principio Activo</label>
                                <div><?php echo htmlspecialchars($medicamento['principio_activo'] ?? '-'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Ubicación</label>
                                <div><?php echo htmlspecialchars($medicamento['ubicacion'] ?? '-'); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Requiere Receta</label>
                                <div>
                                    <?php if ($medicamento['requiere_receta']): ?>
                                        <span class="badge bg-red">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-green">No</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <?php if ($medicamento['estado'] === 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($medicamento['descripcion']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Descripción</label>
                                <div><?php echo nl2br(htmlspecialchars($medicamento['descripcion'])); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Precios y Stock -->
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Precios y Stock</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Precio Compra</label>
                                        <div class="h3">S/ <?php echo number_format($medicamento['precio_compra'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Precio Venta</label>
                                        <div class="h3 text-success">S/ <?php echo number_format($medicamento['precio_venta'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Margen</label>
                                        <div class="h3 text-primary">
                                            <?php
                                            $margen = (($medicamento['precio_venta'] - $medicamento['precio_compra']) / $medicamento['precio_compra']) * 100;
                                            echo number_format($margen, 1);
                                            ?>%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Ganancia Unit.</label>
                                        <div class="h3 text-info">S/ <?php echo number_format($medicamento['precio_venta'] - $medicamento['precio_compra'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Stock Actual</label>
                                        <div class="h2">
                                            <?php if ($medicamento['stock_actual'] <= $medicamento['stock_minimo']): ?>
                                                <span class="badge bg-red" style="font-size: 1.5rem;"><?php echo $medicamento['stock_actual']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-green" style="font-size: 1.5rem;"><?php echo $medicamento['stock_actual']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Stock Mínimo</label>
                                        <div class="h3"><?php echo $medicamento['stock_minimo']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Valor en Stock</label>
                                        <div class="h3">S/ <?php echo number_format($medicamento['stock_actual'] * $medicamento['precio_compra'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de Ventas -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Estadísticas de Ventas</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Total Vendido</label>
                                        <div class="h3"><?php echo $estadisticas['total_vendido'] ?? 0; ?> unidades</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">N° Ventas</label>
                                        <div class="h3"><?php echo $estadisticas['num_ventas'] ?? 0; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Total Ingresos</label>
                                        <div class="h3 text-success">S/ <?php echo number_format($estadisticas['total_ingresos'] ?? 0, 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lotes -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Lotes Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>N° Lote</th>
                                        <th>Proveedor</th>
                                        <th>Vencimiento</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($lotes) > 0): ?>
                                        <?php foreach ($lotes as $lote): ?>
                                        <tr class="<?php
                                            if ($lote['dias_restantes'] < 0) echo 'expired';
                                            elseif ($lote['dias_restantes'] <= 30) echo 'near-expiry';
                                        ?>">
                                            <td><?php echo htmlspecialchars($lote['numero_lote']); ?></td>
                                            <td><?php echo htmlspecialchars($lote['proveedor_nombre'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($lote['fecha_vencimiento'])); ?>
                                                <?php if ($lote['dias_restantes'] < 0): ?>
                                                    <span class="badge bg-danger ms-2">Vencido</span>
                                                <?php elseif ($lote['dias_restantes'] <= 30): ?>
                                                    <span class="badge bg-warning ms-2"><?php echo $lote['dias_restantes']; ?> días</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge <?php echo $lote['cantidad_actual'] > 0 ? 'bg-green' : 'bg-red'; ?>"><?php echo $lote['cantidad_actual']; ?></span></td>
                                            <td>
                                                <?php if ($lote['estado'] === 'disponible'): ?>
                                                    <span class="badge bg-success">Disponible</span>
                                                <?php elseif ($lote['estado'] === 'vencido'): ?>
                                                    <span class="badge bg-danger">Vencido</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Agotado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay lotes registrados</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Últimos Movimientos -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Últimos Movimientos</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
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
                                                $badge_color = 'bg-secondary';
                                                switch($mov['tipo_movimiento']) {
                                                    case 'entrada': $badge_color = 'bg-success'; break;
                                                    case 'salida': $badge_color = 'bg-danger'; break;
                                                    case 'ajuste': $badge_color = 'bg-warning'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_color; ?>">
                                                    <?php echo ucfirst($mov['tipo_movimiento']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($mov['tipo_movimiento'] === 'entrada'): ?>
                                                    <span class="text-success">+<?php echo $mov['cantidad']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger">-<?php echo $mov['cantidad']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($mov['motivo'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($mov['usuario_nombre'] . ' ' . $mov['usuario_apellido']); ?></td>
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
