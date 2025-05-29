<?php
include 'config.php';

$id = intval($_GET['id']);
$clave_correcta = '1999pedro';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $clave = $_POST['clave'] ?? '';

    if ($clave !== $clave_correcta) {
        $mensaje_error = "Contraseña incorrecta.";
    } else {
        if ($accion === 'toggle_pago') {
            // Obtener total pedido (sumando cantidad * precio_unitario)
            $result = $conn->query("
                SELECT SUM(d.cantidad * d.precio_unitario) AS total
                FROM pedido_detalle d
                WHERE d.pedido_id = $id
            ");
            $row = $result->fetch_assoc();
            $totalPedido = intval(round($row['total'] ?? 0)); // Convertir a entero

            $nuevo_estado = $_POST['estado_actual'] == '1' ? 0 : 1;

            if ($nuevo_estado == 1) {
                // Marcar como pagado y guardar monto
                $conn->query("UPDATE pedidos SET pagado = 1, monto_pagado = $totalPedido WHERE id = $id");
            } else {
                // Marcar como no pagado y monto en 0
                $conn->query("UPDATE pedidos SET pagado = 0, monto_pagado = 0 WHERE id = $id");
            }

            header("Location: pedido.php?id=$id");
            exit;
        } elseif ($accion === 'eliminar') {
            $conn->query("DELETE FROM pedido_detalle WHERE pedido_id = $id");
            $conn->query("DELETE FROM pedidos WHERE id = $id");
            header("Location: index.php");
            exit;
        }
    }
}

// Obtener datos del pedido
$pedido = $conn->query("SELECT * FROM pedidos WHERE id = $id")->fetch_assoc();
if (!$pedido) {
    echo "<p>Pedido no encontrado.</p>";
    exit;
}

// Obtener detalles
$detalles = $conn->query("
    SELECT p.nombre, d.cantidad, d.precio_unitario, (d.cantidad * d.precio_unitario) AS subtotal
    FROM pedido_detalle d
    JOIN productos p ON d.producto_id = p.id
    WHERE d.pedido_id = $id
");

$total = 0;
function formato_clp($valor) {
    return '$ ' . number_format($valor, 0, '', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Pedido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* (Tu CSS original aquí, para no repetirlo, pero mantenlo igual) */
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 { margin-top: 0; color: #2c3e50; }
        p { margin-bottom: 10px; color: #555; }
        .estado-pago {
            font-weight: bold;
            margin-bottom: 15px;
        }
        .estado-pago span.pagado { color: green; }
        .estado-pago span.no-pagado { color: red; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #2980b9;
            color: white;
        }
        tr:nth-child(even) { background-color: #f2f6fa; }
        tr:hover { background-color: #e9f3ff; }
        .total-row td {
            font-weight: bold;
            background-color: #ecf0f1;
        }
        .price { text-align: right; }
        .acciones {
            margin-top: 20px;
        }
        .boton {
            display: inline-block;
            margin: 5px;
            padding: 10px 18px;
            background-color: #2980b9;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .boton:hover { background-color: #1f5f8b; }
        .eliminar { background-color: #c0392b; }
        .eliminar:hover { background-color: #992d22; }
        .mensaje-error {
            color: red;
            margin-top: 10px;
        }
        @media print {
            .acciones { display: none; }
            body, .container, #contenido-ticket, * {
                color: black !important;
                background-color: white !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="contenido-ticket">
            <h2>Pedido #<?= $id ?> (Mesa <?= htmlspecialchars($pedido['mesa']) ?>)</h2>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha']) ?></p>
            <p class="estado-pago">
                Estado: 
                <?php if ($pedido['pagado']): ?>
                    <span class="pagado">Pagado</span>
                <?php else: ?>
                    <span class="no-pagado">No Pagado</span>
                <?php endif; ?>
            </p>

            <?php if (isset($mensaje_error)): ?>
                <p class="mensaje-error"><?= $mensaje_error ?></p>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
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
                    <tr class="total-row">
                        <td colspan="3">Total</td>
                        <td class="price"><?= formato_clp($total) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="acciones">
            <button class="boton" onclick="cambiarEstado()">
                <?= $pedido['pagado'] ? 'Marcar como No Pagado' : 'Marcar como Pagado' ?>
            </button>
            <button class="boton" onclick="modificarPedido()">Modificar Pedido</button>
            <button class="boton eliminar" onclick="eliminarPedido()">Eliminar Pedido</button>
            <button class="boton" onclick="window.print()">Imprimir</button>
            <a href="ticket_pedido.php?id=<?= $id ?>" target="_blank" class="boton">Imprimir Formato Ticket</a>

            <a href="index.php" class="boton">Volver</a>
        </div>
    </div>

    <form id="formAccion" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="">
        <input type="hidden" name="clave" value="">
        <input type="hidden" name="estado_actual" value="<?= $pedido['pagado'] ?>">
    </form>

    <script>
    function pedirClaveYEnviar(accion) {
        const clave = prompt("Ingrese la Password:");
        if (clave !== null) {
            const form = document.getElementById('formAccion');
            form.accion.value = accion;
            form.clave.value = clave;
            form.submit();
        }
    }

    function cambiarEstado() {
        pedirClaveYEnviar('toggle_pago');
    }

    function eliminarPedido() {
        if (confirm("¿Estás seguro de que quieres eliminar este pedido?")) {
            pedirClaveYEnviar('eliminar');
        }
    }

    function modificarPedido() {
        const clave = prompt("Ingrese la Password:");
        if (clave === "1999pedro") {
            window.location.href = "modificar_pedido.php?id=<?= $id ?>";
        } else if (clave !== null) {
            alert("Contraseña incorrecta.");
        }
    }

    function imprimirTermica() {
        const contenido = document.getElementById('contenido-ticket').innerHTML;
        const ventana = window.open('', '', 'width=800,height=1000,left=100,top=50');
        ventana.document.write(`
            <html><head><title>Ticket</title>
            <style>
                body { width: 58mm; font-family: monospace; font-size: 11px; margin: 0; padding: 10px; }
                h2, p { text-align: center; margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; font-size: 11px; }
                th, td { padding: 4px; text-align: left; }
                .price { text-align: right; }
                .total { font-weight: bold; border-top: 1px dashed #000; margin-top: 5px; padding-top: 5px; }
            </style>
            </head><body onload="window.print(); window.close();">${contenido}</body></html>
        `);
        ventana.document.close();
    }
    </script>
</body>
</html>