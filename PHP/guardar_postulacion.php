<?php
// ENCENDEMOS EL MODO DEPREDADOR DE ERRORES (Solo para probar)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibimos datos de Postulación
    $codigo = $_POST['codigo'];
    $fecha = $_POST['fecha'];
    $presupuesto = $_POST['presupuesto'];
    $sede = $_POST['sede'];
    $region_ejecucion = $_POST['region_ejecucion'];
    $region_impacto = $_POST['region_impacto'];
    $empresa = $_POST['empresa'];
    $tipo_iniciativa = $_POST['tipo_iniciativa'];
    $estado_borrador = 1; 

    // 2. Recibimos datos de Iniciativa
    $nombre_ini = $_POST['nombre_iniciativa'];
    $objetivo_ini = $_POST['objetivo_iniciativa'];
    $desc_ini = $_POST['descripcion_soluciones'];
    $resultados_ini = $_POST['resultados_esperados'];


    // Calculamos el ID (Como lo tenían antes)
    $resultado_id = $conexion->query("SELECT MAX(Numero_Postulacion) AS max_id FROM Postulacion");
    $fila_id = $resultado_id->fetch_assoc();
    $nuevo_numero = ($fila_id['max_id'] !== null) ? $fila_id['max_id'] + 1 : 1;

    // INICIAMOS LA TRANSACCIÓN (Si algo falla, no se guarda nada)
    $conexion->begin_transaction();

    try {
        // --- A) INSERTAR POSTULACIÓN ---
        $sql_post = "INSERT INTO Postulacion (Numero_Postulacion, Fecha_Postulacion, Codigo_Postulacion, Presupuesto_Total, Rut_Empresa, ID_Sede, ID_Region_Ejecucion, ID_Region_Impacto, ID_Tipo_Iniciativa, ID_Estado) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_post = $conexion->prepare($sql_post);
        if (!$stmt_post) throw new Exception("Error al preparar Postulación: " . $conexion->error);
        
        $stmt_post->bind_param("issdsiiiii", $nuevo_numero, $fecha, $codigo, $presupuesto, $empresa, $sede, $region_ejecucion, $region_impacto, $tipo_iniciativa, $estado_borrador);
        if (!$stmt_post->execute()) throw new Exception("Error al insertar Postulación: " . $stmt_post->error);

        // --- B) INSERTAR INICIATIVA ---
        $sql_ini = "INSERT INTO Iniciativa (Nombre_Iniciativa, Objetivo_Iniciativa, Descripcion_Soluciones, Resultados_Esperados, ID_Postulacion) 
                    VALUES (?, ?, ?, ?, ?)";
        
        $stmt_ini = $conexion->prepare($sql_ini);
        if (!$stmt_ini) throw new Exception("Error al preparar Iniciativa: " . $conexion->error);

        // SI TODO SALIÓ BIEN, CONFIRMAMOS LOS CAMBIOS EN LA BD
        // ... (tu código anterior)
        // "ssssi" = 4 strings y 1 integer (ID_Postulacion)
        $stmt_ini->bind_param("ssssi", $nombre_ini, $objetivo_ini, $desc_ini, $resultados_ini, $nuevo_numero);
        if (!$stmt_ini->execute()) throw new Exception("Error al insertar Iniciativa: " . $stmt_ini->error);

        // --- C) INSERTAR EQUIPO DE TRABAJO ---
        $equipo = $_POST['equipo']; // Es un arreglo con los IDs
        $sql_equipo = "INSERT INTO Postulacion_Integrante (Numero_Postulacion, ID_integrante) VALUES (?, ?)";
        $stmt_equipo = $conexion->prepare($sql_equipo);
        if (!$stmt_equipo) throw new Exception("Error al preparar Equipo: " . $conexion->error);

        foreach ($equipo as $id_integrante) {
            $stmt_equipo->bind_param("ii", $nuevo_numero, $id_integrante);
            if (!$stmt_equipo->execute()) throw new Exception("Error al vincular Integrante: " . $stmt_equipo->error);
        }

        // --- D) INSERTAR CRONOGRAMA ---
        $etapas = $_POST['etapa'];
        $entregables = $_POST['entregable'];
        $plazos = $_POST['plazos'];

        $sql_etapa = "INSERT INTO Etapa_Cronograma (ID_Postulacion, Etapa, Entregable, Plazos) VALUES (?, ?, ?, ?)";
        $stmt_etapa = $conexion->prepare($sql_etapa);
        if (!$stmt_etapa) throw new Exception("Error al preparar Cronograma: " . $conexion->error);

        // Recorremos los arreglos para insertar cada etapa que no esté vacía
        for ($i = 0; $i < count($etapas); $i++) {
            if (!empty($etapas[$i]) && !empty($entregables[$i]) && !empty($plazos[$i])) {
                $stmt_etapa->bind_param("issi", $nuevo_numero, $etapas[$i], $entregables[$i], $plazos[$i]);
                if (!$stmt_etapa->execute()) throw new Exception("Error al insertar Etapa: " . $stmt_etapa->error);
            }
        }

        // SI TODO SALIÓ BIEN, CONFIRMAMOS LOS CAMBIOS EN LA BD
        $conexion->commit();

        echo "<script>
                alert('¡Éxito! La postulación y su iniciativa han sido creadas. Número: $nuevo_numero'); 
                window.location.href='principal.php';
              </script>";

    } catch (Exception $e) {
        // SI ALGO FALLÓ, DESHACEMOS CUALQUIER CAMBIO (Rollback)
        $conexion->rollback();
        echo "<script>
                alert('Ocurrió un error: " . $e->getMessage() . "'); 
                window.history.back();
              </script>";
    }
} else {
    echo "<h1>Error: No enviaste ningún formulario.</h1><a href='crear_postulacion.php'>Volver atrás</a>";
}
?>