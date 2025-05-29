<?php 
include 'config.php'; // conexión a la base de datos

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Obtener mesas con pedidos y si tienen pedidos no pagados
$sql = "
    SELECT 
        mesa,
        SUM(CASE WHEN pagado = 0 THEN 1 ELSE 0 END) AS pedidosNoPagados
    FROM pedidos
    GROUP BY mesa
    ORDER BY mesa ASC
";
$result = $conn->query($sql);

$mesasConPedidos = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $mesasConPedidos[] = [
            'mesa' => $row['mesa'],
            'noPagados' => (int)$row['pedidosNoPagados'] > 0
        ];
    }
}

// Obtener total de pedidos pagados
$sqlPagados = "SELECT COUNT(*) as totalPagados FROM pedidos WHERE pagado = 1";
$resultPagados = $conn->query($sqlPagados);
$totalPagados = 0;
if ($resultPagados && $resultPagados->num_rows > 0) {
    $row = $resultPagados->fetch_assoc();
    $totalPagados = $row['totalPagados'];
}

// Obtener total de pedidos pendientes
$sqlPendientes = "SELECT COUNT(*) as totalPendientes FROM pedidos WHERE pagado = 0";
$resultPendientes = $conn->query($sqlPendientes);
$totalPendientes = 0;
if ($resultPendientes && $resultPendientes->num_rows > 0) {
    $row = $resultPendientes->fetch_assoc();
    $totalPendientes = $row['totalPendientes'];
}

// Obtener total de ventas
$sqlTotalVentas = "SELECT SUM(monto_pagado) as totalVentas FROM pedidos WHERE pagado = 1";
$resultTotalVentas = $conn->query($sqlTotalVentas);
$totalVentas = 0;
if ($resultTotalVentas && $resultTotalVentas->num_rows > 0) {
    $row = $resultTotalVentas->fetch_assoc();
    $totalVentas = $row['totalVentas'] !== null ? (int)$row['totalVentas'] : 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Mesas Disponibles y Pedidos Pagados</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #333;
        }
        h1 {
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 2.2rem;
            color: #222;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .mesas-container {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(120px,1fr));
            gap: 20px;
            width: 100%;
            max-width: 700px;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        a.mesa-btn {
            background: #007bff;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 18px 0;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,123,255,0.3);
            transition: background 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }
        a.mesa-btn:hover {
            background: #0056b3;
            box-shadow: 0 6px 14px rgba(0,86,179,0.5);
        }
        a.mesa-btn:active {
            background: #003d80;
            box-shadow: 0 2px 6px rgba(0,61,128,0.7);
            transform: scale(0.97);
        }
        a.mesa-btn.no-pagado {
            background: #dc3545;
            box-shadow: 0 4px 10px rgba(220,53,69,0.3);
        }
        a.mesa-btn.no-pagado:hover {
            background: #b02a37;
            box-shadow: 0 6px 14px rgba(176,42,55,0.5);
        }
        a.mesa-btn.no-pagado:active {
            background: #841f2c;
            box-shadow: 0 2px 6px rgba(132,31,44,0.7);
        }
        .info-pedidos {
            font-size: 1.2rem;
            font-weight: 600;
            color: #444;
            background: #e1e8f0;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 280px;
        }
    </style>
</head>
<body>
    <h1>Mesas con Pedidos</h1>
    <div class="mesas-container">
        <?php if (!empty($mesasConPedidos)): ?>
            <?php foreach ($mesasConPedidos as $mesa): ?>
                <?php 
                    $mesaId = htmlspecialchars($mesa['mesa']);
                    $claseExtra = $mesa['noPagados'] ? 'no-pagado' : '';
                ?>
                <a class="mesa-btn <?= $claseExtra ?>" href="mesa.php?mesa=<?= $mesaId ?>">
                    Mesa <?= $mesaId ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay mesas con pedidos.</p>
        <?php endif; ?>
    </div>

    <div class="info-pedidos">
        <p>Total de pedidos pagados: <strong><?= $totalPagados ?></strong></p>
        <p>Total de pedidos pendientes: <strong><?= $totalPendientes ?></strong></p>
        <p>Total de ventas: <strong>$<?= number_format($totalVentas, 0, ',', '.') ?></strong></p>
    </div>
</body>
</html>
