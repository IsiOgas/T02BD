<?php
session_start();

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1){
    header("Location: index.php");
    exit();
}

require 'conexion.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $id = intval($_POST['id']);
    $codigo = $_POST['codigo'];
    $presupuesto = $_POST['presupuesto'];
    $sede = $_POST['sede'];
    $region_ejecucion = $_POST['region_ejecucion'];
    $region_impacto = $_POST['region_impacto'];
    
    $nombre_ini = $_POST['nombre_iniciativa'];
    $objetivo_ini = $_POST['objetivo_iniciativa'];
    $desc_ini = $_POST['descripcion_soluciones'];
    $resultados_ini = $_POST['resultados_esperados'];

    $conexion->begin_transaction();

    try{
        //actualizar tabla Postulacion
        $sql_post = "UPDATE Postulacion SET Codigo_Postulacion = ?, Presupuesto_Total = ?, ID_Sede = ?, ID_Region_Ejecucion = ?, ID_Region_Impacto = ? WHERE Numero_Postulacion = ?";
        $stmt_post = $conexion->prepare($sql_post);
        $stmt_post->bind_param("sdsiii", $codigo, $presupuesto, $sede, $region_ejecucion, $region_impacto, $id);
        $stmt_post->execute();

        //actualizar tabla Iniciativa
        $sql_ini = "UPDATE Iniciativa SET Nombre_Iniciativa = ?, Objetivo_Iniciativa = ?, Descripcion_Soluciones = ?, Resultados_Esperados = ? WHERE ID_Postulacion = ?";
        $stmt_ini = $conexion->prepare($sql_ini);
        $stmt_ini->bind_param("ssssi", $nombre_ini, $objetivo_ini, $desc_ini, $resultados_ini, $id);
        $stmt_ini->execute();

        $conexion->commit();

        echo "<script>
                alert('¡Postulación actualizada correctamente!');
                window.location.href='principal.php';
              </script>";

    } catch (Exception $e){
        $conexion->rollback();
        echo "<script>
                alert('Error al actualizar: " . $e->getMessage() . "');
                window.history.back();
              </script>";
    }
}
?>