<?php
$pageTitle = 'Editar Medicamento - Sistema de Farmacia';
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
$stmt = $db->prepare("SELECT * FROM medicamentos WHERE id = ?");
$stmt->execute([$id]);
$medicamento = $stmt->fetch();

if (!$medicamento) {
    $_SESSION['error'] = 'Medicamento no encontrado';
    header('Location: medicines.php');
    exit();
}

// Obtener categorías
$categorias = $db->query("SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventario</div>
                    <h2 class="page-title">Editar Medicamento</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="medicine-view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/inventory/save-medicine.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información del Medicamento</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Código</label>
                                            <input type="text" name="codigo" class="form-control" value="<?php echo htmlspecialchars($medicamento['codigo']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Categoría</label>
                                            <select name="categoria_id" class="form-select">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($medicamento['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label required">Nombre Comercial</label>
                                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($medicamento['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre Genérico</label>
                                            <input type="text" name="nombre_generico" class="form-control" value="<?php echo htmlspecialchars($medicamento['nombre_generico'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Presentación</label>
                                            <input type="text" name="presentacion" class="form-control" value="<?php echo htmlspecialchars($medicamento['presentacion'] ?? ''); ?>" placeholder="Ej: Caja x 20 tabletas">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Concentración</label>
                                            <input type="text" name="concentracion" class="form-control" value="<?php echo htmlspecialchars($medicamento['concentracion'] ?? ''); ?>" placeholder="Ej: 500mg">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Laboratorio</label>
                                            <input type="text" name="laboratorio" class="form-control" value="<?php echo htmlspecialchars($medicamento['laboratorio'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Principio Activo</label>
                                            <input type="text" name="principio_activo" class="form-control" value="<?php echo htmlspecialchars($medicamento['principio_activo'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($medicamento['descripcion'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Precios y Stock</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label required">Precio Compra</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" name="precio_compra" class="form-control" value="<?php echo $medicamento['precio_compra']; ?>" step="0.01" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Precio Venta</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" name="precio_venta" class="form-control" value="<?php echo $medicamento['precio_venta']; ?>" step="0.01" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stock Mínimo</label>
                                    <input type="number" name="stock_minimo" class="form-control" value="<?php echo $medicamento['stock_minimo']; ?>">
                                    <small class="form-hint">Stock actual: <?php echo $medicamento['stock_actual']; ?></small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ubicación en Almacén</label>
                                    <input type="text" name="ubicacion" class="form-control" value="<?php echo htmlspecialchars($medicamento['ubicacion'] ?? ''); ?>" placeholder="Ej: Estante A-5">
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Opciones</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="requiere_receta" value="1" <?php echo $medicamento['requiere_receta'] ? 'checked' : ''; ?>>
                                        <span class="form-check-label">Requiere Receta Médica</span>
                                    </label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="activo" <?php echo $medicamento['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?php echo $medicamento['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
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
                                <a href="medicine-view.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 mt-2">
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
