<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';
$id_postulacion = intval($_GET['id']);
$correo_usuario = $_SESSION['usuario'];

$sql = "UPDATE Postulacion SET ID_Estado = 1 WHERE Numero_Postulacion = ? AND Correo_Responsable = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("is", $id_postulacion, $correo_usuario);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<script>
                alert('¡Postulación enviada exitosamente al CT-USM! Ya no es un borrador.');
                window.location.href='principal.php';
              </script>";
    } else {
        echo "<script>
                alert('No se pudo enviar. Es posible que la postulación no exista o no te pertenezca.');
                window.location.href='principal.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Error al enviar: " . $stmt->error . "');
            window.history.back();
          </script>";
}
?>