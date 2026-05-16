<?php
session_start();

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1){
    header("Location: index.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<script>alert('ID no válido.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';
$id_postulacion = intval($_GET['id']);

//iniciamos transacción para borrar todo en orden seguro
$conexion->begin_transaction();

try {
    //eliminar de Etapa_Cronograma
    $stmt1 = $conexion->prepare("DELETE FROM Etapa_Cronograma WHERE ID_Postulacion = ?");
    $stmt1->bind_param("i", $id_postulacion);
    $stmt1->execute();

    //eliminar de Postulacion_Integrante
    $stmt2 = $conexion->prepare("DELETE FROM Postulacion_Integrante WHERE Numero_Postulacion = ?");
    $stmt2->bind_param("i", $id_postulacion);
    $stmt2->execute();

    //eliminar de Iniciativa
    $stmt3 = $conexion->prepare("DELETE FROM Iniciativa WHERE ID_Postulacion = ?");
    $stmt3->bind_param("i", $id_postulacion);
    $stmt3->execute();

    //eliminar la cabecera en Postulacion
    $stmt4 = $conexion->prepare("DELETE FROM Postulacion WHERE Numero_Postulacion = ?");
    $stmt4->bind_param("i", $id_postulacion);
    $stmt4->execute();

    //confirmamos la eliminación total
    $conexion->commit();

    echo "<script>
            alert('Postulación #" . $id_postulacion . " eliminada con éxito junto a todos sus componentes.');
            window.location.href='principal.php';
          </script>";

} catch (Exception $e) {
    //si algo falla, deshacemos todo para no dejar datos solitos
    $conexion->rollback();
    echo "<script>
            alert('Error al eliminar la postulación: " . $e->getMessage() . "');
            window.location.href='principal.php';
          </script>";
}
?>