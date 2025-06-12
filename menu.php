<?php
include 'config.php';

$sql = "SELECT nombre, categoria, precio, disponible FROM productos ORDER BY categoria, nombre";
$resultado = $conn->query($sql);

$es_imprimir = isset($_GET['print']) && $_GET['print'] == 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>La Carta del Evento <?php echo $es_imprimir ? '- Versión para imprimir' : ''; ?></title>

  <?php if (!$es_imprimir): ?>
  <!-- Estilos para versión pantalla -->
  <style>
    :root {
      --color-primario: #2D9CDB;
      --color-fondo: #f0f2f5;
      --color-blanco: #ffffff;
      --sombra: 0 4px 8px rgba(0, 0, 0, 0.1);
      --radio: 12px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--color-fondo);
      padding: 15px;
      color: #333;
    }

    header {
      background-color: var(--color-primario);
      color: var(--color-blanco);
      padding: 20px;
      border-radius: var(--radio);
      text-align: center;
      margin-bottom: 20px;
      box-shadow: var(--sombra);
    }

    h1 {
      font-size: 1.5em;
    }

    .categoria {
      font-size: 1.2em;
      margin-top: 25px;
      color: #2f3640;
      font-weight: bold;
      border-left: 4px solid var(--color-primario);
      padding-left: 10px;
    }

    .producto {
      background-color: var(--color-blanco);
      margin: 10px 0;
      padding: 15px;
      border-radius: var(--radio);
      box-shadow: var(--sombra);
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: 0.2s;
    }

    .producto:hover {
      transform: scale(1.01);
    }

    .nombre {
      font-weight: 500;
      font-size: 1.1em;
    }

    .precio {
      font-weight: bold;
      color: var(--color-primario);
      font-size: 1.1em;
    }

    /* Estilo para productos no disponibles */
    .producto.no-disponible {
      background-color: #ffe6e6;
      color: #cc0000;
      border: 1px solid #cc0000;
    }

    @media (max-width: 600px) {
      .producto {
        flex-direction: column;
        align-items: flex-start;
      }

      .precio {
        margin-top: 5px;
        font-size: 1em;
      }
    }

    .btn-imprimir {
      display: inline-block;
      background-color: var(--color-primario);
      color: white;
      padding: 10px 20px;
      border-radius: var(--radio);
      text-decoration: none;
      font-weight: 600;
      margin-bottom: 20px;
      box-shadow: var(--sombra);
      cursor: pointer;
    }
  </style>

  <?php else: ?>
  <!-- Estilos para versión imprimir -->
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      color: #000;
      background: #fff;
      margin: 20px;
      padding: 0;
    }

    h1 {
      text-align: center;
      font-size: 2em;
      margin-bottom: 1em;
    }

    .categoria-wrapper {
      page-break-inside: avoid;
    }

    .categoria {
      font-size: 1.4em;
      font-weight: bold;
      margin-top: 2em;
      margin-bottom: 0.5em;
      border-bottom: 2px solid #000;
      padding-bottom: 4px;
      color: #000;
      background: none;
      border-left: none;
    }

    .producto {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5em;
      padding-bottom: 2px;
      border-bottom: 1px dotted #666;
      background: none;
      box-shadow: none;
      color: #000;
      border-radius: 0;
      transform: none;
    }

    /* No aplicar estilos especiales para no disponible en imprimir */

    .nombre {
      max-width: 70%;
      word-break: break-word;
    }

    .precio {
      font-weight: bold;
      min-width: 80px;
      text-align: right;
      color: #000;
    }
  </style>

  <script>
    // Al cargar la página, abrir diálogo imprimir automáticamente
    window.onload = function() {
      window.print();
    }
  </script>

  <?php endif; ?>

</head>
<body>

<?php if (!$es_imprimir): ?>
<header>
  <h1>La Carta del Evento</h1>
</header>

<a href="?print=1" target="_blank" class="btn-imprimir" rel="noopener noreferrer">Imprimir Carta</a>

<?php else: ?>
<h1>La Carta del Evento</h1>
<?php endif; ?>

<?php
if ($resultado && $resultado->num_rows > 0) {
    $categoria_actual = "";
    while ($fila = $resultado->fetch_assoc()) {
        if ($fila["categoria"] !== $categoria_actual) {
            if ($categoria_actual !== "") {
                echo "</div>"; // Cerrar el div categoría-wrapper anterior
            }
            $categoria_actual = $fila["categoria"];
            echo "<div class='categoria-wrapper'>";
            echo "<div class='categoria'>" . htmlspecialchars($categoria_actual) . "</div>";
        }

        if ($es_imprimir) {
            // Versión imprimir sin indicaciones de disponibilidad ni clases extras
            $clase_disponible = "";
            $nombre_producto = htmlspecialchars($fila["nombre"]);
        } else {
            // Versión pantalla con indicación y clase para no disponible
            $clase_disponible = ($fila["disponible"] === 'no') ? "no-disponible" : "";
            $nombre_producto = htmlspecialchars($fila["nombre"]);
            if ($fila["disponible"] === 'no') {
                $nombre_producto .= " <span style='font-weight: normal; font-size: 0.9em;'>(No disponible)</span>";
            }
        }

        echo "<div class='producto $clase_disponible'>
                <div class='nombre'>$nombre_producto</div>
                <div class='precio'>$" . number_format($fila["precio"], 0, ',', '.') . "</div>
              </div>";
    }
    if ($categoria_actual !== "") {
        echo "</div>"; // Cerrar último div categoría-wrapper
    }
} else {
    echo "<p>No hay productos disponibles.</p>";
}

$conn->close();
?>

</body>
</html>
