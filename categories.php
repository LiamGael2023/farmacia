<?php
$pageTitle = 'Categorías - Sistema de Farmacia';
$currentPage = 'inventory';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventario</div>
                    <h2 class="page-title">Categorías de Medicamentos</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-category">
                        <i class="ti ti-plus icon"></i>
                        Nueva Categoría
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <?php foreach ($categorias as $cat): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader"><?php echo ucfirst($cat['estado']); ?></div>
                                <div class="ms-auto">
                                    <span class="badge bg-<?php echo $cat['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($cat['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="h3 mb-0"><?php echo htmlspecialchars($cat['nombre']); ?></div>
                            <div class="text-muted mt-2">
                                <?php echo htmlspecialchars($cat['descripcion'] ?? 'Sin descripción'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Nueva Categoría -->
<div class="modal modal-blur fade" id="modal-category" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/inventory/save-category.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
