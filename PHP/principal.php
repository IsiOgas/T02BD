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
                $sql = "SELECT p.Numero_Postulacion, p.Codigo_Postulacion, i.Nombre_Iniciativa, e.Nombre_Empresa, s.Nombre_Sede, 
                               r.Nombre_Region AS Region_Ejecucion, p.Presupuesto_Total, est.Nombre_Estado
                        FROM Postulacion p
                        LEFT JOIN Iniciativa i ON p.Numero_Postulacion = i.ID_Postulacion
                        JOIN Entidad_Empresa e ON p.Rut_Empresa = e.Rut_Empresa
                        JOIN Sede s ON p.ID_Sede = s.ID_Sede
                        JOIN Region r ON p.ID_Region_Ejecucion = r.ID_Region
                        JOIN Estado_Postulacion est ON p.ID_Estado = est.ID_Estado";

                // Preguntamos: ¿El usuario apretó el botón de buscar y escribió algo?
                if (isset($_GET['buscar']) && $_GET['buscar'] != '') {
                    
                    // Si es así, le agregamos la condición WHERE a nuestra consulta SQL
                    // Usamos LIKE para buscar coincidencias parciales
                    $sql .= " WHERE p.Codigo_Postulacion LIKE ? 
                              OR i.Nombre_Iniciativa LIKE ? 
                              OR e.Nombre_Empresa LIKE ?
                              OR s.Nombre_Sede LIKE ?";

                    
                    // Preparamos la consulta (¡Bonus de seguridad contra inyección SQL!)
                    $stmt = $conexion->prepare($sql);
                    
                    // Le agregamos los % al inicio y al final. 
                    // Esto le dice a MySQL: "Encuentra este texto en cualquier parte de la oración"
                    $termino = "%" . $_GET['buscar'] . "%";
                    
                    // Pasamos el término 3 veces porque tenemos tres signos de interrogación '?' en el WHERE
                    $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
                } else {
                    // Si la barra está vacía, preparamos la consulta normal sin filtros
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
                        echo "<td><a href='detalle_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-warning'>Ver Detalle</a></td>";
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