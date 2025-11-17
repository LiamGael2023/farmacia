<?php
$pageTitle = 'Editar Médico - Sistema de Farmacia';
$currentPage = 'doctors';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: doctors.php');
    exit();
}

// Obtener médico
$stmt = $db->prepare("SELECT * FROM medicos WHERE id = ?");
$stmt->execute([$id]);
$medico = $stmt->fetch();

if (!$medico) {
    $_SESSION['error'] = 'Médico no encontrado';
    header('Location: doctors.php');
    exit();
}
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Médicos</div>
                    <h2 class="page-title">Editar Médico</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="doctor-view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/doctors/update-doctor.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información del Médico</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Nombre</label>
                                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($medico['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Apellido</label>
                                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($medico['apellido']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">N° Colegiatura</label>
                                            <input type="text" name="numero_colegiatura" class="form-control" value="<?php echo htmlspecialchars($medico['numero_colegiatura'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Especialidad</label>
                                            <input type="text" name="especialidad" class="form-control" value="<?php echo htmlspecialchars($medico['especialidad'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($medico['telefono'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($medico['email'] ?? ''); ?>">
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
                                        <option value="activo" <?php echo $medico['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?php echo $medico['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
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
                                <a href="doctor-view.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 mt-2">
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
