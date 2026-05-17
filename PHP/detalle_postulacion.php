<?php
session_start();

//validamos que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "<script>alert('ID de postulación no válido.'); window.location.href='principal.php';</script>";
    exit();
}

require 'conexion.php';
$id_postulacion = intval($_GET['id']);

$sql_principal = "SELECT p.*, e.Nombre_Empresa, e.Representante_Empresa, e.Mail_Representante, e.Convenio_USM, e.Telefono_Representante,
                         s.Nombre_Sede, r1.Nombre_Region AS Region_Ejecucion, r2.Nombre_Region AS Region_Impacto,
                         ti.Nombre_Tipo_Iniciativa, est.Nombre_Estado,
                         i.Nombre_Iniciativa, i.Objetivo_Iniciativa, i.Descripcion_Soluciones, i.Resultados_Esperados, i.Documentos
                  FROM Postulacion p
                  JOIN Entidad_Empresa e ON p.Rut_Empresa = e.Rut_Empresa
                  JOIN Sede s ON p.ID_Sede = s.ID_Sede
                  JOIN Region r1 ON p.ID_Region_Ejecucion = r1.ID_Region
                  JOIN Region r2 ON p.ID_Region_Impacto = r2.ID_Region
                  JOIN Tipo_Iniciativa ti ON p.ID_Tipo_Iniciativa = ti.ID_tipo_iniciativa
                  JOIN Estado_Postulacion est ON p.ID_Estado = est.ID_Estado
                  LEFT JOIN Iniciativa i ON p.Numero_Postulacion = i.ID_Postulacion
                  WHERE p.Numero_Postulacion = ?";

$stmt_p = $conexion->prepare($sql_principal);
$stmt_p->bind_param("i", $id_postulacion);
$stmt_p->execute();
$resultado_p = $stmt_p->get_result();

if ($resultado_p->num_rows == 0) {
    echo "<script>alert('La postulación buscada no existe.'); window.location.href='principal.php';</script>";
    exit();
}

$postulacion = $resultado_p->fetch_assoc();

//consulta del equipo de trabajo
$sql_equipo = "SELECT ie.Nombre_Integrante, ie.RUT_Integrante, ie.Mail_Integrante, pi.Rol_Cumple_Integrante, tp.Nombre_Tipo_Persona
               FROM Postulacion_Integrante pi
               JOIN Integrante_Equipo ie ON pi.ID_integrante = ie.ID_integrante
               JOIN Tipo_Persona tp ON ie.ID_Tipo_Persona = tp.ID_Tipo_Persona
               WHERE pi.Numero_Postulacion = ?";
$stmt_eq = $conexion->prepare($sql_equipo);
$stmt_eq->bind_param("i", $id_postulacion);
$stmt_eq->execute();
$equipo = $stmt_eq->get_result();

//consulta del cronograma
$sql_cronograma = "SELECT Etapa, Entregable, Plazos FROM Etapa_Cronograma WHERE ID_Postulacion = ?";
$stmt_cr = $conexion->prepare($sql_cronograma);
$stmt_cr->bind_param("i", $id_postulacion);
$stmt_cr->execute();
$cronograma = $stmt_cr->get_result();

//usamos la funcion creada sql 
$sql_funcion = "SELECT TotalSemanasPostulacion(?) AS Total";
$stmt_func = $conexion->prepare($sql_funcion);
$stmt_func->bind_param("i", $id_postulacion);
$stmt_func->execute();
$resultado_func = $stmt_func->get_result()->fetch_assoc();
$total_semanas_bd = $resultado_func['Total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Postulación N° <?php echo $postulacion['Numero_Postulacion']; ?> - CT-USM</title>
    <link href="https://bootswatch.com/5/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="principal.php">← Volver al Listado</a>
    <span class="navbar-text text-white">
        Usuario: <?php echo $_SESSION['usuario']; ?>
    </span>
  </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detalle de Postulación <small class="text-muted">#<?php echo $postulacion['Numero_Postulacion']; ?></small></h2>
        <span class="badge bg-info fs-5">Estado: <?php echo $postulacion['Nombre_Estado']; ?></span>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">1. Antecedentes de la Iniciativa</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary"><?php echo htmlspecialchars($postulacion['Nombre_Iniciativa'] ?? 'Sin Nombre'); ?></h4>
                    <p class="text-muted"><strong>Código Único:</strong> <?php echo $postulacion['Codigo_Postulacion']; ?> | <strong>Tipo:</strong> <?php echo $postulacion['Nombre_Tipo_Iniciativa']; ?></p>
                    <hr>
                    <p><strong>Objetivo de la Iniciativa:</strong><br><?php echo nl2br(htmlspecialchars($postulacion['Objetivo_Iniciativa'] ?? 'No especificado')); ?></p>
                    <p><strong>Descripción de Soluciones:</strong><br><?php echo nl2br(htmlspecialchars($postulacion['Descripcion_Soluciones'] ?? 'No especificado')); ?></p>
                    <p><strong>Resultados Esperados:</strong><br><?php echo nl2br(htmlspecialchars($postulacion['Resultados_Esperados'] ?? 'No especificado')); ?></p>
                    <?php if (!empty($postulacion['Documentos'])): ?>
                        <p><strong>Documento Adjunto:</strong> <span class="text-success" <?php echo htmlspecialchars($postulacion['Documentos']); ?></span></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">2. Equipo de Trabajo</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nombre</th>
                                    <th>RUT</th>
                                    <th>Tipo</th>
                                    <th>Correo</th>
                                    <th>Rol Asignado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($equipo->num_rows > 0): ?>
                                    <?php while ($eq = $equipo->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($eq['Nombre_Integrante']); ?></td>
                                            <td><?php echo htmlspecialchars($eq['RUT_Integrante']); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo $eq['Nombre_Tipo_Persona']; ?></span></td>
                                            <td><?php echo htmlspecialchars($eq['Mail_Integrante']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($eq['Rol_Cumple_Integrante'] ?? 'Integrante'); ?></strong></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted p-3">No hay integrantes registrados en este equipo.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">3. Cronograma de Actividades</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Etapa</th>
                                    <th>Entregable Comprometido</th>
                                    <th class="text-center" style="width: 150px;">Plazo (Semanas)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($cronograma->num_rows > 0): ?>
                                    <?php while ($cr = $cronograma->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cr['Etapa']); ?></td>
                                            <td><?php echo htmlspecialchars($cr['Entregable']); ?></td>
                                            <td class="text-center"><?php echo $cr['Plazos']; ?> semanas</td>
                                        </tr>
                                    <?php endwhile; ?>
                                    
                                    <tr class="table-warning fw-bold">
                                        <td colspan="2" class="text-end">Duración Total del Proyecto:</td>
                                        <td class="text-center text-danger"><?php echo $total_semanas_bd; ?> semanas</td>
                                    </tr>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted p-3">No hay etapas registradas en el cronograma.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body bg-white border-top border-4 border-primary rounded-top">
                    <h5 class="card-title text-secondary border-bottom pb-2">Resumen Técnico</h5>
                    <ul class="list-unstyled lh-lg mb-0">
                        <li><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($postulacion['Fecha_Postulacion'])); ?></li>
                        
                        <li class="text-primary"><strong>Responsable 1:</strong> <?php echo htmlspecialchars($postulacion['Nombre_Responsable_1']); ?></li>
                        <li class="text-primary"><strong>Responsable 2:</strong> <?php echo htmlspecialchars($postulacion['Nombre_Responsable_2']); ?></li>
                        
                        <li><strong>Sede/Campus:</strong> <?php echo htmlspecialchars($postulacion['Nombre_Sede']); ?></li>
                        <li><strong>Región Ejecución:</strong> <?php echo htmlspecialchars($postulacion['Region_Ejecucion']); ?></li>
                        <li><strong>Región Impacto:</strong> <?php echo htmlspecialchars($postulacion['Region_Impacto']); ?></li>
                        <li><strong>Presupuesto Solicitado:</strong> <span class="text-success fw-bold">$<?php echo number_format($postulacion['Presupuesto_Total'], 0, ',', '.'); ?></span></li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body bg-white border-top border-4 border-success rounded-top">
                    <h5 class="card-title text-secondary border-bottom pb-2">Empresa Asociada</h5>
                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($postulacion['Nombre_Empresa']); ?></h6>
                    <p class="text-muted small mb-3">RUT: <?php echo $postulacion['Rut_Empresa']; ?></p>
                    
                    <ul class="list-unstyled lh-lg small mb-0">
                        <li><strong>Representante:</strong> <?php echo htmlspecialchars($postulacion['Representante_Empresa']); ?></li>
                        <li><strong>Contacto:</strong> <?php echo htmlspecialchars($postulacion['Telefono_Representante']); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($postulacion['Mail_Representante']); ?></li>
                        <li><strong>Convenio USM:</strong> 
                            <?php if ($postulacion['Convenio_USM'] == 1): ?>
                                <span class="badge bg-success">Sí posee convenio</span>
                            <?php else: ?>
                                <span class="badge bg-danger">No posee convenio</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>