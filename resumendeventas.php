<?php
include 'config.php';

// Consulta SQL
$sql = "
    SELECT 
        p.nombre AS nombre_producto,
        SUM(pd.cantidad) AS cantidad_total,
        SUM(pd.cantidad * pd.precio_unitario) AS total_ventas
    FROM pedido_detalle pd
    JOIN productos p ON pd.producto_id = p.id
    GROUP BY pd.producto_id
    ORDER BY total_ventas DESC
";

$resultado = $conn->query($sql);

// Guardamos los resultados en un array para reutilizar
$datos = [];
$totalGeneral = 0;

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
        $totalGeneral += $fila['total_ventas'];
    }
}

// Descargar Excel
if (isset($_GET['descargar']) && $_GET['descargar'] === 'xls') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=reporte_ventas.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr><th>Producto</th><th>Cantidad Vendido</th><th>Total Vendido</th></tr>";

    foreach ($datos as $fila) {
        echo "<tr>
                <td>" . htmlspecialchars($fila['nombre_producto']) . "</td>
                <td>" . (int)$fila['cantidad_total'] . "</td>
                <td>" . number_format($fila['total_ventas'], 0, ',', '.') . "</td>
              </tr>";
    }

    echo "<tr>
            <td colspan='2'><strong>Total General</strong></td>
            <td><strong>" . number_format($totalGeneral, 0, ',', '.') . "</strong></td>
          </tr>";

    echo "</table>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resumen de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        h2 {
            text-align: center;
            margin-top: 30px;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .boton-descarga {
            display: block;
            width: 200px;
            margin: 10px auto;
            padding: 10px;
            text-align: center;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .boton-descarga:hover {
            background-color: #219150;
        }

        /* Estilo impresión térmica */
        @media print {
            body {
                background: white;
                font-size: 12px;
                margin: 0;
                padding: 0;
            }

            h2 {
                font-size: 16px;
                margin: 5px 0;
                text-align: center;
            }

            .boton-descarga, a {
                display: none;
            }

            table {
                width: 100%;
                font-size: 12px;
                box-shadow: none;
            }

            th, td {
                padding: 5px;
                text-align: left;
                border: none;
            }

            th {
                background-color: white;
                color: black;
                font-weight: bold;
            }

            tr {
                border-bottom: 3px solid black; /* Línea gruesa */
            }

            tr.total-final td {
                font-weight: bold;
                font-size: 13px;
                border-top: 3px double black;
                padding-top: 10px;
            }
        }
    </style>

    <?php if (isset($_GET['imprimir'])): ?>
    <script>
        window.onload = function () {
            window.print();
        };
    </script>
    <?php endif; ?>
</head>
<body>

<h2>Resumen de Ventas por Producto</h2>

<a class="boton-descarga" href="?descargar=xls">Descargar en Excel</a>
<a class="boton-descarga" href="#" onclick="window.print()">Imprimir</a>

<table>
    <tr>
        <th>Producto</th>
        <th>Cantidad Vendido</th>
        <th>Total Vendido</th>
    </tr>

<?php
if (!empty($datos)) {
    foreach ($datos as $fila) {
        echo "<tr>
                <td>" . htmlspecialchars($fila['nombre_producto']) . "</td>
                <td>" . (int)$fila['cantidad_total'] . "</td>
                <td>$" . number_format($fila['total_ventas'], 0, ',', '.') . "</td>
              </tr>";
    }

    // Fila Total General
    echo "<tr class='total-final'>
            <td colspan='2'>TOTAL GENERAL</td>
            <td>$" . number_format($totalGeneral, 0, ',', '.') . "</td>
          </tr>";
} else {
    echo "<tr><td colspan='3'>No hay datos disponibles</td></tr>";
}

$conn->close();
?>
</table>

</body>
</html>
