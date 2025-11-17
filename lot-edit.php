<?php
$pageTitle = 'Editar Lote - Sistema de Farmacia';
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
$stmt = $db->prepare("SELECT * FROM lotes WHERE id = ?");
$stmt->execute([$id]);
$lote = $stmt->fetch();

if (!$lote) {
    $_SESSION['error'] = 'Lote no encontrado';
    header('Location: lots.php');
    exit();
}

// Obtener datos para selects
$medicamentos = $db->query("SELECT id, codigo, nombre, presentacion FROM medicamentos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$proveedores = $db->query("SELECT id, nombre FROM proveedores WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Lotes</div>
                    <h2 class="page-title">Editar Lote</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="lot-view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/inventory/update-lot.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información del Lote</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">N° Lote</label>
                                            <input type="text" name="numero_lote" class="form-control" value="<?php echo htmlspecialchars($lote['numero_lote']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Medicamento</label>
                                            <select name="medicamento_id" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($medicamentos as $med): ?>
                                                    <option value="<?php echo $med['id']; ?>" <?php echo $med['id'] == $lote['medicamento_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($med['codigo'] . ' - ' . $med['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Proveedor</label>
                                            <select name="proveedor_id" class="form-select">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($proveedores as $prov): ?>
                                                    <option value="<?php echo $prov['id']; ?>" <?php echo $prov['id'] == $lote['proveedor_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($prov['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Fecha Fabricación</label>
                                            <input type="date" name="fecha_fabricacion" class="form-control" value="<?php echo $lote['fecha_fabricacion'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Fecha Vencimiento</label>
                                            <input type="date" name="fecha_vencimiento" class="form-control" value="<?php echo $lote['fecha_vencimiento']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Precio Compra</label>
                                            <div class="input-group">
                                                <span class="input-group-text">S/</span>
                                                <input type="number" name="precio_compra" class="form-control" step="0.01" value="<?php echo $lote['precio_compra']; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Cantidad Inicial</label>
                                            <input type="number" name="cantidad_inicial" class="form-control" value="<?php echo $lote['cantidad_inicial']; ?>" readonly>
                                            <small class="text-muted">No se puede modificar</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Cantidad Actual</label>
                                            <input type="number" name="cantidad_actual" class="form-control" value="<?php echo $lote['cantidad_actual']; ?>" min="0">
                                            <small class="text-muted">Ajuste manual de inventario</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Opciones</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="disponible" <?php echo $lote['estado'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                        <option value="vencido" <?php echo $lote['estado'] === 'vencido' ? 'selected' : ''; ?>>Vencido</option>
                                        <option value="agotado" <?php echo $lote['estado'] === 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-device-floppy icon"></i>
                                    Guardar Cambios
                                </button>
                                <a href="lot-view.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 mt-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
