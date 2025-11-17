<?php
$pageTitle = 'Configuración - Sistema de Farmacia';
$currentPage = 'config';
require_once 'includes/header.php';
require_once 'config/database.php';

checkAuth();
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

$db = getDB();

// Obtener configuración actual
$config = [];
$stmt = $db->query("SELECT * FROM configuracion");
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST as $clave => $valor) {
            if ($clave !== 'submit') {
                $stmt = $db->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
                $stmt->execute([$valor, $clave]);
            }
        }
        $_SESSION['success'] = 'Configuración actualizada exitosamente';
        header('Location: config.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al actualizar configuración';
    }
}
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Administración</div>
                    <h2 class="page-title">Configuración del Sistema</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <a class="btn-close" data-bs-dismiss="alert"></a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row row-cards">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información de la Farmacia</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la Farmacia</label>
                                    <input type="text" name="nombre_farmacia" class="form-control" value="<?php echo htmlspecialchars($config['nombre_farmacia'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">RUC</label>
                                    <input type="text" name="ruc_farmacia" class="form-control" value="<?php echo htmlspecialchars($config['ruc_farmacia'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dirección</label>
                                    <input type="text" name="direccion_farmacia" class="form-control" value="<?php echo htmlspecialchars($config['direccion_farmacia'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" name="telefono_farmacia" class="form-control" value="<?php echo htmlspecialchars($config['telefono_farmacia'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email_farmacia" class="form-control" value="<?php echo htmlspecialchars($config['email_farmacia'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Configuración General</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Impuesto (IGV %)</label>
                                    <input type="number" name="impuesto_venta" class="form-control" value="<?php echo htmlspecialchars($config['impuesto_venta'] ?? '18'); ?>" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Días de Alerta de Vencimiento</label>
                                    <input type="number" name="dias_alerta_vencimiento" class="form-control" value="<?php echo htmlspecialchars($config['dias_alerta_vencimiento'] ?? '30'); ?>">
                                    <small class="form-hint">Días antes del vencimiento para mostrar alerta</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Moneda</label>
                                    <input type="text" name="moneda" class="form-control" value="<?php echo htmlspecialchars($config['moneda'] ?? 'S/'); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Acciones</h3>
                            </div>
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy icon"></i>
                                    Guardar Configuración
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
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
