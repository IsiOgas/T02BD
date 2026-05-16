<?php
session_start();

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1){
    header("Location: index.php");
    exit();
}

require 'conexion.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nombre = $_POST['nombre'];
    $rut = $_POST['rut'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $departamento = $_POST['departamento'];
    $tipo_persona = $_POST['tipo_persona'];
    $sede = $_POST['sede'];

    $sql = "INSERT INTO Integrante_Equipo (Nombre_Integrante, RUT_Integrante, Departamento_Integrante, Mail_Integrante, Telefono_Integrante, ID_Tipo_Persona, ID_Sede) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    //vinculamos los parametros: sssssii (5 strings, 2 integers)
    $stmt->bind_param("sssssii", $nombre, $rut, $departamento, $correo, $telefono, $tipo_persona, $sede);

    if ($stmt->execute()) {
        echo "<script>
                alert('Integrante registrado exitosamente.'); 
                window.location.href='gestionar_equipo.php';
              </script>";
    } else {
        //en caso de que el RUT ya exista lanza advertencia
        echo "<script>
                alert('Error al guardar: Posiblemente el RUT ya se encuentra registrado. Detalles: " . $stmt->error . "'); 
                window.history.back();
              </script>";
    }
} else {
    header("Location: gestionar_equipo.php");
}
?>