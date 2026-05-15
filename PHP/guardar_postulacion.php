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
    
    $codigo = $_POST['codigo'];
    $fecha = $_POST['fecha'];
    $presupuesto = $_POST['presupuesto'];
    $sede = $_POST['sede'];
    $region_ejecucion = $_POST['region_ejecucion'];
    $region_impacto = $_POST['region_impacto'];
    $empresa = $_POST['empresa'];
    $tipo_iniciativa = $_POST['tipo_iniciativa'];
    
    $estado_borrador = 1; 

    // Verificamos si existe un máximo, de lo contrario empezamos en 1
    $resultado_id = $conexion->query("SELECT MAX(Numero_Postulacion) AS max_id FROM Postulacion");
    $fila_id = $resultado_id->fetch_assoc();
    $nuevo_numero = ($fila_id['max_id'] !== null) ? $fila_id['max_id'] + 1 : 1;

    $sql = "INSERT INTO Postulacion (Numero_Postulacion, Fecha_Postulacion, Codigo_Postulacion, Presupuesto_Total, Rut_Empresa, ID_Sede, ID_Region_Ejecucion, ID_Region_Impacto, ID_Tipo_Iniciativa, ID_Estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    // Si hay un error en la consulta SQL, nos avisará aquí en vez de quedar en blanco
    if (!$stmt) {
        die("❌ Error en la preparación de SQL: " . $conexion->error);
    }
    
    $stmt->bind_param("issdsiiiii", $nuevo_numero, $fecha, $codigo, $presupuesto, $empresa, $sede, $region_ejecucion, $region_impacto, $tipo_iniciativa, $estado_borrador);

    if ($stmt->execute()) {
        echo "<script>
                alert('¡Éxito! La postulación principal ha sido creada. Número asignado: $nuevo_numero'); 
                window.location.href='principal.php';
              </script>";
    } else {
        echo "<script>
                alert('Error al guardar: " . $stmt->error . "'); 
                window.history.back();
              </script>";
    }
} else {
    // Si entras directo al archivo sin pasar por el botón, te avisará
    echo "<h1>Error: No enviaste ningún formulario.</h1>";
    echo "<a href='crear_postulacion.php'>Volver atrás</a>";
}
?>