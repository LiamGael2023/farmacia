<?php
$pageTitle = 'Detalle de Cliente - Sistema de Farmacia';
$currentPage = 'customers';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: customers.php');
    exit();
}

// Obtener cliente
$stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    $_SESSION['error'] = 'Cliente no encontrado';
    header('Location: customers.php');
    exit();
}

// Obtener historial de compras
$stmt = $db->prepare("
    SELECT v.*
    FROM ventas v
    WHERE v.cliente_id = ?
    ORDER BY v.fecha_venta DESC
    LIMIT 20
");
$stmt->execute([$id]);
$compras = $stmt->fetchAll();

// Estadísticas
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_compras,
        COALESCE(SUM(total), 0) as total_gastado,
        COALESCE(AVG(total), 0) as promedio_compra
    FROM ventas
    WHERE cliente_id = ? AND estado = 'completada'
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
                    <div class="page-pretitle">Clientes</div>
                    <h2 class="page-title"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="customers.php" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Volver
                    </a>
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
                            <h3 class="card-title">Información Personal</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($cliente['dni']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">DNI</label>
                                <div><strong><?php echo htmlspecialchars($cliente['dni']); ?></strong></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre Completo</label>
                                <div><strong><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong></div>
                            </div>
                            <?php if ($cliente['fecha_nacimiento']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Nacimiento</label>
                                <div><?php echo date('d/m/Y', strtotime($cliente['fecha_nacimiento'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($cliente['sexo']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Sexo</label>
                                <div><?php echo $cliente['sexo']; ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($cliente['telefono']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Teléfono</label>
                                <div><?php echo htmlspecialchars($cliente['telefono']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($cliente['email']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <div><?php echo htmlspecialchars($cliente['email']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($cliente['direccion']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Dirección</label>
                                <div><?php echo nl2br(htmlspecialchars($cliente['direccion'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($cliente['alergias']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Alergias</label>
                                <div class="text-danger"><?php echo nl2br(htmlspecialchars($cliente['alergias'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($cliente['observaciones']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Observaciones</label>
                                <div><?php echo nl2br(htmlspecialchars($cliente['observaciones'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <span class="badge bg-<?php echo $cliente['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($cliente['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Registro</label>
                                <div><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></div>
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
                                        <label class="form-label text-muted">Total Gastado</label>
                                        <div class="h3 text-success">S/ <?php echo number_format($stats['total_gastado'], 2); ?></div>
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

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Historial de Compras</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>N° Venta</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($compras) > 0): ?>
                                        <?php foreach ($compras as $compra): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($compra['numero_venta']); ?></strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha_venta'])); ?></td>
                                            <td><strong>S/ <?php echo number_format($compra['total'], 2); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $compra['estado'] === 'completada' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($compra['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="sales-view.php?id=<?php echo $compra['id']; ?>" class="btn btn-sm btn-icon btn-info">
                                                    <i class="ti ti-eye icon"></i>
                                                </a>
                                            </td>
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
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
