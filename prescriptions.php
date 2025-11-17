<?php
$pageTitle = 'Prescripciones Médicas - Sistema de Farmacia';
$currentPage = 'prescriptions';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener prescripciones
$prescripciones = $db->query("
    SELECT p.*,
           c.nombre as cliente_nombre, c.apellido as cliente_apellido,
           m.nombre as medico_nombre, m.apellido as medico_apellido
    FROM prescripciones p
    INNER JOIN clientes c ON p.cliente_id = c.id
    LEFT JOIN medicos m ON p.medico_id = m.id
    ORDER BY p.fecha_emision DESC
")->fetchAll();

// Obtener datos para el modal
$clientes = $db->query("SELECT id, nombre, apellido, dni FROM clientes WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$medicos = $db->query("SELECT id, nombre, apellido, especialidad FROM medicos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Gestión</div>
                    <h2 class="page-title">Prescripciones Médicas</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-prescription">
                        <i class="ti ti-plus icon"></i>
                        Nueva Prescripción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Prescripciones</h3>
                </div>
                <div class="card-body">
                    <table id="tablePrescriptions" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>N° Receta</th>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Diagnóstico</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescripciones as $pres): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pres['numero_receta']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($pres['fecha_emision'])); ?></td>
                                <td><?php echo htmlspecialchars($pres['cliente_nombre'] . ' ' . $pres['cliente_apellido']); ?></td>
                                <td><?php echo htmlspecialchars(($pres['medico_nombre'] ?? 'N/A') . ' ' . ($pres['medico_apellido'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars(substr($pres['diagnostico'] ?? '-', 0, 50)); ?></td>
                                <td>
                                    <?php
                                    $badge = 'bg-secondary';
                                    switch($pres['estado']) {
                                        case 'pendiente': $badge = 'bg-warning'; break;
                                        case 'dispensada': $badge = 'bg-success'; break;
                                        case 'parcial': $badge = 'bg-info'; break;
                                        case 'cancelada': $badge = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($pres['estado']); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-icon btn-info" title="Ver">
                                            <i class="ti ti-eye icon"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-success" title="Dispensar">
                                            <i class="ti ti-pill icon"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-primary" title="Imprimir">
                                            <i class="ti ti-printer icon"></i>
                                        </button>
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

<!-- Modal Nueva Prescripción -->
<div class="modal modal-blur fade" id="modal-prescription" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/prescriptions/save-prescription.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Prescripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">N° Receta</label>
                                <input type="text" name="numero_receta" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Fecha Emisión</label>
                                <input type="date" name="fecha_emision" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Paciente</label>
                                <select name="cliente_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?php echo $cli['id']; ?>">
                                            <?php echo htmlspecialchars($cli['nombre'] . ' ' . $cli['apellido'] . ($cli['dni'] ? ' - ' . $cli['dni'] : '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Médico</label>
                                <select name="medico_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($medicos as $med): ?>
                                        <option value="<?php echo $med['id']; ?>">
                                            <?php echo htmlspecialchars($med['nombre'] . ' ' . $med['apellido'] . ($med['especialidad'] ? ' - ' . $med['especialidad'] : '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Diagnóstico</label>
                                <textarea name="diagnostico" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"></textarea>
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

<?php $customJS = '<script>$("#tablePrescriptions").DataTable();</script>'; ?>
