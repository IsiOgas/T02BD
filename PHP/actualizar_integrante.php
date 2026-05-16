<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nombre = $_POST['nombre'];
    $rut = $_POST['rut'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $departamento = $_POST['departamento'];
    $tipo_persona = $_POST['tipo_persona'];
    $sede = $_POST['sede'];

    $sql = "UPDATE Integrante_Equipo 
            SET Nombre_Integrante = ?, RUT_Integrante = ?, Departamento_Integrante = ?, Mail_Integrante = ?, Telefono_Integrante = ?, ID_Tipo_Persona = ?, ID_Sede = ? 
            WHERE ID_integrante = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssiii", $nombre, $rut, $departamento, $correo, $telefono, $tipo_persona, $sede, $id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Datos del integrante actualizados con éxito.'); 
                window.location.href='gestionar_equipo.php';
              </script>";
    } else {
        echo "<script>
                alert('Error al actualizar los datos: " . $stmt->error . "'); 
                window.history.back();
              </script>";
    }
} else {
    header("Location: gestionar_equipo.php");
}
?>