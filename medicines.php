<?php
$pageTitle = 'Medicamentos - Sistema de Farmacia';
$currentPage = 'inventory';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener filtros
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

// Construir query
$query = "
    SELECT m.*, c.nombre as categoria_nombre
    FROM medicamentos m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE 1=1
";

if ($filter === 'low_stock') {
    $query .= " AND m.stock_actual <= m.stock_minimo";
}

if (!empty($search)) {
    $query .= " AND (m.nombre LIKE :search OR m.codigo LIKE :search OR m.nombre_generico LIKE :search OR m.laboratorio LIKE :search)";
}

$query .= " ORDER BY m.nombre ASC";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
}

$stmt->execute();
$medicamentos = $stmt->fetchAll();

// Obtener categorías para el modal
$categorias = $db->query("SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventario</div>
                    <h2 class="page-title">Medicamentos</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-medicine">
                            <i class="ti ti-plus icon"></i>
                            Nuevo Medicamento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Medicamentos</h3>
                            <div class="col-auto ms-auto">
                                <form method="GET" class="d-flex">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Buscar medicamento..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-icon">
                                            <i class="ti ti-search icon"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tableMedicines" class="table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Categoría</th>
                                            <th>Presentación</th>
                                            <th>Laboratorio</th>
                                            <th>Stock</th>
                                            <th>Precio Venta</th>
                                            <th>Estado</th>
                                            <th class="w-1">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medicamentos as $med): ?>
                                        <tr class="<?php echo ($med['stock_actual'] <= $med['stock_minimo']) ? 'low-stock' : ''; ?>">
                                            <td><?php echo htmlspecialchars($med['codigo']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($med['nombre']); ?></strong>
                                                <?php if ($med['nombre_generico']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($med['nombre_generico']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($med['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                            <td><?php echo htmlspecialchars($med['presentacion']); ?></td>
                                            <td><?php echo htmlspecialchars($med['laboratorio']); ?></td>
                                            <td>
                                                <?php if ($med['stock_actual'] <= $med['stock_minimo']): ?>
                                                    <span class="badge bg-red"><?php echo $med['stock_actual']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-green"><?php echo $med['stock_actual']; ?></span>
                                                <?php endif; ?>
                                                <small class="text-muted">/ <?php echo $med['stock_minimo']; ?></small>
                                            </td>
                                            <td>S/ <?php echo number_format($med['precio_venta'], 2); ?></td>
                                            <td>
                                                <?php if ($med['estado'] === 'activo'): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <button class="btn btn-sm btn-icon btn-primary" onclick="editMedicine(<?php echo $med['id']; ?>)" title="Editar">
                                                        <i class="ti ti-edit icon"></i>
                                                    </button>
                                                    <a href="medicine-view.php?id=<?php echo $med['id']; ?>" class="btn btn-sm btn-icon btn-info" title="Ver detalles">
                                                        <i class="ti ti-eye icon"></i>
                                                    </a>
                                                </div>
                                            </td>
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
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Nuevo/Editar Medicamento -->
<div class="modal modal-blur fade" id="modal-medicine" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="modules/inventory/save-medicine.php" id="formMedicine">
                <input type="hidden" name="id" id="medicine_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Nuevo Medicamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Código</label>
                                <input type="text" name="codigo" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Categoría</label>
                                <select name="categoria_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label required">Nombre Comercial</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Nombre Genérico</label>
                                <input type="text" name="nombre_generico" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Presentación</label>
                                <input type="text" name="presentacion" class="form-control" placeholder="Ej: Caja x 20 tabletas">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Concentración</label>
                                <input type="text" name="concentracion" class="form-control" placeholder="Ej: 500mg">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Laboratorio</label>
                                <input type="text" name="laboratorio" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Principio Activo</label>
                                <input type="text" name="principio_activo" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Precio Compra</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" name="precio_compra" class="form-control" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Precio Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" name="precio_venta" class="form-control" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Inicial</label>
                                <input type="number" name="stock_actual" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Mínimo</label>
                                <input type="number" name="stock_minimo" class="form-control" value="10">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Ubicación</label>
                                <input type="text" name="ubicacion" class="form-control" placeholder="Ej: Estante A-5">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="requiere_receta" value="1">
                                    <span class="form-check-label">Requiere Receta Médica</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
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

<?php
$customJS = <<<'JS'
<script>
$(document).ready(function() {
    $('#tableMedicines').DataTable({
        pageLength: 25,
        order: [[1, 'asc']]
    });
});

function editMedicine(id) {
    // Aquí iría la lógica para cargar los datos del medicamento y abrir el modal
    alert('Función de edición - ID: ' + id);
}
</script>
JS;
?>
