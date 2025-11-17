<?php
$pageTitle = 'Detalle de Prescripción - Sistema de Farmacia';
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
$stmt = $db->prepare("
    SELECT p.*,
           c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni,
           m.nombre as medico_nombre, m.apellido as medico_apellido, m.numero_colegiatura, m.especialidad
    FROM prescripciones p
    INNER JOIN clientes c ON p.cliente_id = c.id
    LEFT JOIN medicos m ON p.medico_id = m.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$prescripcion = $stmt->fetch();

if (!$prescripcion) {
    $_SESSION['error'] = 'Prescripción no encontrada';
    header('Location: prescriptions.php');
    exit();
}

// Obtener detalles de medicamentos
$stmt = $db->prepare("
    SELECT pd.*, m.codigo, m.nombre, m.presentacion, m.precio_venta
    FROM prescripciones_detalle pd
    INNER JOIN medicamentos m ON pd.medicamento_id = m.id
    WHERE pd.prescripcion_id = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Obtener medicamentos disponibles para agregar
$medicamentos = $db->query("SELECT id, codigo, nombre, presentacion, stock_actual FROM medicamentos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Prescripciones</div>
                    <h2 class="page-title">Receta N° <?php echo htmlspecialchars($prescripcion['numero_receta']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <?php if ($prescripcion['estado'] === 'pendiente' || $prescripcion['estado'] === 'parcial'): ?>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-add-medicine">
                            <i class="ti ti-pill icon"></i>
                            Agregar Medicamento
                        </button>
                        <?php endif; ?>
                        <a href="prescription-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="ti ti-edit icon"></i>
                            Editar
                        </a>
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="ti ti-printer icon"></i>
                            Imprimir
                        </button>
                        <a href="prescriptions.php" class="btn btn-secondary">
                            <i class="ti ti-arrow-left icon"></i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información de la Receta</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">N° Receta</label>
                                <div><strong><?php echo htmlspecialchars($prescripcion['numero_receta']); ?></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Emisión</label>
                                <div><?php echo date('d/m/Y', strtotime($prescripcion['fecha_emision'])); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <?php
                                    $badge = 'bg-secondary';
                                    switch($prescripcion['estado']) {
                                        case 'pendiente': $badge = 'bg-warning'; break;
                                        case 'dispensada': $badge = 'bg-success'; break;
                                        case 'parcial': $badge = 'bg-info'; break;
                                        case 'cancelada': $badge = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($prescripcion['estado']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Paciente</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre</label>
                                <div><strong><?php echo htmlspecialchars($prescripcion['cliente_nombre'] . ' ' . $prescripcion['cliente_apellido']); ?></strong></div>
                            </div>
                            <?php if ($prescripcion['dni']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">DNI</label>
                                <div><?php echo htmlspecialchars($prescripcion['dni']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($prescripcion['medico_nombre']): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Médico</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre</label>
                                <div><strong><?php echo htmlspecialchars($prescripcion['medico_nombre'] . ' ' . $prescripcion['medico_apellido']); ?></strong></div>
                            </div>
                            <?php if ($prescripcion['numero_colegiatura']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">N° Colegiatura</label>
                                <div><?php echo htmlspecialchars($prescripcion['numero_colegiatura']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($prescripcion['especialidad']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Especialidad</label>
                                <div><?php echo htmlspecialchars($prescripcion['especialidad']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-8">
                    <?php if ($prescripcion['diagnostico']): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Diagnóstico</h3>
                        </div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($prescripcion['diagnostico'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Medicamentos Recetados</h3>
                        </div>
                        <div class="card-body">
                            <?php if (count($detalles) > 0): ?>
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Cantidad</th>
                                        <th>Dosis</th>
                                        <th>Frecuencia</th>
                                        <th>Duración</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $det): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($det['nombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($det['codigo'] . ' - ' . $det['presentacion']); ?></small>
                                            <?php if ($det['indicaciones']): ?>
                                            <br><small class="text-info"><?php echo htmlspecialchars($det['indicaciones']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-blue"><?php echo $det['cantidad']; ?></span></td>
                                        <td><?php echo htmlspecialchars($det['dosis'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($det['frecuencia'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($det['duracion'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <p>No hay medicamentos agregados a esta prescripción</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-medicine">
                                    <i class="ti ti-pill icon"></i>
                                    Agregar Medicamento
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($prescripcion['observaciones']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Observaciones</h3>
                        </div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($prescripcion['observaciones'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Agregar Medicamento -->
<div class="modal modal-blur fade" id="modal-add-medicine" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="modules/prescriptions/add-medicine.php">
                <input type="hidden" name="prescripcion_id" value="<?php echo $id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Medicamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label required">Medicamento</label>
                                <select name="medicamento_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($medicamentos as $med): ?>
                                        <option value="<?php echo $med['id']; ?>">
                                            <?php echo htmlspecialchars($med['codigo'] . ' - ' . $med['nombre'] . ' - ' . $med['presentacion'] . ' (Stock: ' . $med['stock_actual'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label required">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Dosis</label>
                                <input type="text" name="dosis" class="form-control" placeholder="Ej: 500mg">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Frecuencia</label>
                                <input type="text" name="frecuencia" class="form-control" placeholder="Ej: Cada 8 horas">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Duración</label>
                                <input type="text" name="duracion" class="form-control" placeholder="Ej: 7 días">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label">Indicaciones</label>
                                <textarea name="indicaciones" class="form-control" rows="2" placeholder="Instrucciones especiales"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
