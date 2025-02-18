<?php
session_start();
include('db/db.php');

// Verifica si los datos del formulario se enviaron
if (isset($_POST['usuario']) && isset($_POST['password'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $USUARIO = validate($_POST['usuario']);
    $PASSWORD = validate($_POST['password']);

    // Verifica que los campos no estén vacíos
    if (empty($USUARIO)) {
        header("Location: index.php?error=El usuario es requerido");
        exit();
    } elseif (empty($PASSWORD)) {
        header("Location: index.php?error=La contraseña es requerida");
        exit();
    } else {
        //$PASSWORD = md5($PASSWORD); // Descomenta si usas md5
        $consulta = "SELECT * FROM personal WHERE usuario = '$USUARIO' AND password = '$PASSWORD'";
        $resultado = mysqli_query($conexion, $consulta);

        if (!$resultado) {
            die("Error en la consulta: " . mysqli_error($conexion));
        }

        $filas = mysqli_num_rows($resultado);

        if ($filas) {
            $row = mysqli_fetch_assoc($resultado);
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['correo'] = $row['correo'];
            $_SESSION['ID'] = $row['ID'];
            header("Location: home.php");
            exit();
        } else {
            // Incluye el archivo index.php en lugar de index.html
            include("index.php");   
        }

        mysqli_free_result($resultado);
        mysqli_close($conexion);
    }
} else {
    // Si no se enviaron los datos del formulario, redirige al index
    header("Location: index.php?error=Por favor, ingrese usuario y contraseña");
    exit();
}
?>