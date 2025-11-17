<?php
$pageTitle = 'Editar Proveedor - Sistema de Farmacia';
$currentPage = 'suppliers';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: suppliers.php');
    exit();
}

// Obtener proveedor
$stmt = $db->prepare("SELECT * FROM proveedores WHERE id = ?");
$stmt->execute([$id]);
$proveedor = $stmt->fetch();

if (!$proveedor) {
    $_SESSION['error'] = 'Proveedor no encontrado';
    header('Location: suppliers.php');
    exit();
}
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Proveedores</div>
                    <h2 class="page-title">Editar Proveedor</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="supplier-view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                        <i class="ti ti-arrow-left icon"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/suppliers/update-supplier.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información del Proveedor</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label required">Nombre / Razón Social</label>
                                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($proveedor['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">RUC</label>
                                            <input type="text" name="ruc" class="form-control" value="<?php echo htmlspecialchars($proveedor['ruc'] ?? ''); ?>" maxlength="11">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Dirección</label>
                                            <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($proveedor['direccion'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($proveedor['telefono'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($proveedor['email'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Persona de Contacto</label>
                                            <input type="text" name="contacto_nombre" class="form-control" value="<?php echo htmlspecialchars($proveedor['contacto_nombre'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono de Contacto</label>
                                            <input type="text" name="contacto_telefono" class="form-control" value="<?php echo htmlspecialchars($proveedor['contacto_telefono'] ?? ''); ?>">
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
                                        <option value="activo" <?php echo $proveedor['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="inactivo" <?php echo $proveedor['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
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
                                <a href="supplier-view.php?id=<?php echo $id; ?>" class="btn btn-secondary w-100 mt-2">
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
