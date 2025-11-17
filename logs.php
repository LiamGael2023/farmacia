<?php
$pageTitle = 'Logs del Sistema - Sistema de Farmacia';
$currentPage = 'logs';
require_once 'includes/header.php';
require_once 'config/database.php';

checkAuth();
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$db = getDB();

// Obtener logs
$logs = $db->query("
    SELECT l.*, u.nombre, u.apellido, u.username
    FROM logs l
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    ORDER BY l.fecha DESC
    LIMIT 1000
")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Administración</div>
                    <h2 class="page-title">Logs de Auditoría</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Registro de Actividades (Últimas 1000)</h3>
                </div>
                <div class="card-body">
                    <table id="tableLogs" class="table table-vcenter table-sm">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Tabla</th>
                                <th>ID Registro</th>
                                <th>Detalles</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><small><?php echo date('d/m/Y H:i:s', strtotime($log['fecha'])); ?></small></td>
                                <td>
                                    <?php if ($log['username']): ?>
                                        <strong><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($log['nombre'] . ' ' . $log['apellido']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Sistema</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge = 'bg-secondary';
                                    if (strpos($log['accion'], 'create') !== false) $badge = 'bg-success';
                                    if (strpos($log['accion'], 'update') !== false) $badge = 'bg-blue';
                                    if (strpos($log['accion'], 'delete') !== false) $badge = 'bg-danger';
                                    if (strpos($log['accion'], 'login') !== false) $badge = 'bg-info';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($log['accion']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($log['tabla'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($log['registro_id'] ?? '-'); ?></td>
                                <td><small><?php echo htmlspecialchars(substr($log['detalles'] ?? '-', 0, 50)); ?></small></td>
                                <td><small><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></small></td>
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

<?php $customJS = '<script>$("#tableLogs").DataTable({order: [[0, "desc"]], pageLength: 50});</script>'; ?>
