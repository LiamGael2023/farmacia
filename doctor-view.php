<?php
$pageTitle = 'Detalle de Médico - Sistema de Farmacia';
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

// Obtener prescripciones del médico
$stmt = $db->prepare("
    SELECT p.*, c.nombre as cliente_nombre, c.apellido as cliente_apellido,
           m.nombre as medicamento_nombre
    FROM prescripciones p
    INNER JOIN clientes c ON p.cliente_id = c.id
    LEFT JOIN medicamentos m ON p.medicamento_id = m.id
    WHERE p.medico_id = ?
    ORDER BY p.fecha_emision DESC
    LIMIT 20
");
$stmt->execute([$id]);
$prescripciones = $stmt->fetchAll();

// Estadísticas
$stmt = $db->prepare("
    SELECT
        COUNT(*) as total_prescripciones,
        COUNT(CASE WHEN estado = 'vigente' THEN 1 END) as vigentes,
        COUNT(CASE WHEN estado = 'dispensada' THEN 1 END) as dispensadas,
        COUNT(CASE WHEN estado = 'vencida' THEN 1 END) as vencidas
    FROM prescripciones
    WHERE medico_id = ?
");
$stmt->execute([$id]);
$stats = $stmt->fetch();

// Medicamentos más recetados
$stmt = $db->prepare("
    SELECT m.nombre, COUNT(*) as cantidad
    FROM prescripciones p
    INNER JOIN medicamentos m ON p.medicamento_id = m.id
    WHERE p.medico_id = ?
    GROUP BY m.id, m.nombre
    ORDER BY cantidad DESC
    LIMIT 5
");
$stmt->execute([$id]);
$top_meds = $stmt->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Médicos</div>
                    <h2 class="page-title"><?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?></h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="doctor-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="ti ti-edit icon"></i>
                            Editar
                        </a>
                        <a href="doctors.php" class="btn btn-secondary">
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
                            <h3 class="card-title">Información del Médico</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre Completo</label>
                                <div><strong><?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?></strong></div>
                            </div>
                            <?php if ($medico['numero_colegiatura']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">N° Colegiatura</label>
                                <div><?php echo htmlspecialchars($medico['numero_colegiatura']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($medico['especialidad']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Especialidad</label>
                                <div><?php echo htmlspecialchars($medico['especialidad']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($medico['telefono']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Teléfono</label>
                                <div><?php echo htmlspecialchars($medico['telefono']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($medico['email']): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <div><?php echo htmlspecialchars($medico['email']); ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    <span class="badge bg-<?php echo $medico['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($medico['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Fecha de Registro</label>
                                <div><?php echo date('d/m/Y', strtotime($medico['fecha_creacion'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if (count($top_meds) > 0): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Medicamentos Más Recetados</h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php foreach ($top_meds as $med): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?php echo htmlspecialchars($med['nombre']); ?></span>
                                    <span class="badge bg-primary"><?php echo $med['cantidad']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">Estadísticas de Prescripciones</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Total Prescripciones</label>
                                        <div class="h3"><?php echo $stats['total_prescripciones']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Vigentes</label>
                                        <div class="h3 text-success"><?php echo $stats['vigentes']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Dispensadas</label>
                                        <div class="h3 text-info"><?php echo $stats['dispensadas']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Vencidas</label>
                                        <div class="h3 text-danger"><?php echo $stats['vencidas']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Historial de Prescripciones</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Paciente</th>
                                        <th>Medicamento</th>
                                        <th>Diagnóstico</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($prescripciones) > 0): ?>
                                        <?php foreach ($prescripciones as $presc): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($presc['fecha_emision'])); ?></td>
                                            <td><strong><?php echo htmlspecialchars($presc['cliente_nombre'] . ' ' . $presc['cliente_apellido']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($presc['medicamento_nombre'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(substr($presc['diagnostico'] ?? '-', 0, 30)); ?><?php echo strlen($presc['diagnostico'] ?? '') > 30 ? '...' : ''; ?></td>
                                            <td>
                                                <?php
                                                $badge = 'bg-secondary';
                                                switch($presc['estado']) {
                                                    case 'vigente': $badge = 'bg-success'; break;
                                                    case 'dispensada': $badge = 'bg-info'; break;
                                                    case 'vencida': $badge = 'bg-danger'; break;
                                                    case 'cancelada': $badge = 'bg-warning'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($presc['estado']); ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay prescripciones registradas</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>
