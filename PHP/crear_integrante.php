<?php
session_start();

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1){
    header("Location: index.php");
    exit();
}

require 'conexion.php';

$sedes = $conexion->query("SELECT ID_Sede, Nombre_Sede FROM Sede");
$tipos = $conexion->query("SELECT ID_Tipo_Persona, Nombre_Tipo_Persona FROM Tipo_Persona");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Integrante - CT-USM</title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Registrar Nuevo Integrante</h4>
        </div>
        <div class="card-body">
            <form action="guardar_integrante.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre Completo (*)</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">RUT (* Sin puntos, con guion)</label>
                        <input type="text" class="form-control" name="rut" placeholder="Ej: 12345678-9" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo Institucional (*)</label>
                        <input type="email" class="form-control" name="correo" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Departamento o Área (*)</label>
                    <input type="text" class="form-control" name="departamento" placeholder="Ej: Informática, Mecánica..." required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Persona (*)</label>
                        <select class="form-select" name="tipo_persona" required>
                            <option value="">Seleccione...</option>
                            <?php while($t = $tipos->fetch_assoc()) { ?>
                                <option value="<?php echo $t['ID_Tipo_Persona']; ?>"><?php echo $t['Nombre_Tipo_Persona']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sede / Campus (*)</label>
                        <select class="form-select" name="sede" required>
                            <option value="">Seleccione...</option>
                            <?php while($s = $sedes->fetch_assoc()) { ?>
                                <option value="<?php echo $s['ID_Sede']; ?>"><?php echo $s['Nombre_Sede']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success btn-lg flex-grow-1">Guardar Integrante</button>
                    <a href="gestionar_equipo.php" class="btn btn-secondary btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>