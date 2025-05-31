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
        body {
            font-family: monospace;
            max-width: 58mm;
            margin: auto;
            padding: 10px;
            color: #000;
            background: #fff;
            font-weight: bold; /* Todo en negrita */
        }
        h2, p {
            text-align: center;
            margin: 5px 0;
            font-size: 20px
            
        }
        table {
            width: 120%;
            border-collapse: collapse;
            font-size: 17px;
            font-weight: bold;
        }
        th, td {
            padding: 3px 5px;
            text-align: center;
            border-bottom: 1px dashed #aaa;
        }
        .price {
            text-align: right;
        }
        .total {
            border-top: 1px dashed #000;
            margin-top: 8px;
            padding-top: 5px;
            text-align: right;
            font-size: 17px;
        }
        #btnPrint {
            display: block;
            width: 100%;
            margin: 15px 0;
            padding: 8px;
            font-size: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            user-select: none;
        }
        #btnPrint:hover {
            background-color: #0056b3;
        }
        /* El botón "Volver" visible en pantalla */
        #btnVolver {
            display: block;
            width: 100%;
            padding: 8px;
            font-size: 14px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        /* Al imprimir ocultamos ambos botones */
        @media print {
            #btnPrint, #btnVolver {
                display: none;
            }
            body {
                max-width: 58mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <h2>Mesa: <?= htmlspecialchars($pedido['mesa']) ?></h2> 
    <p>Pedido #<?= $id ?></p>
    <p>Fecha: <?= formato_fecha($pedido['fecha']) ?></p>
    <p>Estado: <strong><?= $pedido['pagado'] ? 'Pagado' : 'Pendiente' ?></strong></p>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th class="price">Precio.</th>
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
    <p class="total">Total: <?= formato_clp($total) ?></p>

    <p>Peña del canto popular, Gracias Por Participar</p>
    <p>.</p>
    <p>.</p>

    <button id="btnPrint" onclick="window.print()">Imprimir Ticket</button>
    <button id="btnVolver" onclick="window.location.href='index.php'">Volver</button>

</body>
</html>