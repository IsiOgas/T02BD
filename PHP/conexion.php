<?php

$servidor = "localhost";
$usuario = "root"; 
$contrasena = ""; 
$base_de_datos = "ct_usm_postulaciones";


$conexion = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);

//verifica si la conexion tuvo algun error
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

//configurar el conjunto de caracteres para no tener problemas con las ñ y tildes
$conexion->set_charset("utf8");


?>