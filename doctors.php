<?php
$pageTitle = 'Médicos - Sistema de Farmacia';
$currentPage = 'doctors';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$medicos = $db->query("SELECT * FROM medicos ORDER BY nombre ASC")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Gestión</div>
                    <h2 class="page-title">Médicos</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-doctor">
                        <i class="ti ti-plus icon"></i>
                        Nuevo Médico
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <table id="tableDoctors" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>N° Colegiatura</th>
                                <th>Nombre</th>
                                <th>Especialidad</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicos as $med): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($med['numero_colegiatura'] ?? '-'); ?></td>
                                <td><strong><?php echo htmlspecialchars($med['nombre'] . ' ' . $med['apellido']); ?></strong></td>
                                <td><?php echo htmlspecialchars($med['especialidad'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($med['telefono'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($med['email'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $med['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($med['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-icon btn-info" title="Ver">
                                            <i class="ti ti-eye icon"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-primary" title="Editar">
                                            <i class="ti ti-edit icon"></i>
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

<!-- Modal Nuevo Médico -->
<div class="modal modal-blur fade" id="modal-doctor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/doctors/save-doctor.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Médico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Apellido</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">N° Colegiatura</label>
                                <input type="text" name="numero_colegiatura" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
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

<?php $customJS = '<script>$("#tableDoctors").DataTable();</script>'; ?>
