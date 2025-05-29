<?php
include 'config.php';

$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pedido_id <= 0) {
    die("ID inválido");
}

// Obtener productos para selección
$productos = $conn->query("SELECT * FROM productos WHERE disponible = 1 ORDER BY categoria, nombre")->fetch_all(MYSQLI_ASSOC);


// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Primero, eliminar detalles actuales del pedido
    $stmtDelete = $conn->prepare("DELETE FROM pedido_detalle WHERE pedido_id = ?");
    $stmtDelete->bind_param("i", $pedido_id);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Insertar nuevos detalles
    $stmtDetalle = $conn->prepare("INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($_POST['producto_id']); $i++) {
        $prod_id = intval($_POST['producto_id'][$i]);
        $cantidad = intval($_POST['cantidad'][$i]);
        if ($prod_id > 0 && $cantidad > 0) {
            // Obtener precio actual del producto
            $stmtPrecio = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
            $stmtPrecio->bind_param("i", $prod_id);
            $stmtPrecio->execute();
            $stmtPrecio->bind_result($precio);
            $stmtPrecio->fetch();
            $stmtPrecio->close();

            $stmtDetalle->bind_param("iiid", $pedido_id, $prod_id, $cantidad, $precio);
            $stmtDetalle->execute();
        }
    }
    $stmtDetalle->close();

    // Redirigir después de guardar
    header("Location: https://relamticket.cl/comida/pedido.php?id=$pedido_id");
    exit;
}

// Obtener datos actuales del pedido para mostrar en formulario
$pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch_assoc();
if (!$pedido) {
    die("Pedido no encontrado");
}

// Obtener detalles actuales
$detalles = $conn->query("
    SELECT d.producto_id, d.cantidad, p.nombre, p.categoria 
    FROM pedido_detalle d
    JOIN productos p ON d.producto_id = p.id
    WHERE d.pedido_id = $pedido_id
")->fetch_all(MYSQLI_ASSOC);

// Agrupar productos por categoría para JS
$productosPorCategoria = [];
foreach ($productos as $p) {
    $productosPorCategoria[$p['categoria']][] = $p;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Editar Pedido #<?= htmlspecialchars($pedido_id) ?></title>
<style>
    /* El mismo CSS que usaste para el formulario (simplificado para el ejemplo) */
    body { font-family: 'Segoe UI', sans-serif; margin: 40px auto; max-width: 700px; background-color: #f0f2f5; padding: 20px; }
    h2 { text-align: center; margin-bottom: 20px; }
    .pedido-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 3px 12px rgba(0,0,0,0.12); border: 1px solid #ddd; }
    .item-row { display: flex; gap: 15px; margin-bottom: 18px; align-items: center; }
    select, input[type="number"] { padding: 10px 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 6px; }
    select.categoria-select { flex: 2; min-width: 140px; }
    select.producto-select { flex: 4; min-width: 250px; }
    input[type="number"] { flex: 1; max-width: 100px; }
    select:disabled { background-color: #eee; }
    button { background-color: #28a745; color: white; border: none; padding: 12px 22px; font-size: 16px; border-radius: 6px; cursor: pointer; margin-top: 15px; }
    button:hover { background-color: #218838; }
    .btn-group { display: flex; justify-content: flex-start; gap: 10px; margin-top: 10px; }
</style>
</head>
<body>
    <h2>Editar Pedido #<?= htmlspecialchars($pedido_id) ?></h2>
    <div class="pedido-box">
        <form method="POST" id="editar-pedido-form">
            <div id="form-container">
                <?php foreach ($detalles as $detalle): ?>
                <div class="item-row">
                    <select name="categoria[]" class="categoria-select" required>
                        <option value="">Seleccione categoría</option>
                        <?php foreach (array_keys($productosPorCategoria) as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $detalle['categoria'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="producto_id[]" class="producto-select" required>
                        <option value="">Seleccione producto</option>
                        <?php 
                        if (isset($productosPorCategoria[$detalle['categoria']])) {
                            foreach ($productosPorCategoria[$detalle['categoria']] as $prod) {
                                $selected = ($prod['id'] == $detalle['producto_id']) ? 'selected' : '';
                                echo "<option value=\"{$prod['id']}\" $selected>" . htmlspecialchars($prod['nombre']) . "</option>";
                            }
                        }
                        ?>
                    </select>

                    <input type="number" name="cantidad[]" min="1" value="<?= intval($detalle['cantidad']) ?>" required />
                </div>
                <?php endforeach; ?>
            </div>

            <div class="btn-group">
                <button type="button" onclick="agregarFila()">+ Agregar otro producto</button>
                <button type="submit">Guardar Cambios</button>
            </div>
        </form>
    </div>

<script>
    const productosPorCategoria = <?= json_encode($productosPorCategoria, JSON_UNESCAPED_UNICODE) ?>;

    function actualizarProductos(selectCategoria, selectProducto) {
        const categoria = selectCategoria.value;
        selectProducto.innerHTML = '<option value="">Seleccione producto</option>';
        if (categoria && productosPorCategoria[categoria]) {
            productosPorCategoria[categoria].forEach(p => {
                const option = document.createElement('option');
                option.value = p.id;
                option.textContent = p.nombre;
                selectProducto.appendChild(option);
            });
            selectProducto.disabled = false;
        } else {
            selectProducto.disabled = true;
        }
    }

    document.getElementById('form-container').addEventListener('change', function(e) {
        if (e.target.classList.contains('categoria-select')) {
            const fila = e.target.closest('.item-row');
            const selectProducto = fila.querySelector('.producto-select');
            actualizarProductos(e.target, selectProducto);
        }
    });

    function agregarFila() {
        const container = document.getElementById('form-container');
        const nuevaFila = container.children[0].cloneNode(true);
        nuevaFila.querySelector('.categoria-select').value = '';
        const prodSelect = nuevaFila.querySelector('.producto-select');
        prodSelect.innerHTML = '<option value="">Seleccione producto</option>';
        prodSelect.disabled = true;
        nuevaFila.querySelector('input[type=number]').value = '';
        container.appendChild(nuevaFila);
    }
</script>

</body>
</html>
