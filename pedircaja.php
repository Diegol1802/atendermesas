<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : 0;
if ($mesa <= 0) die("Mesa invalida");

// Si viene pedido_id en GET, mostramos resumen
if (isset($_GET['pedido_id'])) {
    $pedido_id = intval($_GET['pedido_id']);

    // Consultar detalles del pedido
    $stmt = $conn->prepare("
        SELECT p.nombre, d.cantidad, d.precio_unitario
        FROM pedido_detalle d
        JOIN productos p ON d.producto_id = p.id
        WHERE d.pedido_id = ?
    ");
    if (!$stmt) die("Error preparando consulta resumen: " . $conn->error);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $pedidoItems = [];
    $totalPedido = 0;

    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['precio_unitario'] * $row['cantidad'];
        $totalPedido += $subtotal;
        $pedidoItems[] = [
            'nombre' => $row['nombre'],
            'cantidad' => $row['cantidad'],
            'precio' => $row['precio_unitario'],
            'subtotal' => $subtotal
        ];
    }
    $stmt->close();
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Pedido en Caja <?= htmlspecialchars($mesa) ?></title>
        <style>
            body {
                font-family: 'Segoe UI', sans-serif;
                margin: 20px;
                background-color: #f0f2f5;
                padding: 10px;
            }
            h2 {
                text-align: center;
                font-size: 28px;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 18px;
            }
            th, td {
                padding: 12px;
                border-bottom: 1px solid #ddd;
                text-align: center;
            }
            th {
                background-color: #28a745;
                color: white;
            }
            tfoot td {
                font-weight: bold;
                font-size: 20px;
            }
            a {
                display: block;
                margin-top: 30px;
                text-align: center;
                font-size: 20px;
                color: #007bff;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <h2>Pedido en Caja<?= htmlspecialchars($mesa) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidoItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td><?= $item['cantidad'] ?></td>
                        <td>$<?= number_format($item['precio'], 0, ',', '.') ?></td>
                        <td>$<?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td>$<?= number_format($totalPedido, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </body>
    </html>

    <?php
    exit;
}

// Mostrar formulario o procesar POST
$productos = $conn->query("SELECT * FROM productos WHERE disponible = 1 ORDER BY categoria, nombre")->fetch_all(MYSQLI_ASSOC);
$categorias = array_unique(array_column($productos, 'categoria'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['producto_id'], $_POST['cantidad']) ||
        !is_array($_POST['producto_id']) || !is_array($_POST['cantidad'])) {
        die("Datos del formulario incompletos");
    }

    $fechaChile = new DateTime('now', new DateTimeZone('America/Santiago'));
    $fechaPedido = $fechaChile->format('Y-m-d H:i:s');

    $stmtPedido = $conn->prepare("INSERT INTO pedidos (mesa, fecha) VALUES (?, ?)");
    if (!$stmtPedido) {
        die("Error preparando pedido: " . $conn->error);
    }
    $stmtPedido->bind_param("is", $mesa, $fechaPedido);
    if (!$stmtPedido->execute()) {
        die("Error ejecutando pedido: " . $stmtPedido->error);
    }
    $pedido_id = $stmtPedido->insert_id;
    $stmtPedido->close();

    $stmtDetalle = $conn->prepare("INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    if (!$stmtDetalle) {
        die("Error preparando detalle: " . $conn->error);
    }

    for ($i = 0; $i < count($_POST['producto_id']); $i++) {
        $id = intval($_POST['producto_id'][$i]);
        $cantidad = intval($_POST['cantidad'][$i]);
        if ($cantidad > 0 && $id > 0) {
            $stmtPrecio = $conn->prepare("SELECT precio FROM productos WHERE id = ?");
            $stmtPrecio->bind_param("i", $id);
            $stmtPrecio->execute();
            $stmtPrecio->bind_result($precio);
            $stmtPrecio->fetch();
            $stmtPrecio->close();

            $stmtDetalle->bind_param("iiid", $pedido_id, $id, $cantidad, $precio);
            if (!$stmtDetalle->execute()) {
                die("Error insertando detalle: " . $stmtDetalle->error);
            }
        }
    }
    $stmtDetalle->close();

    file_get_contents("https://relamticket.cl/comida/pagadocaja.php?id=$pedido_id");

    header("Location: ticket_pedido.php?id=$pedido_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pedido en Caja <?= htmlspecialchars($mesa) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 20px auto;
            max-width: 700px;
            background-color: #f0f2f5;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
        }
        .pedido-box {
            background: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.12);
            border: 1px solid #ddd;
            min-height: 350px;
        }
        .input-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        select, input[type="number"] {
            padding: 14px 16px;
            font-size: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        select.categoria-select {
            flex: 2;
            min-width: 150px;
        }
        select.producto-select {
            flex: 4;
            min-width: 270px;
        }
        input[type="number"] {
            flex: 1;
            max-width: 120px;
        }
        select:disabled {
            background-color: #eee;
        }
        select:focus, input[type="number"]:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
        }
        button {
            background-color: #5dade2;
            color: white;
            border: none;
            padding: 16px 26px;
            font-size: 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
            user-select: none;
        }
        button:hover {
            background-color: #218838;
        }
        .btn-group {
            display: flex;
            justify-content: flex-start;
            gap: 15px;
            margin-top: 15px;
        }
        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            font-size: 18px;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
            }
            .pedido-box {
                padding: 30px 20px;
            }
            h2 {
                font-size: 24px;
            }
            .input-row {
                flex-direction: column;
                align-items: stretch;
            }
            select.categoria-select, select.producto-select, input[type="number"] {
                flex: none;
                width: 100%;
                margin-bottom: 12px;
            }
            .btn-group {
                flex-direction: column;
            }
            button {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <h2>Pedido en Caja <?= htmlspecialchars($mesa) ?></h2>

    <div class="pedido-box">
        <div class="input-row">
            <select id="categoria-select" class="categoria-select" required>
                <option value="">Seleccione categoria</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>

            <select id="producto-select" class="producto-select" required disabled>
                <option value="">Seleccione producto</option>
            </select>

            <input type="number" id="cantidad-input" min="1" placeholder="Cantidad" required />

            <button type="button" id="add-product-btn">Agregar</button>
        </div>

        <form method="POST" id="pedido-form" onsubmit="prepararEnvio()">
            <table id="productos-table" aria-label="Productos agregados">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aqui se agregan filas dinamicamente -->
                </tbody>
            </table>

            <button type="submit" id="submit-btn" disabled>Enviar Pedido</button>
        </form>
    </div>

<script>
const productosPorCategoria = <?= json_encode(array_reduce($productos, function($acc, $prod){
    $acc[$prod['categoria']][] = [
        'id' => $prod['id'],
        'nombre' => $prod['nombre'],
        'precio' => $prod['precio']
    ];
    return $acc;
}, [])) ?>;

const categoriaSelect = document.getElementById('categoria-select');
const productoSelect = document.getElementById('producto-select');
const cantidadInput = document.getElementById('cantidad-input');
const addProductBtn = document.getElementById('add-product-btn');
const productosTableBody = document.querySelector('#productos-table tbody');
const pedidoForm = document.getElementById('pedido-form');
const submitBtn = document.getElementById('submit-btn');

let productosAgregados = [];

categoriaSelect.addEventListener('change', () => {
    actualizarProductos(categoriaSelect.value);
    productoSelect.value = "";
});

function actualizarProductos(categoria) {
    if (!categoria || !productosPorCategoria[categoria]) {
        productoSelect.innerHTML = '<option value="">Seleccione producto</option>';
        productoSelect.disabled = true;
        return;
    }

    let options = '<option value="">Seleccione producto</option>';
    for (const prod of productosPorCategoria[categoria]) {
        options += `<option value="${prod.id}">${prod.nombre} ($${prod.precio.toLocaleString('es-CL')})</option>`;
    }
    productoSelect.innerHTML = options;
    productoSelect.disabled = false;
}

addProductBtn.addEventListener('click', () => {
    const categoria = categoriaSelect.value;
    const productoId = productoSelect.value;
    const cantidad = cantidadInput.value;

    if (!categoria) {
        alert('Seleccione una categoria');
        return;
    }
    if (!productoId) {
        alert('Seleccione un producto');
        return;
    }
    if (!cantidad || cantidad < 1) {
        alert('Ingrese una cantidad valida');
        return;
    }

    const producto = productosPorCategoria[categoria].find(p => p.id == productoId);
    if (!producto) {
        alert('Producto no valido');
        return;
    }

    // Si ya existe producto agregado, sumar cantidad
    const indexExistente = productosAgregados.findIndex(p => p.producto_id == productoId);
    if (indexExistente >= 0) {
        productosAgregados[indexExistente].cantidad = parseInt(productosAgregados[indexExistente].cantidad) + parseInt(cantidad);
    } else {
        productosAgregados.push({
            categoria,
            producto_id: productoId,
            cantidad: parseInt(cantidad),
            nombre: producto.nombre,
            precio: producto.precio
        });
    }

    limpiarCampos();
    renderizarTabla();
    actualizarSubmitBtn();
});

function limpiarCampos() {
    categoriaSelect.value = "";
    productoSelect.innerHTML = '<option value="">Seleccione producto</option>';
    productoSelect.disabled = true;
    cantidadInput.value = "";
}

function renderizarTabla() {
    productosTableBody.innerHTML = "";
    productosAgregados.forEach((item, index) => {
        const tr = document.createElement('tr');

        const tdNombre = document.createElement('td');
        tdNombre.textContent = `${item.nombre} ($${item.precio.toLocaleString('es-CL')})`;

        const tdCantidad = document.createElement('td');
        tdCantidad.textContent = item.cantidad;

        const tdAcciones = document.createElement('td');

        // Boton editar
        const btnEditar = document.createElement('button');
        btnEditar.textContent = 'Editar';
        btnEditar.style.marginRight = '8px';
        btnEditar.type = 'button';
        btnEditar.onclick = () => editarProducto(index);

        // Boton borrar
        const btnBorrar = document.createElement('button');
        btnBorrar.textContent = 'Borrar';
        btnBorrar.type = 'button';
        btnBorrar.onclick = () => borrarProducto(index);

        tdAcciones.appendChild(btnEditar);
        tdAcciones.appendChild(btnBorrar);

        tr.appendChild(tdNombre);
        tr.appendChild(tdCantidad);
        tr.appendChild(tdAcciones);

        productosTableBody.appendChild(tr);
    });
}

function editarProducto(index) {
    const item = productosAgregados[index];
    // Colocar valores en los campos para editar
    categoriaSelect.value = item.categoria;
    actualizarProductos(item.categoria);
    productoSelect.value = item.producto_id;
    cantidadInput.value = item.cantidad;

    // Quitar producto de lista para editar
    productosAgregados.splice(index, 1);
    renderizarTabla();
    actualizarSubmitBtn();
}

function borrarProducto(index) {
    productosAgregados.splice(index, 1);
    renderizarTabla();
    actualizarSubmitBtn();
}

function prepararEnvio() {
    if (productosAgregados.length === 0) {
        alert('Agregue al menos un producto antes de enviar el pedido.');
        return false;
    }
    // Limpiar inputs anteriores
    while (pedidoForm.querySelector('input[name="producto_id[]"]')) {
        pedidoForm.querySelector('input[name="producto_id[]"]').remove();
    }
    while (pedidoForm.querySelector('input[name="cantidad[]"]')) {
        pedidoForm.querySelector('input[name="cantidad[]"]').remove();
    }

    // Agregar inputs hidden para enviar datos
    productosAgregados.forEach(item => {
        const inputProd = document.createElement('input');
        inputProd.type = 'hidden';
        inputProd.name = 'producto_id[]';
        inputProd.value = item.producto_id;

        const inputCant = document.createElement('input');
        inputCant.type = 'hidden';
        inputCant.name = 'cantidad[]';
        inputCant.value = item.cantidad;

        pedidoForm.appendChild(inputProd);
        pedidoForm.appendChild(inputCant);
    });

    return true;
}

function actualizarSubmitBtn() {
    submitBtn.disabled = productosAgregados.length === 0;
}
</script>

</body>
</html>
