<?php
session_start();

// Validamos que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Según el PDF, el Rol 1 (Postulante) es quien crea las postulaciones
if ($_SESSION['rol'] != 1) {
    echo "<script>alert('Solo los Responsables Académicos (Rol 1) pueden crear postulaciones.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';

// Traemos los catálogos de la base de datos para llenar los selectores (dropdowns)
$empresas = $conexion->query("SELECT Rut_Empresa, Nombre_Empresa FROM Entidad_Empresa");
$sedes = $conexion->query("SELECT ID_Sede, Nombre_Sede FROM Sede");
$regiones = $conexion->query("SELECT ID_Region, Nombre_Region FROM Region");
$tipos_iniciativa = $conexion->query("SELECT ID_tipo_iniciativa, Nombre_Tipo_Iniciativa FROM Tipo_Iniciativa");
$integrantes_db = $conexion->query("SELECT ID_integrante, Nombre_Integrante, RUT_Integrante FROM Integrante_Equipo ORDER BY Nombre_Integrante ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Postulación - CT-USM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="principal.php"> Volver al Listado</a>
    <span class="navbar-text text-white">
        Usuario: <?php echo $_SESSION['usuario']; ?>
    </span>
  </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Formulario de Nueva Postulación CT-USM</h4>
        </div>
        <div class="card-body">
            
            <form action="guardar_postulacion.php" method="POST">
                
                <h5 class="text-secondary border-bottom pb-2 mb-3">1. Antecedentes de la Postulación</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Código de Postulación (*)</label>
                        <input type="text" class="form-control" name="codigo" placeholder="Ej: A15" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Postulación (*)</label>
                        <input type="date" class="form-control" name="fecha" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Presupuesto Total Solicitado ($) (*)</label>
                        <input type="number" class="form-control" name="presupuesto" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sede / Campus (*)</label>
                        <select class="form-select" name="sede" required>
                            <option value="">Seleccione...</option>
                            <?php while($s = $sedes->fetch_assoc()) { ?>
                                <option value="<?php echo $s['ID_Sede']; ?>"><?php echo $s['Nombre_Sede']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Región de Ejecución (*)</label>
                        <select class="form-select" name="region_ejecucion" required>
                            <option value="">Seleccione...</option>
                            <?php 
                            // Como usamos el mismo resultado de regiones dos veces, lo guardamos en un array temporal
                            $regiones_array = [];
                            while($r = $regiones->fetch_assoc()) { 
                                $regiones_array[] = $r;
                                echo "<option value='".$r['ID_Region']."'>".$r['Nombre_Region']."</option>";
                            } 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Región de Impacto (*)</label>
                        <select class="form-select" name="region_impacto" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($regiones_array as $r) { ?>
                                <option value="<?php echo $r['ID_Region']; ?>"><?php echo $r['Nombre_Region']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mt-4 mb-3">2. Antecedentes Entidad Externa</h5>
                <div class="mb-3">
                    <label class="form-label">Empresa Asociada (*)</label>
                    <select class="form-select" name="empresa" required>
                        <option value="">Seleccione la empresa...</option>
                        <?php while($e = $empresas->fetch_assoc()) { ?>
                            <option value="<?php echo $e['Rut_Empresa']; ?>"><?php echo $e['Nombre_Empresa']; ?> (RUT: <?php echo $e['Rut_Empresa']; ?>)</option>
                        <?php } ?>
                    </select>
                    <div class="form-text">Nota: Para este paso la empresa ya debe existir en los registros.</div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mt-4 mb-3">3. Tipo de Iniciativa</h5>
                <div class="mb-3">
                    <label class="form-label">Estado de la iniciativa (*)</label>
                    <select class="form-select" name="tipo_iniciativa" required>
                        <option value="">Seleccione...</option>
                        <?php while($ti = $tipos_iniciativa->fetch_assoc()) { ?>
                            <option value="<?php echo $ti['ID_tipo_iniciativa']; ?>"><?php echo $ti['Nombre_Tipo_Iniciativa']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <h5 class="text-secondary border-bottom pb-2 mt-4 mb-3">4. Antecedentes de la Iniciativa</h5>
                <div class="mb-3">
                    <label class="form-label">Nombre de la Iniciativa (*)</label>
                    <input type="text" class="form-control" name="nombre_iniciativa" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Objetivo de la Iniciativa (*)</label>
                    <textarea class="form-control" name="objetivo_iniciativa" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción de las Soluciones (*)</label>
                    <textarea class="form-control" name="descripcion_soluciones" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Resultados Esperados (*)</label>
                    <textarea class="form-control" name="resultados_esperados" rows="2" required></textarea>
                </div>
                <h5 class="text-secondary border-bottom pb-2 mt-4 mb-3">5. Equipo de Trabajo</h5>
                <div class="mb-3">
                    <label class="form-label">Seleccione a los integrantes (Mantenga presionado Ctrl / Cmd para seleccionar varios) (*)</label>
                    <select class="form-select" name="equipo[]" multiple required size="5">
                        <?php while($int = $integrantes_db->fetch_assoc()) { ?>
                            <option value="<?php echo $int['ID_integrante']; ?>">
                                <?php echo $int['Nombre_Integrante']; ?> (RUT: <?php echo $int['RUT_Integrante']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <h5 class="text-secondary border-bottom pb-2 mt-4 mb-3">6. Cronograma (Primeras 2 Etapas)</h5>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Etapa 1 (*)</label>
                        <input type="text" class="form-control" name="etapa[]" placeholder="Ej: Planificación" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Entregable 1 (*)</label>
                        <input type="text" class="form-control" name="entregable[]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Plazo (Semanas) (*)</label>
                        <input type="number" class="form-control" name="plazos[]" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Etapa 2</label>
                        <input type="text" class="form-control" name="etapa[]" placeholder="Opcional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Entregable 2</label>
                        <input type="text" class="form-control" name="entregable[]">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Plazo (Semanas)</label>
                        <input type="number" class="form-control" name="plazos[]">
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100 mt-4">Guardar y Continuar</button>

            </form>
        </div>
    </div>
</div>

</body>
</html>