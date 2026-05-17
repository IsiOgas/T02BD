DROP DATABASE IF EXISTS ct_usm_postulaciones;
CREATE DATABASE ct_usm_postulaciones;
USE ct_usm_postulaciones;

-- CREACIÓN DE LOS CATÁLOGOS
CREATE TABLE Sede(
    ID_Sede INT PRIMARY KEY,
    Nombre_Sede VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Region(
    ID_Region INT PRIMARY KEY,
    Nombre_Region VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Tamanio_Empresa(
    ID_Tamanio INT PRIMARY KEY,
    Nombre_Tamanio VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Tipo_Iniciativa(
    ID_tipo_iniciativa INT PRIMARY KEY,
    Nombre_Tipo_Iniciativa VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Tipo_Persona(
    ID_Tipo_Persona INT PRIMARY KEY,
    Nombre_Tipo_Persona VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Estado_Postulacion(
    ID_Estado INT PRIMARY KEY,
    Nombre_Estado VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- DATOS A LOS CATÁLOGOS
INSERT INTO Sede(ID_Sede, Nombre_Sede) VALUES
(1, 'Casa Central Valparaíso'), 
(2, 'Campus Santiago San Joaquín'),
(3, 'Campus Santiago Vitacura'), 
(4, 'Sede Viña del Mar'), 
(5, 'Sede Concepción');

INSERT INTO Region(ID_Region, Nombre_Region) VALUES
(1, 'Arica y Parinacota'), 
(2, 'Tarapacá'), 
(3, 'Antofagasta'), 
(4, 'Atacama'),
(5, 'Coquimbo'), 
(6, 'Valparaíso'), 
(7, 'Metropolitana'), 
(8, 'O''Higgins'),
(9, 'Maule'), 
(10, 'Ñuble'), 
(11, 'Biobío'), 
(12, 'La Araucanía'),
(13, 'Los Ríos'), 
(14, 'Los Lagos'), 
(15, 'Aysén'), 
(16, 'Magallanes');

INSERT INTO Tamanio_Empresa(ID_Tamanio, Nombre_Tamanio) VALUES
(1, 'MicroEmpresa'), 
(2, 'Mediana'), 
(3, 'Grande');

INSERT INTO Tipo_Iniciativa(Id_Tipo_Iniciativa, Nombre_Tipo_Iniciativa) VALUES
(1, 'Nueva'), 
(2, 'Existente');

INSERT INTO Tipo_Persona(ID_Tipo_Persona, Nombre_Tipo_Persona) VALUES
(1, 'Profesor'), 
(2, 'Estudiante');

INSERT INTO Estado_Postulacion(ID_Estado, Nombre_Estado) VALUES
(1, 'En Revisión'), 
(2, 'Aprobada'), 
(3, 'Rechazada'), 
(4, 'Cerrada');
INSERT INTO Estado_Postulacion (ID_Estado, Nombre_Estado) VALUES (5, 'Borrador');

CREATE TABLE Usuarios (
    ID_Usuario INT PRIMARY KEY AUTO_INCREMENT,
    Correo VARCHAR(100) NOT NULL UNIQUE,
    Contrasena VARCHAR(255) NOT NULL,
    Rol INT NOT NULL
) ENGINE=InnoDB;

-- CREACIÓN DE TABLAS PRINCIPALES (CORREGIDAS A 3FN)
CREATE TABLE Entidad_Empresa(
    Rut_Empresa VARCHAR(10) PRIMARY KEY,
    Nombre_Empresa VARCHAR(100) NOT NULL,
    Representante_Empresa VARCHAR(100) NOT NULL,
    Mail_Representante VARCHAR(100) NOT NULL,
    Convenio_USM BOOLEAN NOT NULL,
    Telefono_Representante VARCHAR(20) NOT NULL,
    ID_Tamanio INT NOT NULL,
    FOREIGN KEY (ID_Tamanio) REFERENCES Tamanio_Empresa(ID_Tamanio)
) ENGINE=InnoDB;

CREATE TABLE Postulacion(
    Numero_Postulacion INT PRIMARY KEY,
    Fecha_Postulacion DATE NOT NULL,
    Codigo_Postulacion VARCHAR(100) NOT NULL UNIQUE,
    Presupuesto_Total DECIMAL(15,2) NOT NULL,
    Nombre_Responsable_1 VARCHAR(100) NOT NULL, 
    Nombre_Responsable_2 VARCHAR(100) NOT NULL,
    Rut_Empresa VARCHAR(10) NOT NULL,
    ID_Sede INT NOT NULL,
    ID_Region_Ejecucion INT NOT NULL,
    ID_Region_Impacto INT NOT NULL,
    ID_Tipo_Iniciativa INT NOT NULL,
    ID_Estado INT NOT NULL,
    FOREIGN KEY (Rut_Empresa) REFERENCES Entidad_Empresa(Rut_Empresa),
    FOREIGN KEY (ID_Sede) REFERENCES Sede(ID_Sede),
    FOREIGN KEY (ID_Region_Ejecucion) REFERENCES Region(ID_Region),
    FOREIGN KEY (ID_Region_Impacto) REFERENCES Region(ID_Region),
    FOREIGN KEY (ID_Tipo_Iniciativa) REFERENCES Tipo_Iniciativa(ID_Tipo_Iniciativa),
    FOREIGN KEY (ID_Estado) REFERENCES Estado_Postulacion(ID_Estado)
) ENGINE=InnoDB;

ALTER TABLE Postulacion ADD Correo_Responsable VARCHAR(100) DEFAULT 'postulante@usm.cl';
ALTER TABLE Postulacion ADD ID_Evaluador INT NULL;
ALTER TABLE Postulacion ADD FOREIGN KEY (ID_Evaluador) REFERENCES Usuarios(ID_Usuario);

CREATE TABLE Iniciativa(
    ID_Iniciativa INT PRIMARY KEY AUTO_INCREMENT,
    Documentos VARCHAR(100),
    Nombre_Iniciativa VARCHAR(100) NOT NULL,
    Objetivo_Iniciativa VARCHAR(255) NOT NULL,
    Descripcion_Soluciones VARCHAR(255) NOT NULL,
    Resultados_Esperados VARCHAR(255) NOT NULL,
    ID_Postulacion INT NOT NULL,
    FOREIGN KEY (ID_Postulacion) REFERENCES Postulacion(Numero_Postulacion)
) ENGINE=InnoDB;

CREATE TABLE Integrante_Equipo(
    ID_integrante INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Integrante VARCHAR(100) NOT NULL,
    RUT_Integrante VARCHAR(10) NOT NULL UNIQUE,
    Departamento_Integrante VARCHAR(100) NOT NULL,
    Mail_Integrante VARCHAR(100) NOT NULL,
    Telefono_Integrante VARCHAR(20),
    ID_Tipo_Persona INT NOT NULL,
    ID_Sede INT NOT NULL,
    FOREIGN KEY (ID_Tipo_persona) REFERENCES Tipo_Persona(ID_Tipo_Persona),
    FOREIGN KEY (ID_Sede) REFERENCES Sede(ID_Sede)
) ENGINE=InnoDB;

CREATE TABLE Postulacion_Integrante(
    Numero_Postulacion INT NOT NULL,
    ID_integrante INT NOT NULL,
    Rol_Cumple_Integrante VARCHAR(100) NOT NULL,
    PRIMARY KEY (Numero_Postulacion, ID_integrante),
    FOREIGN KEY (Numero_Postulacion) REFERENCES Postulacion(Numero_Postulacion),
    FOREIGN KEY (ID_integrante) REFERENCES Integrante_Equipo(ID_integrante)
) ENGINE=InnoDB;

CREATE TABLE Etapa_Cronograma(
    ID_Etapa INT NOT NULL AUTO_INCREMENT,
    ID_Postulacion INT NOT NULL,
    Etapa VARCHAR(100) NOT NULL,
    Entregable VARCHAR(100) NOT NULL,
    Plazos INT NOT NULL,
    PRIMARY KEY (ID_Etapa),
    FOREIGN KEY (ID_Postulacion) REFERENCES Postulacion(Numero_Postulacion)
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW Vista_Postulaciones_Principal AS
SELECT 
    p.Numero_Postulacion, 
    p.Codigo_Postulacion, 
    p.Nombre_Responsable_1,
    p.Nombre_Responsable_2,
    i.Nombre_Iniciativa, 
    e.Nombre_Empresa, 
    s.Nombre_Sede, 
    r.Nombre_Region AS Region_Ejecucion, 
    p.Presupuesto_Total, 
    est.Nombre_Estado,
    p.Correo_Responsable,
    u.Correo AS Correo_Evaluador
FROM Postulacion p
LEFT JOIN Iniciativa i ON p.Numero_Postulacion = i.ID_Postulacion
JOIN Entidad_Empresa e ON p.Rut_Empresa = e.Rut_Empresa
JOIN Sede s ON p.ID_Sede = s.ID_Sede
JOIN Region r ON p.ID_Region_Ejecucion = r.ID_Region
JOIN Estado_Postulacion est ON p.ID_Estado = est.ID_Estado
LEFT JOIN Usuarios u ON p.ID_Evaluador = u.ID_Usuario;

DELIMITER //
CREATE FUNCTION TotalSemanasPostulacion(id_post INT) 
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT SUM(Plazos) INTO total FROM Etapa_Cronograma WHERE ID_Postulacion = id_post;
    IF total IS NULL THEN
        SET total = 0;
    END IF;
    RETURN total;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER Validar_Presupuesto_Insert
BEFORE INSERT ON Postulacion
FOR EACH ROW
BEGIN
    IF NEW.Presupuesto_Total < 0 THEN
        SET NEW.Presupuesto_Total = 0;
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE CambiarEstadoPostulacion(IN p_numero INT, IN p_nuevo_estado INT)
BEGIN
    UPDATE Postulacion 
    SET ID_Estado = p_nuevo_estado 
    WHERE Numero_Postulacion = p_numero;
END //
DELIMITER ;