-- ============================================================
-- Script SQL para el sistema de Consolidación & Ministración
-- Ministerio: Consolidación & Ministración - Dpto de
--             Consolidación y Célula
-- ============================================================

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS consolidacion_cfa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Seleccionar la base de datos
USE consolidacion_cfa;

-- ============================================================
-- Tabla de personas (catálogo único por número de documento)
-- ============================================================
CREATE TABLE IF NOT EXISTS personas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    numero_documento VARCHAR(20)  NOT NULL UNIQUE,       -- Documento único por persona
    nombre           VARCHAR(100) NOT NULL,
    apellido         VARCHAR(100) NOT NULL,
    color_equipo     VARCHAR(50)  NOT NULL,
    lider_celula     VARCHAR(150) NOT NULL,
    linea            VARCHAR(150) NOT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear la tabla principal de asistencia
CREATE TABLE IF NOT EXISTS asistencia (
    id               INT AUTO_INCREMENT PRIMARY KEY,       -- Identificador único
    numero_documento VARCHAR(20)  DEFAULT NULL,            -- Documento de la persona
    fecha            DATE NOT NULL,                        -- Fecha del evento
    devocional       ENUM('Sí','No') NOT NULL,             -- Participó del devocional
    convocado        ENUM('Consolidar','Ministrar','Ambos') NOT NULL, -- Propósito de la convocatoria
    color_equipo     VARCHAR(50)  NOT NULL,                 -- Color del equipo asignado
    culto            ENUM('AM','PM') NOT NULL,              -- Turno del culto (mañana o tarde)
    nombre           VARCHAR(100) NOT NULL,                 -- Nombre del asistente
    apellido         VARCHAR(100) NOT NULL,                 -- Apellido del asistente
    lider_celula     VARCHAR(150) NOT NULL,                 -- Nombre del líder de célula
    linea            VARCHAR(150) NOT NULL,                 -- Línea a la que pertenece
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP   -- Fecha/hora de creación del registro
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Datos de ejemplo para personas (para probar autocompletado)
-- ============================================================
INSERT INTO personas (numero_documento, nombre, apellido, color_equipo, lider_celula, linea)
VALUES
    ('12345678',  'Juan',    'Pérez',    'Verde',    'María López',   'Línea 1'),
    ('87654321',  'Ana',     'García',   'Azul',     'Carlos Ruiz',   'Línea 2'),
    ('11223344',  'Pedro',   'Martínez', 'Amarillo', 'Laura Jiménez', 'Línea 3'),
    ('44332211',  'Lucía',   'Ramírez',  'Naranja',  'Diego Torres',  'Línea 4'),
    ('55667788',  'Carlos',  'Herrera',  'Verde',    'María López',   'Línea 1')
ON DUPLICATE KEY UPDATE
    nombre       = VALUES(nombre),
    apellido     = VALUES(apellido),
    color_equipo = VALUES(color_equipo),
    lider_celula = VALUES(lider_celula),
    linea        = VALUES(linea);

-- ============================================================
-- Datos de ejemplo para asistencia
-- ============================================================
INSERT INTO asistencia (numero_documento, fecha, devocional, convocado, color_equipo, culto, nombre, apellido, lider_celula, linea)
VALUES
    ('12345678', '2024-03-10', 'Sí', 'Ambos',      'Verde',    'AM', 'Juan',   'Pérez',    'María López',    'Línea 1'),
    ('87654321', '2024-03-10', 'No', 'Consolidar', 'Azul',     'PM', 'Ana',    'García',   'Carlos Ruiz',    'Línea 2'),
    ('11223344', '2024-03-17', 'Sí', 'Ministrar',  'Amarillo', 'AM', 'Pedro',  'Martínez', 'Laura Jiménez',  'Línea 3');
