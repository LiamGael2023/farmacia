<?php
$pageTitle = 'Editar Prescripción - Sistema de Farmacia';
$currentPage = 'prescriptions';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: prescriptions.php');
    exit();
}

// Obtener prescripción
$stmt = $db->prepare("SELECT * FROM prescripciones WHERE id = ?");
$stmt->execute([$id]);
$prescripcion = $stmt->fetch();

if (!$prescripcion) {
    $_SESSION['error'] = 'Prescripción no encontrada';
    header('Location: prescriptions.php');
    exit();
}

// Obtener datos para selects
$clientes = $db->query("SELECT id, nombre, apellido, dni FROM clientes WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$medicos = $db->query("SELECT id, nombre, apellido, especialidad FROM medicos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Prescripciones</div>
                    <h2 class="page-title">Editar Prescripción</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="prescription-view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/prescriptions/update-prescription.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información de la Prescripción</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">N° Receta</label>
                                            <input type="text" name="numero_receta" class="form-control" value="<?php echo htmlspecialchars($prescripcion['numero_receta']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Fecha Emisión</label>
                                            <input type="date" name="fecha_emision" class="form-control" value="<?php echo $prescripcion['fecha_emision']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">Paciente</label>
                                            <select name="cliente_id" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($clientes as $cli): ?>
                                                    <option value="<?php echo $cli['id']; ?>" <?php echo $cli['id'] == $prescripcion['cliente_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cli['nombre'] . ' ' . $cli['apellido'] . ($cli['dni'] ? ' - ' . $cli['dni'] : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Médico</label>
                                            <select name="medico_id" class="form-select">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($medicos as $med): ?>
                                                    <option value="<?php echo $med['id']; ?>" <?php echo $med['id'] == $prescripcion['medico_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($med['nombre'] . ' ' . $med['apellido'] . ($med['especialidad'] ? ' - ' . $med['especialidad'] : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Diagnóstico</label>
                                            <textarea name="diagnostico" class="form-control" rows="4"><?php echo htmlspecialchars($prescripcion['diagnostico'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Observaciones</label>
                                            <textarea name="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($prescripcion['observaciones'] ?? ''); ?></textarea>
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
                                        <option value="pendiente" <?php echo $prescripcion['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="dispensada" <?php echo $prescripcion['estado'] === 'dispensada' ? 'selected' : ''; ?>>Dispensada</option>
                                        <option value="parcial" <?php echo $prescripcion['estado'] === 'parcial' ? 'selected' : ''; ?>>Parcial</option>
                                        <option value="cancelada" <?php echo $prescripcion['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
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
                                <a href="prescription-view.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 mt-2">
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
