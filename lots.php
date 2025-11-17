<?php
$pageTitle = 'Lotes de Medicamentos - Sistema de Farmacia';
$currentPage = 'lots';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener lotes
$lotes = $db->query("
    SELECT l.*, m.codigo, m.nombre as medicamento_nombre, m.presentacion,
           p.nombre as proveedor_nombre,
           DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
    FROM lotes l
    INNER JOIN medicamentos m ON l.medicamento_id = m.id
    LEFT JOIN proveedores p ON l.proveedor_id = p.id
    ORDER BY l.fecha_vencimiento ASC
")->fetchAll();

// Obtener medicamentos para el modal
$medicamentos = $db->query("SELECT id, codigo, nombre, presentacion FROM medicamentos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$proveedores = $db->query("SELECT id, nombre FROM proveedores WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Inventario</div>
                    <h2 class="page-title">Lotes de Medicamentos</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-lot">
                        <i class="ti ti-plus icon"></i>
                        Nuevo Lote
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Lotes</h3>
                </div>
                <div class="card-body">
                    <table id="tableLots" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>N° Lote</th>
                                <th>Medicamento</th>
                                <th>Proveedor</th>
                                <th>Fabricación</th>
                                <th>Vencimiento</th>
                                <th>Días Restantes</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lotes as $lote): ?>
                            <tr class="<?php
                                if ($lote['estado'] === 'vencido' || $lote['dias_restantes'] < 0) {
                                    echo 'expired';
                                } elseif ($lote['dias_restantes'] <= 30) {
                                    echo 'near-expiry';
                                }
                            ?>">
                                <td><strong><?php echo htmlspecialchars($lote['numero_lote']); ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($lote['medicamento_nombre']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($lote['codigo']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($lote['proveedor_nombre'] ?? 'N/A'); ?></td>
                                <td><?php echo $lote['fecha_fabricacion'] ? date('d/m/Y', strtotime($lote['fecha_fabricacion'])) : 'N/A'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($lote['fecha_vencimiento'])); ?></td>
                                <td>
                                    <?php if ($lote['dias_restantes'] < 0): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                    <?php elseif ($lote['dias_restantes'] <= 30): ?>
                                        <span class="badge bg-warning"><?php echo $lote['dias_restantes']; ?> días</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $lote['dias_restantes']; ?> días</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $lote['cantidad_actual'] > 0 ? 'bg-green' : 'bg-red'; ?>">
                                        <?php echo $lote['cantidad_actual']; ?> / <?php echo $lote['cantidad_inicial']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($lote['estado'] === 'disponible'): ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php elseif ($lote['estado'] === 'vencido'): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="lot-view.php?id=<?php echo $lote['id']; ?>" class="btn btn-sm btn-icon btn-info" title="Ver">
                                            <i class="ti ti-eye icon"></i>
                                        </a>
                                        <a href="lot-edit.php?id=<?php echo $lote['id']; ?>" class="btn btn-sm btn-icon btn-primary" title="Editar">
                                            <i class="ti ti-edit icon"></i>
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

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Nuevo Lote -->
<div class="modal modal-blur fade" id="modal-lot" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/inventory/save-lot.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Lote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Medicamento</label>
                                <select name="medicamento_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($medicamentos as $med): ?>
                                        <option value="<?php echo $med['id']; ?>">
                                            <?php echo htmlspecialchars($med['codigo'] . ' - ' . $med['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Proveedor</label>
                                <select name="proveedor_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($proveedores as $prov): ?>
                                        <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">N° Lote</label>
                                <input type="text" name="numero_lote" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" required min="1">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha Fabricación</label>
                                <input type="date" name="fecha_fabricacion" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Fecha Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" class="form-control" required>
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

<?php $customJS = '<script>$("#tableLots").DataTable();</script>'; ?>
