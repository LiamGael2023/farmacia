<?php
require_once 'config/database.php';

$db = getDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ID de venta no especificado";
    exit();
}

// Obtener datos de venta
$stmt = $db->prepare("
    SELECT v.*,
           c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.dni,
           u.nombre as vendedor_nombre, u.apellido as vendedor_apellido
    FROM ventas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    INNER JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = ?
");
$stmt->execute([$id]);
$venta = $stmt->fetch();

if (!$venta) {
    echo "Venta no encontrada";
    exit();
}

// Obtener detalles
$stmt = $db->prepare("
    SELECT vd.*, m.codigo, m.nombre, m.presentacion
    FROM ventas_detalle vd
    INNER JOIN medicamentos m ON vd.medicamento_id = m.id
    WHERE vd.venta_id = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Obtener configuración
$config = [];
$stmt = $db->query("SELECT clave, valor FROM configuracion");
while ($row = $stmt->fetch()) {
    $config[$row['clave']] = $row['valor'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta - <?php echo $venta['numero_venta']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
        }
        .info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .info p {
            margin: 3px 0;
        }
        .items {
            margin-bottom: 10px;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
        }
        .items th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
        }
        .items td {
            padding: 3px 0;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 2px 0;
        }
        .totals .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 15px;
            font-size: 11px;
        }
        @media print {
            body {
                width: 80mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($config['nombre_farmacia'] ?? 'FARMACIA'); ?></h1>
        <?php if (isset($config['ruc_farmacia'])): ?>
        <p>RUC: <?php echo htmlspecialchars($config['ruc_farmacia']); ?></p>
        <?php endif; ?>
        <?php if (isset($config['direccion_farmacia'])): ?>
        <p><?php echo htmlspecialchars($config['direccion_farmacia']); ?></p>
        <?php endif; ?>
        <?php if (isset($config['telefono_farmacia'])): ?>
        <p>Tel: <?php echo htmlspecialchars($config['telefono_farmacia']); ?></p>
        <?php endif; ?>
    </div>

    <div class="info">
        <p><strong>TICKET DE VENTA</strong></p>
        <p>N°: <?php echo htmlspecialchars($venta['numero_venta']); ?></p>
        <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></p>
        <p>Cliente: <?php echo $venta['cliente_nombre'] ? htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']) : 'CLIENTE GENERAL'; ?></p>
        <?php if ($venta['dni']): ?>
        <p>DNI: <?php echo htmlspecialchars($venta['dni']); ?></p>
        <?php endif; ?>
        <p>Vendedor: <?php echo htmlspecialchars($venta['vendedor_nombre'] . ' ' . $venta['vendedor_apellido']); ?></p>
        <p>Pago: <?php echo ucfirst($venta['metodo_pago']); ?></p>
    </div>

    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Producto</th>
                    <th style="text-align: right;">P.Unit</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $det): ?>
                <tr>
                    <td><?php echo $det['cantidad']; ?></td>
                    <td><?php echo htmlspecialchars(substr($det['nombre'], 0, 20)); ?></td>
                    <td style="text-align: right;"><?php echo number_format($det['precio_unitario'], 2); ?></td>
                    <td style="text-align: right;"><?php echo number_format($det['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td style="text-align: right;">S/ <?php echo number_format($venta['subtotal'], 2); ?></td>
            </tr>
            <?php if ($venta['descuento'] > 0): ?>
            <tr>
                <td>Descuento:</td>
                <td style="text-align: right;">-S/ <?php echo number_format($venta['descuento'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>IGV (18%):</td>
                <td style="text-align: right;">S/ <?php echo number_format($venta['impuesto'], 2); ?></td>
            </tr>
            <tr class="grand-total">
                <td>TOTAL:</td>
                <td style="text-align: right;">S/ <?php echo number_format($venta['total'], 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p><?php echo date('d/m/Y H:i:s'); ?></p>
        <?php if (isset($config['email_farmacia'])): ?>
        <p><?php echo htmlspecialchars($config['email_farmacia']); ?></p>
        <?php endif; ?>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
            Imprimir Ticket
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional, comentar si no se desea)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
