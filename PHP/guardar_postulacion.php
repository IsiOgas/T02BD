<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1){
    header("Location: index.php");
    exit();
}

require 'conexion.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        $conexion->begin_transaction();

        $resultado = $conexion->query("SELECT MAX(Numero_Postulacion) AS max_id FROM Postulacion");
        $fila = $resultado->fetch_assoc();
        $nuevo_numero = ($fila['max_id']) ? $fila['max_id'] + 1 : 1;

        $codigo = $_POST['codigo'];
        $fecha = $_POST['fecha'];
        $presupuesto = $_POST['presupuesto'];

        $resp1 = $_POST['responsable1'];
        $resp2 = $_POST['responsable2'];
        
        $empresa = $_POST['empresa'];
        $sede = $_POST['sede'];
        $region_ejecucion = $_POST['region_ejecucion'];
        $region_impacto = $_POST['region_impacto'];
        $tipo_iniciativa = $_POST['tipo_iniciativa'];
        
        $estado_borrador = 5; 
        $creador = $_SESSION['usuario'];

        $sql_post = "INSERT INTO Postulacion (Numero_Postulacion, Fecha_Postulacion, Codigo_Postulacion, Presupuesto_Total, Nombre_Responsable_1, Nombre_Responsable_2, Rut_Empresa, ID_Sede, ID_Region_Ejecucion, ID_Region_Impacto, ID_Tipo_Iniciativa, ID_Estado, Correo_Responsable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_post = $conexion->prepare($sql_post);
        if (!$stmt_post) throw new Exception("Error preparar Postulación: " . $conexion->error);

        $stmt_post->bind_param("issdsssiiiiis", $nuevo_numero, $fecha, $codigo, $presupuesto, $resp1, $resp2, $empresa, $sede, $region_ejecucion, $region_impacto, $tipo_iniciativa, $estado_borrador, $creador);
        if (!$stmt_post->execute()) throw new Exception("Error al insertar Postulación: " . $stmt_post->error);

        $nombre_iniciativa = $_POST['nombre_iniciativa'];
        $objetivo = $_POST['objetivo_iniciativa'];
        $soluciones = $_POST['descripcion_soluciones'];
        $resultados = $_POST['resultados_esperados'];

        $sql_ini = "INSERT INTO Iniciativa (Nombre_Iniciativa, Objetivo_Iniciativa, Descripcion_Soluciones, Resultados_Esperados, ID_Postulacion) VALUES (?, ?, ?, ?, ?)";
        $stmt_ini = $conexion->prepare($sql_ini);
        $stmt_ini->bind_param("ssssi", $nombre_iniciativa, $objetivo, $soluciones, $resultados, $nuevo_numero);
        if (!$stmt_ini->execute()) throw new Exception("Error al insertar Iniciativa: " . $stmt_ini->error);

        $sql_equipo = "INSERT INTO Postulacion_Integrante (Numero_Postulacion, ID_integrante, Rol_Cumple_Integrante) VALUES (?, ?, ?)";
        $stmt_equipo = $conexion->prepare($sql_equipo);
        if (isset($_POST['equipo']) && is_array($_POST['equipo'])) {
            $rol_resto = 'Integrante';
            foreach($_POST['equipo'] as $id_integrante){
                $stmt_equipo->bind_param("iis", $nuevo_numero, $id_integrante, $rol_resto);
                if (!$stmt_equipo->execute()) throw new Exception("Error al vincular Integrante: " . $stmt_equipo->error);
            }
        }

        $sql_crono = "INSERT INTO Etapa_Cronograma (ID_Postulacion, Etapa, Entregable, Plazos) VALUES (?, ?, ?, ?)";
        $stmt_crono = $conexion->prepare($sql_crono);
        if (isset($_POST['etapa']) && is_array($_POST['etapa'])) {
            for ($i = 0; $i < count($_POST['etapa']); $i++) {
                $etapa = $_POST['etapa'][$i];
                $entregable = $_POST['entregable'][$i];
                $plazos = $_POST['plazos'][$i];
                
                if (!empty($etapa) && !empty($entregable) && !empty($plazos)) {
                    $stmt_crono->bind_param("issi", $nuevo_numero, $etapa, $entregable, $plazos);
                    if (!$stmt_crono->execute()) throw new Exception("Error al insertar Cronograma: " . $stmt_crono->error);
                }
            }
        }

        $conexion->commit();
        echo "<script>alert('Borrador de postulación guardado con éxito.'); window.location.href='principal.php';</script>";

    } catch (Exception $e) {
        $conexion->rollback();
        
        $error_seguro = addslashes($e->getMessage());
        
        if (strpos($error_seguro, 'Duplicate entry') !== false) {
            echo "<script>
                    alert('Error: Ya existe una postulación con ese Código. Por favor, vuelve atrás e ingresa uno distinto.'); 
                    window.history.back();
                  </script>";
        } else {
            echo "<script>
                    alert('Error en Base de Datos: " . $error_seguro . "'); 
                    window.history.back();
                  </script>";
        }
    }
}
?>