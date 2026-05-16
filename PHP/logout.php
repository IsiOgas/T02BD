<?php
//iniciamos la sesion para poder destruirla
session_start();

//borramos todas las variables de sesion (como el usuario y el rol)
session_unset();

//destruimos la sesion por completo
session_destroy();

//lo devolvemos a la pantalla de login
header("Location: index.php");
exit();
?>