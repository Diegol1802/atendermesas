<?php
include 'config.php';
$mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : 0;
$res = $conn->query("SELECT * FROM pedidos WHERE mesa = $mesa ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Mesa <?= $mesa ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .pedido-card {
            transition: transform 0.2s ease;
        }
        .pedido-card:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Pedidos de la Mesa <?= $mesa ?></h2>
            <a href="index.php" class="btn btn-outline-secondary">Volver</a>
        </div>

        <?php if ($res->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-3">
                <?php while ($row = $res->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card pedido-card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Pedido #<?= $row['id'] ?></h5>
                                <p class="card-text text-muted">Fecha: <?= $row['fecha'] ?></p>
                                <a href="pedido.php?id=<?= $row['id'] ?>" class="btn btn-primary">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No hay pedidos para esta mesa.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS (opcional si no usas componentes interactivos) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
