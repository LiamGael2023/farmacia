<?php
$pageTitle = 'Punto de Venta - Sistema de Farmacia';
$currentPage = 'pos';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = getDB();

// Obtener medicamentos activos
$medicamentos = $db->query("
    SELECT m.*, c.nombre as categoria_nombre
    FROM medicamentos m
    LEFT JOIN categorias c ON m.categoria_id = c.id
    WHERE m.estado = 'activo' AND m.stock_actual > 0
    ORDER BY m.nombre ASC
")->fetchAll();

// Obtener clientes
$clientes = $db->query("
    SELECT id, dni, nombre, apellido
    FROM clientes
    WHERE estado = 'activo'
    ORDER BY nombre ASC
")->fetchAll();

// Generar número de venta
$stmt = $db->query("SELECT numero_venta FROM ventas ORDER BY id DESC LIMIT 1");
$lastVenta = $stmt->fetch();
if ($lastVenta) {
    $lastNumber = (int)substr($lastVenta['numero_venta'], 2);
    $newNumber = str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT);
} else {
    $newNumber = '00000001';
}
$numeroVenta = 'V-' . $newNumber;
?>

<?php include 'includes/navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Ventas</div>
                    <h2 class="page-title">Punto de Venta</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form method="POST" action="modules/sales/process-sale.php" id="formSale">
                <div class="row row-deck row-cards">
                    <!-- Panel Izquierdo - Productos -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Productos</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Buscar Medicamento</label>
                                    <div class="input-group">
                                        <input type="text" id="searchMedicine" class="form-control" placeholder="Buscar por nombre o código..." autocomplete="off">
                                        <button type="button" class="btn btn-icon" onclick="$('#searchMedicine').val(''); $('#searchMedicine').focus();">
                                            <i class="ti ti-x icon"></i>
                                        </button>
                                    </div>
                                    <div id="medicineResults" class="list-group mt-2" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-vcenter" id="saleItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-end">Precio Unit.</th>
                                                <th class="text-end">Subtotal</th>
                                                <th class="w-1"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="saleItems">
                                            <tr id="emptyRow">
                                                <td colspan="5" class="text-center text-muted">
                                                    <div class="empty">
                                                        <div class="empty-icon">
                                                            <i class="ti ti-shopping-cart icon"></i>
                                                        </div>
                                                        <p class="empty-title">No hay productos en la venta</p>
                                                        <p class="empty-subtitle text-muted">
                                                            Busque y agregue productos para comenzar
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Derecho - Resumen -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header">
                                <h3 class="card-title">Resumen de Venta</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">N° Venta</label>
                                    <input type="text" name="numero_venta" class="form-control" value="<?php echo $numeroVenta; ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Cliente</label>
                                    <select name="cliente_id" class="form-select" id="clienteSelect">
                                        <option value="">Cliente General</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['id']; ?>">
                                                <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                                                <?php if ($cliente['dni']): ?>
                                                    - <?php echo htmlspecialchars($cliente['dni']); ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-hint">
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-new-client">+ Nuevo Cliente</a>
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Método de Pago</label>
                                    <select name="metodo_pago" class="form-select" required>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="mixto">Mixto</option>
                                    </select>
                                </div>

                                <hr>

                                <div class="row mb-2">
                                    <div class="col">Subtotal:</div>
                                    <div class="col-auto">
                                        <strong>S/ <span id="subtotalDisplay">0.00</span></strong>
                                        <input type="hidden" name="subtotal" id="subtotal" value="0">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col">
                                        <label for="descuento">Descuento:</label>
                                    </div>
                                    <div class="col-auto">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">S/</span>
                                            <input type="number" name="descuento" id="descuento" class="form-control form-control-sm text-end" value="0" step="0.01" min="0" style="width: 80px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col">IGV (18%):</div>
                                    <div class="col-auto">
                                        <strong>S/ <span id="impuestoDisplay">0.00</span></strong>
                                        <input type="hidden" name="impuesto" id="impuesto" value="0">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col"><h3>Total:</h3></div>
                                    <div class="col-auto">
                                        <h2 class="text-primary">S/ <span id="totalDisplay">0.00</span></h2>
                                        <input type="hidden" name="total" id="total" value="0">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="resetSale()">
                                        <i class="ti ti-x icon"></i>
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-success flex-fill" id="btnProcessSale">
                                        <i class="ti ti-check icon"></i>
                                        Procesar Venta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Nuevo Cliente Rápido -->
<div class="modal modal-blur fade" id="modal-new-client" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formQuickClient">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Cliente Rápido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$customJS = <<<'JS'
<script>
let saleItems = [];
let medicamentos = <?php echo json_encode($medicamentos); ?>;

$(document).ready(function() {
    // Búsqueda de medicamentos
    $('#searchMedicine').on('keyup', function() {
        let search = $(this).val().toLowerCase();
        if (search.length < 2) {
            $('#medicineResults').hide().html('');
            return;
        }

        let results = medicamentos.filter(m =>
            m.nombre.toLowerCase().includes(search) ||
            m.codigo.toLowerCase().includes(search) ||
            (m.nombre_generico && m.nombre_generico.toLowerCase().includes(search))
        ).slice(0, 10);

        if (results.length > 0) {
            let html = '';
            results.forEach(med => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action" onclick="addMedicine(${med.id}); return false;">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                <strong>${med.nombre}</strong>
                                <small class="d-block text-muted">${med.codigo} - ${med.presentacion || ''}</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">S/ ${parseFloat(med.precio_venta).toFixed(2)}</strong>
                                <small class="d-block text-muted">Stock: ${med.stock_actual}</small>
                            </div>
                        </div>
                    </a>
                `;
            });
            $('#medicineResults').html(html).show();
        } else {
            $('#medicineResults').html('<div class="list-group-item text-muted">No se encontraron resultados</div>').show();
        }
    });

    // Calcular totales al cambiar descuento
    $('#descuento').on('input', calculateTotals);

    // Guardar cliente rápido
    $('#formQuickClient').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.post('modules/customers/save-quick-client.php', formData, function(response) {
            if (response.success) {
                let option = new Option(response.cliente.nombre + ' ' + response.cliente.apellido, response.cliente.id, true, true);
                $('#clienteSelect').append(option).trigger('change');
                $('#modal-new-client').modal('hide');
                $('#formQuickClient')[0].reset();
                showAlert('success', 'Cliente agregado exitosamente');
            } else {
                showAlert('error', response.message || 'Error al guardar cliente');
            }
        }, 'json').fail(function() {
            showAlert('error', 'Error al procesar la solicitud');
        });
    });

    // Validar formulario de venta
    $('#formSale').on('submit', function(e) {
        if (saleItems.length === 0) {
            e.preventDefault();
            showAlert('warning', 'Debe agregar al menos un producto a la venta');
            return false;
        }
    });
});

function addMedicine(medicineId) {
    let medicine = medicamentos.find(m => m.id == medicineId);
    if (!medicine) return;

    // Verificar si ya está en la lista
    let existingItem = saleItems.find(item => item.id == medicineId);
    if (existingItem) {
        if (existingItem.cantidad < medicine.stock_actual) {
            existingItem.cantidad++;
        } else {
            showAlert('warning', 'Stock insuficiente');
            return;
        }
    } else {
        saleItems.push({
            id: medicine.id,
            codigo: medicine.codigo,
            nombre: medicine.nombre,
            presentacion: medicine.presentacion,
            precio: parseFloat(medicine.precio_venta),
            cantidad: 1,
            stock: medicine.stock_actual
        });
    }

    renderSaleItems();
    $('#searchMedicine').val('');
    $('#medicineResults').hide();
}

function removeMedicine(medicineId) {
    saleItems = saleItems.filter(item => item.id != medicineId);
    renderSaleItems();
}

function updateQuantity(medicineId, delta) {
    let item = saleItems.find(item => item.id == medicineId);
    if (!item) return;

    let newQuantity = item.cantidad + delta;
    if (newQuantity <= 0) {
        removeMedicine(medicineId);
        return;
    }

    if (newQuantity > item.stock) {
        showAlert('warning', 'Stock insuficiente');
        return;
    }

    item.cantidad = newQuantity;
    renderSaleItems();
}

function renderSaleItems() {
    let tbody = $('#saleItems');
    tbody.html('');

    if (saleItems.length === 0) {
        tbody.html($('#emptyRow').prop('outerHTML'));
        calculateTotals();
        return;
    }

    saleItems.forEach(item => {
        let subtotal = item.precio * item.cantidad;
        let row = `
            <tr>
                <td>
                    <input type="hidden" name="items[${item.id}][medicamento_id]" value="${item.id}">
                    <strong>${item.nombre}</strong><br>
                    <small class="text-muted">${item.codigo} - ${item.presentacion || ''}</small>
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm" style="width: 120px; margin: 0 auto;">
                        <button type="button" class="btn btn-sm" onclick="updateQuantity(${item.id}, -1)">
                            <i class="ti ti-minus"></i>
                        </button>
                        <input type="number" name="items[${item.id}][cantidad]" class="form-control form-control-sm text-center"
                               value="${item.cantidad}" min="1" max="${item.stock}" readonly>
                        <button type="button" class="btn btn-sm" onclick="updateQuantity(${item.id}, 1)">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    <small class="text-muted">Stock: ${item.stock}</small>
                </td>
                <td class="text-end">
                    <input type="hidden" name="items[${item.id}][precio]" value="${item.precio}">
                    S/ ${item.precio.toFixed(2)}
                </td>
                <td class="text-end">
                    <strong>S/ ${subtotal.toFixed(2)}</strong>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-icon btn-danger" onclick="removeMedicine(${item.id})">
                        <i class="ti ti-trash icon"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    calculateTotals();
}

function calculateTotals() {
    let subtotal = saleItems.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    let descuento = parseFloat($('#descuento').val()) || 0;
    let baseImponible = subtotal - descuento;
    let impuesto = baseImponible * 0.18;
    let total = baseImponible + impuesto;

    $('#subtotal').val(subtotal.toFixed(2));
    $('#subtotalDisplay').text(subtotal.toFixed(2));
    $('#impuesto').val(impuesto.toFixed(2));
    $('#impuestoDisplay').text(impuesto.toFixed(2));
    $('#total').val(total.toFixed(2));
    $('#totalDisplay').text(total.toFixed(2));

    $('#btnProcessSale').prop('disabled', saleItems.length === 0);
}

function resetSale() {
    if (saleItems.length > 0) {
        Swal.fire({
            title: '¿Cancelar venta?',
            text: 'Se perderán todos los productos agregados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63939',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                saleItems = [];
                renderSaleItems();
                $('#clienteSelect').val('').trigger('change');
                $('select[name="metodo_pago"]').val('efectivo');
                $('#descuento').val(0);
                $('textarea[name="observaciones"]').val('');
            }
        });
    }
}
</script>
JS;
?>
