<?php
include 'config.php';

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "<p>Pedido no encontrado.</p>";
    exit;
}

$stmt = $conn->prepare("
    SELECT p.nombre, d.cantidad, d.precio_unitario, (d.cantidad * d.precio_unitario) AS subtotal
    FROM pedido_detalle d
    JOIN productos p ON d.producto_id = p.id
    WHERE d.pedido_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$detalles = $stmt->get_result();
$stmt->close();

$total = 0;
function formato_clp($valor) {
    return '$ ' . number_format($valor, 0, '', '.');
}

function formato_fecha($fecha_str) {
    $fecha = new DateTime($fecha_str);
    return $fecha->format('d/m/Y H:i');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Ticket Pedido #<?= $id ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: monospace;
            max-width: 80mm;
            margin: auto;
            padding: 10px;
            color: #000;
            background: #fff;
            font-weight: bold;
        }
        .info-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 24px;
        }
        .info-header p {
            margin: 6px 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 18px;
            font-weight: bold;
            border-top: 3px solid #000; /* línea gruesa arriba tabla */
        }
        thead tr {
            border-bottom: 3px solid #000; /* línea gruesa debajo de la cabecera */
        }
        tbody tr:not(:last-child) {
            border-bottom: 3px solid #000; /* línea gruesa entre filas */
        }
        th, td {
            padding: 3px 5px;
            text-align: center;
        }
        /* Evitar salto de línea en precios */
        th.price, td.price {
            min-width: 70px;
            text-align: right;
            white-space: nowrap;
        }
        .linea-total {
            border-top: 3px solid #000; /* línea gruesa */
            margin: 15px 0 10px 0;
            width: 105%;
        }
        .price {
            text-align: right;
        }
        .total {
            position: relative;
            right: -10px;
            text-align: right;
            font-size: 18px;
            margin: 0;
            padding: 10;
        }
        /* Contenedor de botones centrado y fijo abajo */
        #botones {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px; /* espacio entre botones */
            z-index: 1000;
        }
        /* Botones grandes */
        #btnPrint, #btnVolver {
            width: 200px;
            height: 60px;
            font-size: 24px;
            border-radius: 10px;
            cursor: pointer;
            user-select: none;
            color: white;
            border: none;
            font-weight: bold;
        }
        #btnPrint {
            background-color: #007bff;
        }
        #btnPrint:hover {
            background-color: #0056b3;
        }
        #btnVolver {
            background-color: #28a745;
        }
        #btnVolver:hover {
            background-color: #1e7e34;
        }
        @media print {
            #botones {
                display: none;
            }
            body {
                max-width: 80mm;
                margin: 0;
                padding: 0;
            }
        }

        /* Div para simular avance de papel (feed) al imprimir */
        .feed-space {
            height: 40mm;
            background: transparent;
            display: block;
            position: relative;
        }
        .feed-space::after {
            content: "\00a0"; /* espacio no rompible */
            display: block;
            height: 40mm; /* feed adicional */
            visibility: hidden;
        }
    </style>
</head>
<body>

    <div id="botones">
        <button id="btnVolver" onclick="window.location.href='index.php'">Volver</button>
        <button id="btnPrint" onclick="window.print()">Imprimir Ticket</button>
    </div>

    <div class="info-header">
        <p>Mesa: <?= htmlspecialchars($pedido['mesa']) ?></p>
        <p>Pedido #<?= $id ?></p>
        <p>Fecha: <?= formato_fecha($pedido['fecha']) ?></p>
        <p>Estado: <?= $pedido['pagado'] ? 'Pagado' : 'Pendiente' ?></p>
    </div>
<?php if (!empty($pedido['cliente'])): ?>
    <p style="text-align: center; font-size: 18px; margin-top: 10px;">
        Cliente: <?= htmlspecialchars($pedido['cliente']) ?>
    </p>
<?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th class="price">Precio</th>
                <th class="price">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($d = $detalles->fetch_assoc()): $total += $d['subtotal']; ?>
                <tr>
                    <td><?= htmlspecialchars($d['nombre']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td class="price"><?= formato_clp($d['precio_unitario']) ?></td>
                    <td class="price"><?= formato_clp($d['subtotal']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="linea-total"></div>

    <p class="total">Total: <?= formato_clp($total) ?></p>

    <p style="text-align: center; font-size: 22px; margin-top: 20px;">
        PeÃ±a del canto popular, Gracias Por Participar.
    </p>
    
    <div class="feed-space"></div>

    <p style="
        font-size: 10px;
        text-align: center;
        opacity: 1;
        margin: 0;
        padding: 0;
        user-select: none;
    ">.</p>

</body>
</html>
