<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='gestionar_equipo.php';</script>";
    exit();
}

require 'conexion.php';
$id_integrante = intval($_GET['id']);

try {
    $stmt = $conexion->prepare("DELETE FROM Integrante_Equipo WHERE ID_integrante = ?");
    $stmt->bind_param("i", $id_integrante);
    
    if ($stmt->execute()) {
        echo "<script>
                alert('Integrante eliminado con éxito del directorio.');
                window.location.href='gestionar_equipo.php';
              </script>";
    }
} catch (mysqli_sql_exception $e) {
    // Si arroja error por integridad referencial (llave foránea)
    echo "<script>
            alert('No se puede eliminar al integrante porque actualmente participa en un proyecto activo. Primero debes removerlo del equipo del proyecto.');
            window.location.href='gestionar_equipo.php';
          </script>";
}
?>