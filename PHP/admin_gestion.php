<?php
session_start();

//si no es el Admin, lo sacamos 
if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 3){
    echo "<script>alert('Acceso denegado. Solo administradores.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';

//si el admin presiono el boton de guardar asignacion
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_postulacion'])){
    $id_post = intval($_POST['id_postulacion']);
    $id_eval = intval($_POST['id_evaluador']);
    
    $stmt = $conexion->prepare("UPDATE Postulacion SET ID_Evaluador = ? WHERE Numero_Postulacion = ?");
    $stmt->bind_param("ii", $id_eval, $id_post);
    
    if($stmt->execute()){
        echo "<script>alert('¡Evaluador asignado con éxito!'); window.location.href='admin_gestion.php';</script>";
    } else {
        echo "<script>alert('Error al asignar.'); window.history.back();</script>";
    }
    exit();
}

//obtenemos la lista de evaluadores (Usuarios con el rol = 2)
$evaluadores = $conexion->query("SELECT ID_Usuario, Correo FROM Usuarios WHERE Rol = 2");
$lista_evaluadores = $evaluadores->fetch_all(MYSQLI_ASSOC);

//obtenemos todas las postulaciones para mostrarlas en la tabla
$sql = "SELECT p.Numero_Postulacion, p.Codigo_Postulacion, i.Nombre_Iniciativa, p.ID_Evaluador, u.Correo AS Correo_Eval, est.Nombre_Estado 
        FROM Postulacion p
        LEFT JOIN Iniciativa i ON p.Numero_Postulacion = i.ID_Postulacion
        JOIN Estado_Postulacion est ON p.ID_Estado = est.ID_Estado
        LEFT JOIN Usuarios u ON p.ID_Evaluador = u.ID_Usuario";
$postulaciones = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Admin - CT-USM</title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="principal.php">← Volver al Panel</a>
    <span class="navbar-text text-white fw-bold">
        Panel de Administración | Usuario: <?php echo $_SESSION['usuario']; ?>
    </span>
  </div>
</nav>

<div class="container mb-5">
    <h2 class="mb-4">Gestión de Evaluadores y Asignaciones</h2>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Código Postulación</th>
                        <th>Iniciativa</th>
                        <th>Estado</th>
                        <th>Evaluador Asignado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $postulaciones->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $p['Codigo_Postulacion']; ?></strong></td>
                            <td><?php echo htmlspecialchars($p['Nombre_Iniciativa'] ?? 'Sin nombre'); ?></td>
                            <td><span class="badge bg-secondary"><?php echo $p['Nombre_Estado']; ?></span></td>
                            
                            <form action="admin_gestion.php" method="POST">
                                <td>
                                    <input type="hidden" name="id_postulacion" value="<?php echo $p['Numero_Postulacion']; ?>">
                                    <select class="form-select form-select-sm" name="id_evaluador" required>
                                        <option value="">-- Seleccionar Evaluador --</option>
                                        <?php foreach($lista_evaluadores as $eval): ?>
                                            <option value="<?php echo $eval['ID_Usuario']; ?>" <?php if($p['ID_Evaluador'] == $eval['ID_Usuario']) echo 'selected'; ?>>
                                                <?php echo $eval['Correo']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>