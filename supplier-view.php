<?php
$pageTitle = 'Detalle de Proveedor - Sistema de Farmacia';
$currentPage = 'suppliers';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: suppliers.php');
    exit();
}

// Obtener proveedor
$stmt = $db->prepare("SELECT * FROM proveedores WHERE id = ?");
$stmt->execute([$id]);
$proveedor = $stmt->fetch();

if (!$proveedor) {
    $_SESSION['error'] = 'Proveedor no encontrado';
    header('Location: suppliers.php');
    exit();
}

// Obtener compras al proveedor
$stmt = $db->prepare("
    SELECT c.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM compras c
    INNER JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.proveedor_id = ?
    ORDER BY c.fecha_compra DESC
    LIMIT 20
");
$stmt->execute([$id]);
$compras = $stmt->fetchAll();

// Obtener lotes del proveedor
$stmt = $db->prepare("
    SELECT l.*, m.codigo, m.nombre as medicamento_nombre,
           DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
    FROM lotes l
    INNER JOIN medicamentos m ON l.medicamento_id = m.id
    WHERE l.proveedor_id = ?
    ORDER BY l.fecha_vencimiento ASC
    LIMIT 20
");
$stmt->execute([$id]);
$lotes = $stmt->fetchAll();

// Estadísticas
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_compras,
        COALESCE(SUM(total), 0) as total_comprado,
        COALESCE(AVG(total), 0) as promedio_compra
    FROM compras
    WHERE proveedor_id = ? AND estado != 'cancelada'
");
$stmt->execute([$id]);
$stats = $stmt->fetch();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Proveedores</div>
                    <h2 class="page-title"><?php echo htmlspecialchars($proveedor['nombre']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="supplier-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="ti ti-edit icon"></i>
                            Editar
                        </a>
                        <a href="suppliers.php" class="btn btn-secondary">
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
                            <h3 class="card-title">Información del Proveedor</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre / Razón Social</label>
                                <div><strong><?php echo htmlspecialchars($proveedor['nombre']); ?></strong></div>
                            </div>
                            <?php if ($proveedor['ruc']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">RUC</label>
                                <div><?php echo htmlspecialchars($proveedor['ruc']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($proveedor['direccion']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Dirección</label>
                                <div><?php echo nl2br(htmlspecialchars($proveedor['direccion'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($proveedor['telefono']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Teléfono</label>
                                <div><?php echo htmlspecialchars($proveedor['telefono']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($proveedor['email']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <div><?php echo htmlspecialchars($proveedor['email']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($proveedor['contacto_nombre']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Contacto</label>
                                <div>
                                    <strong><?php echo htmlspecialchars($proveedor['contacto_nombre']); ?></strong>
                                    <?php if ($proveedor['contacto_telefono']): ?>
                                        <br><small><?php echo htmlspecialchars($proveedor['contacto_telefono']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <span class="badge bg-<?php echo $proveedor['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($proveedor['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Registro</label>
                                <div><?php echo date('d/m/Y', strtotime($proveedor['fecha_creacion'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Estadísticas</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Total Compras</label>
                                        <div class="h3"><?php echo $stats['total_compras']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Total Comprado</label>
                                        <div class="h3 text-success">S/ <?php echo number_format($stats['total_comprado'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Promedio por Compra</label>
                                        <div class="h3 text-primary">S/ <?php echo number_format($stats['promedio_compra'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Historial de Compras</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>N° Compra</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($compras) > 0): ?>
                                        <?php foreach ($compras as $compra): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($compra['numero_compra']); ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?></td>
                                            <td><strong>S/ <?php echo number_format($compra['total'], 2); ?></strong></td>
                                            <td>
                                                <?php
                                                $badge = 'bg-secondary';
                                                switch($compra['estado']) {
                                                    case 'pendiente': $badge = 'bg-warning'; break;
                                                    case 'recibida': $badge = 'bg-success'; break;
                                                    case 'parcial': $badge = 'bg-info'; break;
                                                    case 'cancelada': $badge = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($compra['estado']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($compra['usuario_nombre'] . ' ' . $compra['usuario_apellido']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay compras registradas</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lotes Suministrados</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>N° Lote</th>
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
                                            <td>
                                                <strong><?php echo htmlspecialchars($lote['medicamento_nombre']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($lote['codigo']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($lote['numero_lote']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($lote['fecha_vencimiento'])); ?></td>
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
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
