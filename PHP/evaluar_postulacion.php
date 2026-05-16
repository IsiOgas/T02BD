<?php
session_start();

//solo el Rol 2 evaluador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] == 1) {
    echo "<script>alert('No tienes permisos para evaluar.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $id_postulacion = intval($_POST['id_postulacion']);
    $nuevo_estado = intval($_POST['estado']);

    //usamos el Procedimiento Almacenado
    $stmt = $conexion->prepare("CALL CambiarEstadoPostulacion(?, ?)");
    $stmt->bind_param("ii", $id_postulacion, $nuevo_estado);

    if($stmt->execute()){
        echo "<script>alert('Estado actualizado exitosamente.'); window.location.href='principal.php';</script>";
    } else{
        echo "<script>alert('Error al actualizar: " . $conexion->error . "'); window.history.back();</script>";
    }
    exit();
}

if(!isset($_GET['id'])){
    header("Location: principal.php");
    exit();
}
$id = intval($_GET['id']);
$sql_seguridad = "SELECT u.Correo FROM Postulacion p LEFT JOIN Usuarios u ON p.ID_Evaluador = u.ID_Usuario WHERE p.Numero_Postulacion = ?";
$stmt_seg = $conexion->prepare($sql_seguridad);
$stmt_seg->bind_param("i", $id);
$stmt_seg->execute();
$resultado_seg = $stmt_seg->get_result()->fetch_assoc();

if (!$resultado_seg || $resultado_seg['Correo'] != $_SESSION['usuario']) {
    echo "<script>alert('Acceso denegado: Esta postulación no te ha sido asignada por el Administrador.'); window.location.href='principal.php';</script>";
    exit();
}
$estados = $conexion->query("SELECT * FROM Estado_Postulacion WHERE Nombre_Estado != 'Borrador'");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluar Postulación - CT-USM</title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width: 500px;">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Evaluar Postulación #<?php echo $id; ?></h5>
        </div>
        <div class="card-body">
            <form action="evaluar_postulacion.php" method="POST">
                <input type="hidden" name="id_postulacion" value="<?php echo $id; ?>">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Seleccione la nueva resolución:</label>
                    <select class="form-select form-select-lg" name="estado" required>
                        <?php while($e = $estados->fetch_assoc()) { ?>
                            <option value="<?php echo $e['ID_Estado']; ?>"><?php echo $e['Nombre_Estado']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1">Confirmar Evaluación</button>
                    <a href="principal.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>