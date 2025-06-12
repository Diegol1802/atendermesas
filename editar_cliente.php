<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die("ID de pedido inválido.");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoCliente = trim($_POST['cliente'] ?? '');
    if ($nuevoCliente === '') {
        $error = "El nombre del cliente no puede estar vacío.";
    } else {
        $stmtUpdate = $conn->prepare("UPDATE pedidos SET cliente = ? WHERE id = ?");
        $stmtUpdate->bind_param('si', $nuevoCliente, $id);
        if ($stmtUpdate->execute()) {
            $stmtUpdate->close();
            // Redirigir a pedido.php con el id
            header("Location: pedido.php?id=" . $id);
            exit;
        } else {
            $error = "Error al actualizar el nombre del cliente.";
        }
    }
}

// Obtener cliente actual
$stmt = $conn->prepare("SELECT cliente FROM pedidos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) die("Pedido no encontrado.");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Editar Cliente Pedido #<?= htmlspecialchars($id) ?></title>
<style>
  /* Estilos como antes */
  * { box-sizing: border-box; }
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    margin: 0; padding: 0;
    display: flex;
    height: 100vh;
    align-items: center;
    justify-content: center;
  }
  .container {
    background: white;
    padding: 2rem 3rem;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
  }
  h1 {
    margin-bottom: 1rem;
    font-weight: 700;
    color: #333;
    text-align: center;
  }
  .btn-back {
    display: inline-block;
    margin-bottom: 1.5rem;
    color: #64748b;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    border: 1.5px solid #64748b;
    padding: 0.4rem 1rem;
    border-radius: 6px;
    transition: background-color 0.3s ease, color 0.3s ease;
  }
  .btn-back:hover,
  .btn-back:focus {
    background-color: #64748b;
    color: white;
    outline: none;
  }
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #555;
  }
  input[type="text"] {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    border: 1.8px solid #ddd;
    border-radius: 6px;
    transition: border-color 0.3s ease;
  }
  input[type="text"]:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
  }
  button {
    margin-top: 1.5rem;
    width: 100%;
    padding: 0.75rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: white;
    background: #007bff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background: #0056b3;
  }
  .message {
    margin: 1rem 0 0 0;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    text-align: center;
  }
  .message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  .current-name {
    margin-top: 1rem;
    font-style: italic;
    color: #666;
    text-align: center;
  }
</style>
</head>
<body>
  <div class="container" role="main" aria-labelledby="pageTitle">
    <h1 id="pageTitle">Editar Cliente Pedido #<?= htmlspecialchars($id) ?></h1>

    <?php if (!empty($error)) : ?>
      <div class="message error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
      <label for="cliente">Nombre del Cliente</label>
      <input
        type="text"
        id="cliente"
        name="cliente"
        value="<?= htmlspecialchars($pedido['cliente']) ?>"
        required
        autocomplete="off"
        placeholder="Ingresa el nombre del cliente"
      />
      <button type="submit">Actualizar</button>
    </form>

    <p class="current-name">Nombre actual: <strong><?= htmlspecialchars($pedido['cliente']) ?></strong></p>
  </div>
</body>
</html>
