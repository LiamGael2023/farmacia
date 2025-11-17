<?php
$pageTitle = 'Compras - Sistema de Farmacia';
$currentPage = 'purchases';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

$compras = $db->query("
    SELECT c.*, p.nombre as proveedor_nombre, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM compras c
    INNER JOIN proveedores p ON c.proveedor_id = p.id
    INNER JOIN usuarios u ON c.usuario_id = u.id
    ORDER BY c.fecha_compra DESC
")->fetchAll();
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Gestión</div>
                    <h2 class="page-title">Compras</h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-purchase">
                        <i class="ti ti-plus icon"></i>
                        Nueva Compra
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Historial de Compras</h3>
                </div>
                <div class="card-body">
                    <table id="tablePurchases" class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>N° Compra</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Fecha Entrega</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $comp): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($comp['numero_compra']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($comp['fecha_compra'])); ?></td>
                                <td><?php echo htmlspecialchars($comp['proveedor_nombre']); ?></td>
                                <td><?php echo $comp['fecha_entrega'] ? date('d/m/Y', strtotime($comp['fecha_entrega'])) : 'Pendiente'; ?></td>
                                <td><strong>S/ <?php echo number_format($comp['total'], 2); ?></strong></td>
                                <td>
                                    <?php
                                    $badge = 'bg-secondary';
                                    switch($comp['estado']) {
                                        case 'pendiente': $badge = 'bg-warning'; break;
                                        case 'recibida': $badge = 'bg-success'; break;
                                        case 'parcial': $badge = 'bg-info'; break;
                                        case 'cancelada': $badge = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($comp['estado']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($comp['usuario_nombre'] . ' ' . $comp['usuario_apellido']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="purchase-view.php?id=<?php echo $comp['id']; ?>" class="btn btn-sm btn-icon btn-info" title="Ver">
                                            <i class="ti ti-eye icon"></i>
                                        </a>
                                        <a href="purchase-edit.php?id=<?php echo $comp['id']; ?>" class="btn btn-sm btn-icon btn-success" title="Editar">
                                            <i class="ti ti-edit icon"></i>
                                        </a>
                                        <button onclick="window.print()" class="btn btn-sm btn-icon btn-primary" title="Imprimir">
                                            <i class="ti ti-printer icon"></i>
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

<?php $customJS = '<script>$("#tablePurchases").DataTable();</script>'; ?>
