<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Recuperar mensaje de sesion si existe
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Definir la password para cambios
$PASSWORD_CAMBIO = '1999pedro'; // Cambia esto por tu password real, puede venir de config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['password'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $password = $_POST['password'];

    if ($password === $PASSWORD_CAMBIO) {
        // Verificar que exista producto
        $sqlCheck = "SELECT disponible FROM productos WHERE nombre = '$nombre' LIMIT 1";
        $resultCheck = $conn->query($sqlCheck);

        if ($resultCheck && $resultCheck->num_rows === 1) {
            $row = $resultCheck->fetch_assoc();
            $nuevoEstado = ($row['disponible'] === 'si') ? 'no' : 'si';

            $sqlUpdate = "UPDATE productos SET disponible = '$nuevoEstado' WHERE nombre = '$nombre'";
            if ($conn->query($sqlUpdate)) {
                // Guardar mensaje en sesion y redirigir para evitar reenviar formulario
                $_SESSION['mensaje'] = "Estado cambiado con exito para '$nombre'.";
                header('Location: productos.php');
                exit();
            } else {
                $mensaje = "Error al actualizar estado: " . $conn->error;
            }
        } else {
            $mensaje = "Producto no encontrado.";
        }
    } else {
        $mensaje = "Password incorrecta.";
    }
}

// Obtener lista de productos
$sql = "SELECT nombre, disponible FROM productos ORDER BY nombre ASC";
$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Productos - Cambiar Disponibilidad</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

    body {
        margin: 0;
        background-color: #f0f4ff;
        font-family: 'Inter', sans-serif;
        color: #0a2342;
        padding: 40px 20px;
    }

    h2 {
        text-align: center;
        font-weight: 600;
        font-size: 2rem;
        margin-bottom: 20px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #0a2342;
    }

    .mensaje {
        max-width: 800px;
        margin: 10px auto 30px auto;
        padding: 15px 20px;
        background-color: #d4edda;
        color: #155724;
        border-radius: 10px;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 4px 12px rgba(18, 76, 170, 0.15);
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }

    table {
        margin: 0 auto;
        width: 90%;
        max-width: 800px;
        border-collapse: separate;
        border-spacing: 0 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(10, 35, 66, 0.12);
        overflow: hidden;
    }

    thead tr {
        background-color: #124caa;
        color: #e1e9ff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.08em;
    }

    thead tr th {
        padding: 18px 24px;
        text-align: left;
    }

    tbody tr {
        background-color: #f8fbff;
        transition: background-color 0.3s ease;
        cursor: default;
    }

    tbody tr:hover {
        background-color: #e4ecff;
    }

    tbody tr td {
        padding: 16px 24px;
        font-size: 1rem;
        vertical-align: middle;
        text-align: left;
        color: #0a2342;
    }

    tbody tr td.status {
        text-align: center;
        font-weight: 600;
        font-size: 0.95rem;
        color: #124caa;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    tbody tr td.status.no {
        color: #bf1e2e;
    }

    .btn {
        display: inline-block;
        padding: 10px 28px;
        background-color: #124caa;
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        border-radius: 8px;
        box-shadow: 0 6px 12px rgba(18, 76, 170, 0.35);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
        cursor: pointer;
        border: none;
    }

    .btn:hover {
        background-color: #0a3678;
        box-shadow: 0 8px 20px rgba(10, 54, 120, 0.45);
    }

    /* Modal (simple) */
    .modal-bg {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(10, 35, 66, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }
    .modal {
        background: white;
        border-radius: 12px;
        padding: 30px 40px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 30px rgba(10, 35, 66, 0.25);
        text-align: center;
    }
    .modal h3 {
        margin-top: 0;
        margin-bottom: 20px;
        font-weight: 600;
        color: #124caa;
    }
    .modal input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        border: 1.5px solid #124caa;
        font-size: 1rem;
        font-family: 'Inter', sans-serif;
        outline: none;
        transition: border-color 0.3s ease;
    }
    .modal input[type="password"]:focus {
        border-color: #0a3678;
    }
    .modal .btn {
        width: 100%;
    }
    .modal .cancel-btn {
        margin-top: 12px;
        background: #bf1e2e;
    }

    /* Responsive */
    @media (max-width: 600px) {
        table, thead tr, tbody tr, tbody tr td {
            display: block;
            width: 100%;
        }
        thead tr {
            display: none;
        }
        tbody tr {
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(18, 76, 170, 0.15);
            border-radius: 10px;
            padding: 15px 20px;
        }
        tbody tr td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            font-size: 1rem;
        }
        tbody tr td::before {
            content: attr(data-label);
            position: absolute;
            left: 20px;
            width: 45%;
            padding-left: 10px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #124caa;
            text-align: left;
            letter-spacing: 0.05em;
        }
        tbody tr td.status {
            text-align: right;
            padding-left: 50%;
        }
        tbody tr td .btn {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 0;
            font-size: 1rem;
        }
    }
</style>
</head>
<body>

<h2>Lista de productos</h2>

<?php if ($mensaje): ?>
<div class="mensaje <?= (strpos($mensaje, 'error') !== false || strpos($mensaje, 'incorrecta') !== false) ? 'error' : '' ?>">
    <?= htmlspecialchars($mensaje) ?>
</div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Disponible</th>
            <th>Accion</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td data-label="Nombre"><?= htmlspecialchars($row['nombre']) ?></td>
            <td class="status <?= ($row['disponible'] === 'si') ? 'si' : 'no' ?>" data-label="Disponible">
                <?= ($row['disponible'] === 'si') ? 'Si' : 'No' ?>
            </td>
            <td data-label="Accion">
                <button class="btn cambiar-btn" data-nombre="<?= htmlspecialchars($row['nombre']) ?>">
                    Cambiar Estado
                </button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal para ingresar password -->
<div class="modal-bg" id="modal-bg">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <h3 id="modal-title">Ingrese password para cambiar estado</h3>
        <form method="POST" id="form-cambiar">
            <input type="hidden" name="nombre" id="input-nombre" value="" />
            <input type="password" name="password" placeholder="Password" required autocomplete="off" />
            <button type="submit" class="btn">Confirmar</button>
            <button type="button" class="btn cancel-btn" id="btn-cancelar">Cancelar</button>
        </form>
    </div>
</div>

<script>
    const modalBg = document.getElementById('modal-bg');
    const formCambiar = document.getElementById('form-cambiar');
    const inputNombre = document.getElementById('input-nombre');
    const btnCancelar = document.getElementById('btn-cancelar');
    const botonesCambiar = document.querySelectorAll('.cambiar-btn');

    botonesCambiar.forEach(btn => {
        btn.addEventListener('click', () => {
            const nombre = btn.getAttribute('data-nombre');
            inputNombre.value = nombre;
            modalBg.style.display = 'flex';
            formCambiar.password.focus();
        });
    });

    btnCancelar.addEventListener('click', () => {
        modalBg.style.display = 'none';
        formCambiar.reset();
    });

    // Cerrar modal con ESC
    window.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modalBg.style.display === 'flex') {
            modalBg.style.display = 'none';
            formCambiar.reset();
        }
    });
</script>

</body>
</html>
