<?php
session_start();
include('db/db.php');

// Verifica si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_contraseña = mysqli_real_escape_string($conexion, $_POST['nueva_contraseña']);
    $confirmar_contraseña = mysqli_real_escape_string($conexion, $_POST['confirmar_contraseña']);

    // Verificar que los campos no estén vacíos y que las contraseñas coincidan
    if (empty($nueva_contraseña) || empty($confirmar_contraseña)) {
        $error = "Todos los campos son requeridos.";
    } elseif ($nueva_contraseña != $confirmar_contraseña) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Actualiza la contraseña en la base de datos
        $usuario = $_SESSION['usuario'];
        $consulta = "UPDATE personal SET password = '$nueva_contraseña' WHERE usuario = '$usuario'";
        $resultado = mysqli_query($conexion, $consulta);

        if ($resultado) {
            $mensaje = "Contraseña cambiada exitosamente.";
        } else {
            $error = "Hubo un problema al actualizar la contraseña. Intenta de nuevo.";
        }
    }
}
?>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
        display: flex;
    }

    .sidebar {
        width: 250px;
        height: 100vh;
        background-color: #2c3e50;
        color: white;
        padding-top: 20px;
    }

    .sidebar h2 {

        margin-bottom: 20px;
    }

    .sidebar ul {
        list-style-type: none;
        padding: 0;
    }


    .main-content h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #2c3e50;
        margin: 20px;
    }

    .success {
        color: green;
        font-size: 16px;
    }

    .error {
        color: red;
        font-size: 16px;
    }

    form {
        display: flex;
        flex-direction: column;
        max-width: 400px;
        margin: 30px;
        
    }

    form label {
        font-size: 16px;
        margin-bottom: 5px;
    }

    form input {
        padding: 10px;
        margin-bottom: 15px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
        transition: border-color 0.3s;
    }

    form input:focus {
        border-color: #3498db;
    }

    form button {
        padding: 12px;
        background-color: #3498db;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    form button:hover {
        background-color: #2980b9;
    }
</style>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <h2><i class=""></i>Plaza Shopping Center</h2>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Inicio</a></li>
        <li><a href="cambiar.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
        <li><a href="cerrar_sesion.php"><i class="fas fa-unlock"></i> Cerrar Sesión</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <h1>Cambiar Contraseña</h1>

    <?php if (isset($mensaje)) { echo "<p class='success'>$mensaje</p>"; } ?>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

    <form action="cambiar.php" method="POST">
        <label for="nueva_contraseña">Nueva Contraseña</label>
        <input type="password" id="nueva_contraseña" name="nueva_contraseña" required>

        <label for="confirmar_contraseña">Confirmar Contraseña</label>
        <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" required>

        <button type="submit">Cambiar Contraseña</button>
    </form>
</div>

</body>
</html>
