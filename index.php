<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="css/styles_inicio_sesion.css">
    <title>Inicio</title>
</head>
<body>
    <form action="iniciar_sesion.php" method="POST">
        <h1>INICIAR SESION</h1>
        <hr>
        
        <?php
            if (isset($_GET['error'])) {
                ?>
            <p class="error">
                <?php
                echo $_GET['error']
                ?>
            </p>
        <?php   
            }
        ?>


        <i class="fa-solid fa-user"></i>
        <label for="">Usuario</label>
        <input type="text" name="usuario" placeholder="Nombre de usuario" required>
        <i class="fa-solid fa-unlock"></i>
        <label for="">Contraseña</label>

        <input type="text" name="password" placeholder="Contraseña", required>
        <hr>

        <button type="submit">Iniciar Sesion</button>

        <!-- href="crear_cuenta.php">Crear Cuenta</a-->
    </form>
    
</body>
</html>