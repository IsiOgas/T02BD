-- 1. Listado General de Postulaciones
SELECT
    p.Numero_Postulacion AS 'Postulación N°',
    p.Fecha_Postulacion AS 'Fecha',
    ti.Nombre_Tipo_Iniciativa AS 'Tipo de Iniciativa',
    s.Nombre_Sede AS 'Sede',
    r1.Nombre_Region AS 'Región de ejecución',
    r2.Nombre_Region AS 'Región de Impacto',
    e.Nombre_Empresa AS 'Empresa', 
    p.Presupuesto_Total AS 'Presupuesto total'

FROM Postulacion p
JOIN Tipo_Iniciativa ti 
    ON p.ID_Tipo_Iniciativa = ti.ID_tipo_iniciativa
JOIN Sede s 
    ON p.ID_Sede = s.ID_Sede
JOIN Region r1 
    ON p.ID_Region_Ejecucion = r1.ID_Region
JOIN Region r2 
    ON p.ID_Region_Impacto=r2.ID_Region
JOIN Entidad_Empresa e 
    ON p.Rut_Empresa= e.Rut_Empresa;


-- 2 Postulacion por Region
SELECT
    e.Nombre_Empresa AS 'Empresa',
    s.Nombre_Sede AS 'Sede',
    p.Presupuesto_Total 'Presupuesto'

FROM Postulacion p
JOIN Entidad_Empresa e 
    ON p.Rut_Empresa = e.Rut_Empresa
JOIN Sede s 
    ON p.ID_Sede = s.ID_Sede
JOIN Region r 
    ON p.ID_Region_Ejecucion = r.ID_Region
WHERE r.Nombre_Region = 'Valparaíso';


-- 3 Conteo por tipo Iniciativa 
SELECT
    ti.Nombre_Tipo_Iniciativa AS 'Tipo de Iniciativa', 
    COUNT( p.Numero_Postulacion) AS 'Cantidad de Postulaciones'

FROM Postulacion p
JOIN Tipo_Iniciativa ti 
    ON p.ID_Tipo_Iniciativa = ti.ID_tipo_iniciativa
GROUP BY ti.Nombre_Tipo_Iniciativa;


-- 4 Equipo de trabajo de una postulación
SELECT
    ie.RUT_Integrante AS 'RUT',
    ie.Nombre_Integrante AS 'Nombre',
    tp.Nombre_Tipo_Persona AS 'Tipo (Profesor/Estudiante)',
    s.Nombre_Sede AS 'Sede',
    ie.Mail_Integrante AS 'Email',
    ie.Rol_Cumple_Integrante AS 'ROL'

FROM Postulacion_Integrante pi
JOIN Integrante_Equipo ie 
    ON pi.ID_integrante = ie.ID_integrante
JOIN Tipo_Persona tp 
    ON ie.ID_Tipo_Persona = tp.ID_Tipo_Persona
JOIN Sede s 
    ON ie.ID_Sede = s.ID_Sede
WHERE pi.Numero_Postulacion = 1;


-- 5 Empresas con postulaciones y convenios
SELECT
    e.Nombre_Empresa AS 'Nombre',
    ta.Nombre_Tamanio AS 'Tamanio',
    IF(e.Convenio_USM = 1, 'Sí', 'No') AS 'Convenio(si/no)',
    COUNT(p.Rut_Empresa) AS 'Cantidad de postulaciones asociadas'
FROM Entidad_Empresa e
JOIN Tamanio_Empresa ta
    ON e.ID_Tamanio = ta.ID_Tamanio
JOIN Postulacion p
    ON p.Rut_Empresa = e.Rut_Empresa
GROUP BY e.Rut_Empresa, e.Nombre_Empresa, ta.Nombre_Tamanio, e.Convenio_USM
ORDER BY COUNT(p.Rut_Empresa) DESC;

-- 6 Postulaciones con presupuesto sobre el promedio
SELECT
    p.Numero_Postulacion AS 'Numero postulacion',
    e.Nombre_Empresa AS 'Nombre empresa',
    p.Presupuesto_Total AS 'Presupuesto total'
FROM Postulacion p
JOIN Entidad_Empresa e
    ON p.Rut_Empresa = e.Rut_Empresa
WHERE p.Presupuesto_Total> (SELECT AVG(Presupuesto_Total) FROM Postulacion)
ORDER BY p.Presupuesto_Total DESC;

-- 7 Cantidad de integrantes por postulacion y tipo
SELECT 
    pi.Numero_Postulacion AS Postulacion, 
    tp.Nombre_Tipo_Persona AS Tipo,
    COUNT(ie.ID_integrante) AS Cantidad
FROM Postulacion_Integrante pi
JOIN Integrante_Equipo ie 
    ON pi.ID_integrante = ie.ID_integrante
JOIN Tipo_Persona tp 
    ON ie.ID_Tipo_Persona = tp.ID_Tipo_Persona
GROUP BY pi.Numero_Postulacion, tp.Nombre_Tipo_Persona;

-- 8 Postulaciones que no cumplen el minimo de equipo
SELECT 
    pi.Numero_Postulacion AS Postulacion, 
    SUM(IF(tp.Nombre_Tipo_Persona = 'Profesor', 1, 0)) AS Profesores,
    SUM(IF(tp.Nombre_Tipo_Persona = 'Estudiante', 1, 0)) AS Estudiantes
FROM Postulacion_Integrante pi
JOIN Integrante_Equipo ie 
    ON pi.ID_integrante = ie.ID_integrante
JOIN Tipo_Persona tp 
    ON ie.ID_Tipo_Persona = tp.ID_Tipo_Persona
GROUP BY pi.Numero_Postulacion
HAVING Profesores< 3 OR Estudiantes< 5;

-- 9 Empresas sin postulaciones registradas
SELECT
    e.Nombre_Empresa AS 'Nombre empresa',
    e.Rut_Empresa AS 'Rut_Empresa',
    te.Nombre_Tamanio AS Tamanio
FROM Entidad_Empresa e
JOIN Tamanio_Empresa te 
    ON te.ID_Tamanio= e.ID_Tamanio
LEFT JOIN Postulacion p
    ON p.Rut_Empresa = e.Rut_Empresa
WHERE p.Rut_Empresa IS NULL;

-- 10 Postulaciones que exceden el plazo maximo
SELECT
    p.Numero_Postulacion AS Postulacion, 
    p.Codigo_Postulacion AS 'Codigo Postulacion',
    COUNT(ec.ID_Etapa) AS 'Total de etapas',
    SUM(ec.Plazos) AS 'Total semanas'
FROM Postulacion p
JOIN Etapa_Cronograma ec
    ON ec.ID_Postulacion= p.Numero_Postulacion
GROUP BY p.Numero_Postulacion
HAVING SUM(ec.Plazos) > 36
ORDER BY SUM(ec.Plazos) DESC;