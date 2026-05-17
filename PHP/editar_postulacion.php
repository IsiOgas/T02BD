<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';
$id_postulacion = intval($_GET['id']);

//obtenemos los datos actuales de la postulación e iniciativa
$sql = "SELECT p.*, i.Nombre_Iniciativa, i.Objetivo_Iniciativa, i.Descripcion_Soluciones, i.Resultados_Esperados, est.Nombre_Estado 
        FROM Postulacion p 
        LEFT JOIN Iniciativa i ON p.Numero_Postulacion = i.ID_Postulacion 
        JOIN Estado_Postulacion est ON p.ID_Estado = est.ID_Estado
        WHERE p.Numero_Postulacion = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_postulacion);
$stmt->execute();
$postulacion = $stmt->get_result()->fetch_assoc();

if (!$postulacion) {
    echo "<script>alert('Postulación no encontrada.'); window.location.href='principal.php';</script>";
    exit();
}

if ($postulacion['Correo_Responsable'] != $_SESSION['usuario']) {
    echo "<script>alert('Acceso denegado: No eres el responsable de este proyecto.'); window.location.href='principal.php';</script>";
    exit();
}

$ya_enviado = ($postulacion['Nombre_Estado'] != 'Borrador');

$sedes = $conexion->query("SELECT ID_Sede, Nombre_Sede FROM Sede");
$regiones = $conexion->query("SELECT ID_Region, Nombre_Region FROM Region");
$tipos_iniciativa = $conexion->query("SELECT ID_tipo_iniciativa, Nombre_Tipo_Iniciativa FROM Tipo_Iniciativa");
$regiones_array = $regiones->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Postulación #<?php echo $id_postulacion; ?></title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Modificar Postulación CT-USM (ID: <?php echo $id_postulacion; ?>)</h4>
        </div>
        <div class="card-body">
            <form action="actualizar_postulacion.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id_postulacion; ?>">

                <?php if($ya_enviado): ?>
                    <div class="alert alert-info">
                        <strong>Aviso:</strong> Esta postulación ya se encuentra en estado <b><?php echo $postulacion['Nombre_Estado']; ?></b>. Por regla del CT-USM, los campos críticos (Código y Presupuesto) han sido bloqueados.
                    </div>
                <?php endif; ?>

                <h5 class="text-secondary border-bottom pb-2 mb-3">1. Antecedentes Generales</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Código de Postulación</label>
                        <input type="text" class="form-control <?php echo $ya_enviado ? 'bg-light' : ''; ?>" name="codigo" value="<?php echo $postulacion['Codigo_Postulacion']; ?>" <?php echo $ya_enviado ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Presupuesto Solicitado ($)</label>
                        <input type="number" class="form-control" name="presupuesto" value="<?php echo intval($postulacion['Presupuesto_Total']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre Responsable 1: Jefe(a) de Carreras</label>
                        <input type="text" class="form-control" name="responsable1" value="<?php echo htmlspecialchars($postulacion['Nombre_Responsable_1']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre Responsable 2: Coordinador Proyecto</label>
                        <input type="text" class="form-control" name="responsable2" value="<?php echo htmlspecialchars($postulacion['Nombre_Responsable_2']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sede / Campus</label>
                        <select class="form-select" name="sede" required>
                            <?php foreach($sedes as $s) { ?>
                                <option value="<?php echo $s['ID_Sede']; ?>" <?php if($s['ID_Sede'] == $postulacion['ID_Sede']) echo 'selected'; ?>><?php echo $s['Nombre_Sede']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Región Ejecución</label>
                        <select class="form-select" name="region_ejecucion" required>
                            <?php foreach($regiones_array as $r) { ?>
                                <option value="<?php echo $r['ID_Region']; ?>" <?php if($r['ID_Region'] == $postulacion['ID_Region_Ejecucion']) echo 'selected'; ?>><?php echo $r['Nombre_Region']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Región Impacto</label>
                        <select class="form-select" name="region_impacto" required>
                            <?php foreach($regiones_array as $r) { ?>
                                <option value="<?php echo $r['ID_Region']; ?>" <?php if($r['ID_Region'] == $postulacion['ID_Region_Impacto']) echo 'selected'; ?>><?php echo $r['Nombre_Region']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mt-4 mb-3">2. Datos de la Iniciativa</h5>
                <div class="mb-3">
                    <label class="form-label">Nombre de la Iniciativa</label>
                    <input type="text" class="form-control" name="nombre_iniciativa" value="<?php echo htmlspecialchars($postulacion['Nombre_Iniciativa']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Objetivo</label>
                    <textarea class="form-control" name="objetivo_iniciativa" rows="3" required><?php echo htmlspecialchars($postulacion['Objetivo_Iniciativa']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción de Soluciones</label>
                    <textarea class="form-control" name="descripcion_soluciones" rows="3" required><?php echo htmlspecialchars($postulacion['Descripcion_Soluciones']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Resultados Esperados</label>
                    <textarea class="form-control" name="resultados_esperados" rows="3" required><?php echo htmlspecialchars($postulacion['Resultados_Esperados']); ?></textarea>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-warning btn-lg flex-grow-1">Guardar Cambios</button>
                    <a href="principal.php" class="btn btn-secondary btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>