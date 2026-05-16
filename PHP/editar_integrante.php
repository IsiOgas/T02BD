<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID de integrante no válido.'); window.location.href='gestionar_equipo.php';</script>";
    exit();
}

require 'conexion.php';
$id_integrante = intval($_GET['id']);

//consultamos los datos actuales del integrante
$sql = "SELECT * FROM Integrante_Equipo WHERE ID_integrante = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_integrante);
$stmt->execute();
$integrante = $stmt->get_result()->fetch_assoc();

if (!$integrante) {
    echo "<script>alert('El integrante no existe.'); window.location.href='gestionar_equipo.php';</script>";
    exit();
}

//catálogos para llenar los selects
$sedes = $conexion->query("SELECT ID_Sede, Nombre_Sede FROM Sede");
$tipos = $conexion->query("SELECT ID_Tipo_Persona, Nombre_Tipo_Persona FROM Tipo_Persona");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Integrante - CT-USM</title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Modificar Datos del Integrante (ID: <?php echo $id_integrante; ?>)</h4>
        </div>
        <div class="card-body">
            <form action="actualizar_integrante.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id_integrante; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre Completo (*)</label>
                        <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($integrante['Nombre_Integrante']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">RUT (* Sin puntos, con guion)</label>
                        <input type="text" class="form-control" name="rut" value="<?php echo htmlspecialchars($integrante['RUT_Integrante']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo Institucional (*)</label>
                        <input type="email" class="form-control" name="correo" value="<?php echo htmlspecialchars($integrante['Mail_Integrante']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" value="<?php echo htmlspecialchars($integrante['Telefono_Integrante']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Departamento o Área (*)</label>
                    <input type="text" class="form-control" name="departamento" value="<?php echo htmlspecialchars($integrante['Departamento_Integrante']); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Persona (*)</label>
                        <select class="form-select" name="tipo_persona" required>
                            <?php while($t = $tipos->fetch_assoc()) { ?>
                                <option value="<?php echo $t['ID_Tipo_Persona']; ?>" <?php if($t['ID_Tipo_Persona'] == $integrante['ID_Tipo_Persona']) echo 'selected'; ?>>
                                    <?php echo $t['Nombre_Tipo_Persona']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sede / Campus (*)</label>
                        <select class="form-select" name="sede" required>
                            <?php while($s = $sedes->fetch_assoc()) { ?>
                                <option value="<?php echo $s['ID_Sede']; ?>" <?php if($s['ID_Sede'] == $integrante['ID_Sede']) echo 'selected'; ?>>
                                    <?php echo $s['Nombre_Sede']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-warning btn-lg flex-grow-1 fw-bold">Guardar Cambios</button>
                    <a href="gestionar_equipo.php" class="btn btn-secondary btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>