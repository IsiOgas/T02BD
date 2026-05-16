<?php
//siempre iniciamos sesión al principio para mantener los datos del usuario
session_start();

//si alguien intenta entrar a esta página sin loguearse primero, lo pateamos al login
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal - CT-USM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">CT-USM</a>
    <div class="d-flex">
        <span class="navbar-text me-3 text-white">
            Hola, <?php echo $_SESSION['usuario']; ?> (Rol: <?php echo $_SESSION['rol']; ?>)
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Listado General de Postulaciones</h2>

    <div class="mb-3">
    <a href="crear_postulacion.php" class="btn btn-success"> Crear Nueva Postulación</a>
    </div>

    <form class="d-flex mb-4" action="principal.php" method="GET">
        <input class="form-control me-2" type="search" name="buscar" placeholder="Buscar por código, iniciativa o empresa..." aria-label="Search">
        <button class="btn btn-primary" type="submit">Buscar</button>
    </form>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Iniciativa</th>
                    <th>Empresa</th>
                    <th>Sede</th>
                    <th>Región Ejecución</th>
                    <th>Presupuesto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Esta es la consulta base (igual a la de antes)
                // Usamos la VISTA que creamos en la base de datos (¡Requisito de la rúbrica cumplido!)
                $sql = "SELECT * FROM Vista_Postulaciones_Principal";

                // Preguntamos: ¿El usuario apretó el botón de buscar y escribió algo?
                if (isset($_GET['buscar']) && $_GET['buscar'] != '') {
                    
                    $sql .= " WHERE Codigo_Postulacion LIKE ? 
                              OR Nombre_Iniciativa LIKE ? 
                              OR Nombre_Empresa LIKE ?
                              OR Nombre_Sede LIKE ?";

                    $stmt = $conexion->prepare($sql);
                    $termino = "%" . $_GET['buscar'] . "%";
                    $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
                } else {
                    $stmt = $conexion->prepare($sql);
                }
                // Ejecutamos la magia
                $stmt->execute();
                $resultado = $stmt->get_result();

                // Mostramos los resultados
                if ($resultado->num_rows > 0) {
                    while($fila = $resultado->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $fila["Codigo_Postulacion"] . "</td>";
                        echo "<td>" . $fila["Nombre_Iniciativa"] . "</td>";
                        echo "<td>" . $fila["Nombre_Empresa"] . "</td>";
                        echo "<td>" . $fila["Nombre_Sede"] . "</td>";
                        echo "<td>" . $fila["Region_Ejecucion"] . "</td>";
                        // Formateamos el número para que se vea como dinero (ej: $10.000.000)
                        echo "<td>$" . number_format($fila["Presupuesto_Total"], 0, ',', '.') . "</td>";
                        echo "<td><span class='badge bg-info'>" . $fila["Nombre_Estado"] . "</span></td>";
                        echo "<td>";
                        echo "<a href='detalle_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-info me-1 text-white'>Ver Detalle</a>";
                        echo "<a href='editar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-warning me-1'>Editar</a>";
                        echo "<a href='eliminar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Estás seguro de que deseas eliminar esta postulación por completo?\")'>Eliminar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Si buscaron algo que no existe (ej: "asdfgh")
                    echo "<tr><td colspan='7' class='text-center text-muted'>No se encontraron postulaciones con ese término.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>