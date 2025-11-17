<?php
$pageTitle = 'Usuarios - Sistema de Farmacia';
$currentPage = 'users';
require_once 'includes/header.php';
require_once 'config/database.php';

checkAuth();
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$db = getDB();
$usuarios = $db->query("SELECT * FROM usuarios ORDER BY nombre ASC")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Administración</div>
                    <h2 class="page-title">Usuarios del Sistema</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-user">
                        <i class="ti ti-plus icon"></i>
                        Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <table id="tableUsers" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Último Acceso</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['telefono'] ?? '-'); ?></td>
                                <td>
                                    <?php
                                    $badge = 'bg-secondary';
                                    switch($user['rol']) {
                                        case 'admin': $badge = 'bg-red'; break;
                                        case 'farmaceutico': $badge = 'bg-blue'; break;
                                        case 'vendedor': $badge = 'bg-green'; break;
                                        case 'cajero': $badge = 'bg-yellow'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($user['rol']); ?></span>
                                </td>
                                <td><?php echo $user['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) : 'Nunca'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-icon btn-info" title="Ver">
                                            <i class="ti ti-eye icon"></i>
                                        </a>
                                        <a href="user-edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-icon btn-primary" title="Editar">
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

<!-- Modal Nuevo Usuario -->
<div class="modal modal-blur fade" id="modal-user" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/users/save-user.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Usuario</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
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
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control" required>
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
                                <label class="form-label required">Rol</label>
                                <select name="rol" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <option value="admin">Administrador</option>
                                    <option value="farmaceutico">Farmacéutico</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="cajero">Cajero</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
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

<?php $customJS = '<script>$("#tableUsers").DataTable();</script>'; ?>
