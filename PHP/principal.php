<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';

//catalogos para el filtro avanzado
$sedes = $conexion->query("SELECT Nombre_Sede FROM Sede");
$estados = $conexion->query("SELECT Nombre_Estado FROM Estado_Postulacion");

//logica de busqueda avanzada
$sql = "SELECT v.* FROM Vista_Postulaciones_Principal v ";

$filtros = [];
$tipos_datos = "";

//si el usuario presionó "Mis Postulaciones" y es Rol 1
if (isset($_GET['mis_postulaciones']) && $_SESSION['rol'] == 1) {
    $sql .= " JOIN Postulacion p ON v.Numero_Postulacion = p.Numero_Postulacion WHERE p.Correo_Responsable = ?";
    $filtros[] = $_SESSION['usuario'];
    $tipos_datos .= "s";
} else {
    $sql .= " WHERE 1=1";
    if ($_SESSION['rol'] == 2 || $_SESSION['rol'] == 3) {
        $sql .= " AND v.Nombre_Estado != 'Borrador'";
    }
}

if (!empty($_GET['buscar'])) {
    $sql .= " AND (v.Codigo_Postulacion LIKE ? OR v.Nombre_Iniciativa LIKE ? OR v.Nombre_Empresa LIKE ?)";
    $termino = "%" . $_GET['buscar'] . "%";
    array_push($filtros, $termino, $termino, $termino);
    $tipos_datos .= "sss";
}

//filtro por Sede
if (!empty($_GET['sede'])) {
    $sql .= " AND v.Nombre_Sede = ?";
    $filtros[] = $_GET['sede'];
    $tipos_datos .= "s";
}

//filtro por Estado
if (!empty($_GET['estado'])) {
    $sql .= " AND v.Nombre_Estado = ?";
    $filtros[] = $_GET['estado'];
    $tipos_datos .= "s";
}

$stmt = $conexion->prepare($sql);

if (!empty($filtros)) {
    $stmt->bind_param($tipos_datos, ...$filtros);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Principal - CT-USM</title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="principal.php">CT-USM</a>
    <div class="d-flex align-items-center">
        <span class="navbar-text me-3 text-white fw-bold">
            Usuario: <?php echo $_SESSION['usuario']; ?> (Rol: <?php echo $_SESSION['rol']; ?>)
        </span>
        <?php if ($_SESSION['rol'] == 1): ?>
            <a href="gestionar_equipo.php" class="btn btn-info btn-sm me-2 text-white fw-bold">Directorio Equipo</a>
        <?php endif; ?>

        <?php if ($_SESSION['rol'] == 3): ?>
            <a href="admin_gestion.php" class="btn btn-danger btn-sm me-2 text-white fw-bold">Gestión Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Panel de Postulaciones</h2>
        <div>
            <?php if ($_SESSION['rol'] == 1): ?>
                <a href="principal.php?mis_postulaciones=1" class="btn btn-warning btn-lg shadow-sm me-2">Mis Postulaciones</a>
                <a href="principal.php" class="btn btn-secondary btn-lg shadow-sm me-2">Ver Todas</a>
                <a href="crear_postulacion.php" class="btn btn-success btn-lg shadow-sm">Crear Nueva Postulación</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body bg-white rounded">
            <form action="principal.php" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Buscar por código, nombre o empresa</label>
                    <input type="text" class="form-control" name="buscar" placeholder="Ej: PROY-001..." value="<?php echo $_GET['buscar'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Filtrar por Sede</label>
                    <select class="form-select" name="sede">
                        <option value="">Todas las sedes</option>
                        <?php while($s = $sedes->fetch_assoc()) { ?>
                            <option value="<?php echo $s['Nombre_Sede']; ?>" <?php if(isset($_GET['sede']) && $_GET['sede'] == $s['Nombre_Sede']) echo 'selected'; ?>><?php echo $s['Nombre_Sede']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Filtrar por Estado</label>
                    <select class="form-select" name="estado">
                        <option value="">Todos los estados</option>
                        <?php while($e = $estados->fetch_assoc()) { ?>
                            <option value="<?php echo $e['Nombre_Estado']; ?>" <?php if(isset($_GET['estado']) && $_GET['estado'] == $e['Nombre_Estado']) echo 'selected'; ?>><?php echo $e['Nombre_Estado']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover table-bordered mb-0 align-middle">
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
                if ($resultado->num_rows > 0) {
                    while($fila = $resultado->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($fila["Codigo_Postulacion"]) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($fila["Nombre_Iniciativa"] ?? 'Sin nombre') . "</td>";
                        echo "<td>" . htmlspecialchars($fila["Nombre_Empresa"]) . "</td>";
                        echo "<td>" . htmlspecialchars($fila["Nombre_Sede"]) . "</td>";
                        echo "<td>" . htmlspecialchars($fila["Region_Ejecucion"]) . "</td>";
                        echo "<td class='text-success fw-bold'>$" . number_format($fila["Presupuesto_Total"], 0, ',', '.') . "</td>";
                        $estado = htmlspecialchars($fila["Nombre_Estado"]);
                        $color_badge = 'bg-secondary';
                        if ($estado == 'En Revisión') $color_badge = 'bg-info text-dark';
                        if ($estado == 'Aprobada') $color_badge = 'bg-success';
                        if ($estado == 'Rechazada') $color_badge = 'bg-danger';
                        if ($estado == 'Cerrada') $color_badge = 'bg-dark';

                        echo "<td><span class='badge $color_badge'>" . $estado . "</span></td>";
                        echo "<td>";
                        echo "<a href='detalle_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-info text-white me-1'>Ver</a>";
                        
                        if($_SESSION['rol'] == 1){
                            if ($estado == 'Borrador') {
                                echo "<a href='enviar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-success me-1 text-white' onclick='return confirm(\"¿Deseas enviar esta postulación a revisión definitiva?\")'>Enviar</a>";
                            }
                            echo "<a href='editar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-warning me-1'>Editar</a>";
                            echo "<a href='eliminar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Eliminar postulación completa?\")'>Borrar</a>";
                        } else if ($_SESSION['rol'] == 2){
                            // Si el correo del evaluador asignado coincide con el usuario logueado
                            if ($_SESSION['usuario'] == $fila['Correo_Evaluador']) {
                                echo "<a href='evaluar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-dark'>Evaluar</a>";
                            } else {
                                echo "<span class='badge bg-light text-muted border'>No asignada a ti</span>";
                            }
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center text-muted p-4'>No se encontraron postulaciones con los filtros seleccionados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>