<?php

session_start();

require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['usuario'];
    $pass = $_POST['contrasena'];
    $rol_ingresado = $_POST['rol'];

    $sql = "SELECT ID_Usuario, Rol FROM Usuarios WHERE Correo = ? AND Contrasena = ? AND Rol = ?";
    $stmt = $conexion->prepare($sql);
    
    $stmt->bind_param("ssi", $correo, $pass, $rol_ingresado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    //si encuentra un usuario que coincida
    if ($resultado->num_rows > 0) {
        //guardamos los datos en la sesión y lo mandamos a la página principal
        $_SESSION['usuario'] = $correo;
        $_SESSION['rol'] = $rol_ingresado;
        header("Location: principal.php"); 
        exit();
    } else {
        //si los datos están mal, tira un error y lo devuelve al login
        echo "<script>alert('Correo, contraseña o rol incorrectos. Intente nuevamente.'); window.location.href='index.php';</script>";
    }
}
?>