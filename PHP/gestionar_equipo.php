<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';

// Consulta para traer a todos los integrantes con su tipo y sede
$sql = "SELECT ie.ID_integrante, ie.Nombre_Integrante, ie.RUT_Integrante, ie.Departamento_Integrante, 
               ie.Mail_Integrante, ie.Telefono_Integrante, tp.Nombre_Tipo_Persona, s.Nombre_Sede 
        FROM Integrante_Equipo ie
        JOIN Tipo_Persona tp ON ie.ID_Tipo_Persona = tp.ID_Tipo_Persona
        JOIN Sede s ON ie.ID_Sede = s.ID_Sede
        ORDER BY ie.Nombre_Integrante ASC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Equipo - CT-USM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="principal.php">← Volver a Postulaciones</a>
    <span class="navbar-text text-white">
        Usuario: <?php echo $_SESSION['usuario']; ?>
    </span>
  </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Directorio de Integrantes (Equipo de Trabajo)</h2>
        <?php if ($_SESSION['rol'] == 1): ?>
            <a href="crear_integrante.php" class="btn btn-success"> Agregar Nuevo Integrante</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive bg-white p-3 rounded shadow-sm mb-5">
        <table class="table table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Tipo</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Contacto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado->num_rows > 0): ?>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fila['Nombre_Integrante']); ?></td>
                            <td><?php echo htmlspecialchars($fila['RUT_Integrante']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo $fila['Nombre_Tipo_Persona']; ?></span></td>
                            <td><?php echo htmlspecialchars($fila['Departamento_Integrante']); ?></td>
                            <td><?php echo htmlspecialchars($fila['Nombre_Sede']); ?></td>
                            <td>
                                <small>
                                    <?php echo htmlspecialchars($fila['Mail_Integrante']); ?><br>
                                    <?php echo htmlspecialchars($fila['Telefono_Integrante'] ?? 'N/A'); ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($_SESSION['rol'] == 1): ?>
                                    <a href="editar_integrante.php?id=<?php echo $fila['ID_integrante']; ?>" class="btn btn-sm btn-warning mb-1">Editar</a>
                                    <a href="eliminar_integrante.php?id=<?php echo $fila['ID_integrante']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('¿Seguro que deseas eliminar a este integrante? Podría causar errores si ya está en una postulación.')">Borrar</a>
                                <?php else: ?>
                                    <span class="text-muted small">Sin permisos</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No hay integrantes registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>