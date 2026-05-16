<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';

// Catálogos para el filtro avanzado
$sedes = $conexion->query("SELECT Nombre_Sede FROM Sede");
$estados = $conexion->query("SELECT Nombre_Estado FROM Estado_Postulacion");

// --- LÓGICA DE BÚSQUEDA AVANZADA ---
$sql = "SELECT * FROM Vista_Postulaciones_Principal WHERE 1=1";
$filtros = [];
$tipos_datos = "";

// Filtro por texto libre
if (!empty($_GET['buscar'])) {
    $sql .= " AND (Codigo_Postulacion LIKE ? OR Nombre_Iniciativa LIKE ? OR Nombre_Empresa LIKE ?)";
    $termino = "%" . $_GET['buscar'] . "%";
    array_push($filtros, $termino, $termino, $termino);
    $tipos_datos .= "sss";
}

// Filtro por Sede
if (!empty($_GET['sede'])) {
    $sql .= " AND Nombre_Sede = ?";
    $filtros[] = $_GET['sede'];
    $tipos_datos .= "s";
}

// Filtro por Estado
if (!empty($_GET['estado'])) {
    $sql .= " AND Nombre_Estado = ?";
    $filtros[] = $_GET['estado'];
    $tipos_datos .= "s";
}

$stmt = $conexion->prepare($sql);

// Si hay filtros activos, los vinculamos (bind_param) dinámicamente
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="principal.php">CT-USM</a>
    <div class="d-flex align-items-center">
        <span class="navbar-text me-3 text-white">
            Usuario: <?php echo $_SESSION['usuario']; ?> (Rol: <?php echo $_SESSION['rol']; ?>)
        </span>
        <a href="gestionar_equipo.php" class="btn btn-info btn-sm me-2 text-white fw-bold">Directorio Equipo</a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Panel de Postulaciones</h2>
        <?php if ($_SESSION['rol'] == 1): ?>
            <a href="crear_postulacion.php" class="btn btn-success btn-lg shadow-sm">➕ Crear Nueva Postulación</a>
        <?php endif; ?>
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
                    <button type="submit" class="btn btn-primary w-100">🔍 Filtrar</button>
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
                        echo "<td><span class='badge bg-info text-dark border border-info'>" . htmlspecialchars($fila["Nombre_Estado"]) . "</span></td>";
                        
                        echo "<td>";
                        echo "<a href='detalle_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-info text-white me-1'>Ver</a>";
                        
                        // LÓGICA DE VISTAS POR ROLES
                        if ($_SESSION['rol'] == 1) {
                            // Opciones del Postulante
                            echo "<a href='editar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-warning me-1'>Editar</a>";
                            echo "<a href='eliminar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Eliminar postulación completa?\")'>Borrar</a>";
                        } else if ($_SESSION['rol'] == 2 || $_SESSION['rol'] == 3) {
                            // Opciones del Evaluador / Admin
                            echo "<a href='evaluar_postulacion.php?id=" . $fila["Numero_Postulacion"] . "' class='btn btn-sm btn-dark'>Evaluar</a>";
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