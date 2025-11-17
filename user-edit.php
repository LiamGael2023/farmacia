<?php
$pageTitle = 'Editar Usuario - Sistema de Farmacia';
$currentPage = 'users';
require_once 'includes/header.php';
require_once 'config/database.php';

checkAuth();
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: users.php');
    exit();
}

// Obtener usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION['error'] = 'Usuario no encontrado';
    header('Location: users.php');
    exit();
}
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Administración</div>
                    <h2 class="page-title">Editar Usuario</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="users.php" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/users/update-user.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información del Usuario</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Nombre de Usuario</label>
                                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nueva Contraseña</label>
                                            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                                            <small class="text-muted">Solo completar si desea cambiar la contraseña</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Nombre</label>
                                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Apellido</label>
                                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
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
                                    <label class="form-label required">Rol</label>
                                    <select name="rol" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="farmaceutico" <?php echo $usuario['rol'] === 'farmaceutico' ? 'selected' : ''; ?>>Farmacéutico</option>
                                        <option value="vendedor" <?php echo $usuario['rol'] === 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                                        <option value="cajero" <?php echo $usuario['rol'] === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="activo" <?php echo $usuario['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?php echo $usuario['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Información Adicional</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Fecha de Registro</label>
                                    <div><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></div>
                                </div>
                                <?php if ($usuario['ultimo_acceso']): ?>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Último Acceso</label>
                                    <div><?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-device-floppy icon"></i>
                                    Guardar Cambios
                                </button>
                                <a href="users.php" class="btn btn-secondary w-100 mt-2">
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
