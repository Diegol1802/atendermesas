<?php
$mensaje = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasena = trim($_POST["contrasena"]);
    $archivo = ".htpasswd";

    if ($usuario && $contrasena) {
        // Verificar si el usuario ya existe
        $existe = false;
        if (file_exists($archivo)) {
            $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lineas as $linea) {
                if (strpos($linea, $usuario . ':') === 0) {
                    $existe = true;
                    break;
                }
            }
        }

        if ($existe) {
            $mensaje = "<div class='msg error'>El usuario <strong>$usuario</strong> ya existe.</div>";
        } else {
            // Encriptar contraseña con Apache MD5
            $hash = crypt($contrasena, base64_encode(random_bytes(6)));
            $linea = $usuario . ':' . $hash . "\n";

            if (file_put_contents($archivo, $linea, FILE_APPEND)) {
                $mensaje = "<div class='msg success'>Usuario <strong>$usuario</strong> agregado correctamente.</div>";
            } else {
                $mensaje = "<div class='msg error'>Error al escribir el archivo <code>.htpasswd</code>.</div>";
            }
        }
    } else {
        $mensaje = "<div class='msg error'>Usuario y Password son obligatorios.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador .htpasswd</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-top: 0;
            font-size: 1.4rem;
            color: #333;
            text-align: center;
        }
        label {
            display: block;
            margin: 20px 0 8px;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #0052a3;
        }
        .msg {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        .success {
            background-color: #e0f9e5;
            color: #1b7a32;
            border: 1px solid #a6dfb0;
        }
        .error {
            background-color: #fdecea;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Crear Usuarios</h2>
        <?= $mensaje ?>
        <form method="post">
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" required>

            <label for="contrasena">Password</label>
            <input type="password" name="contrasena" id="contrasena" required>

            <button type="submit">Crear Usuario</button>
        </form>
    </div>
</body>
</html>
</html>
