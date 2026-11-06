USE sicoes_jms;

-- 1. Crear tabla turnos
CREATE TABLE IF NOT EXISTS turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Insertar turnos por defecto
INSERT IGNORE INTO turnos (nombre) VALUES ('Mañana'), ('Tarde');

-- 2. Modificar tabla docentes
ALTER TABLE docentes ADD COLUMN turno_id INT;
ALTER TABLE docentes ADD CONSTRAINT fk_docentes_turno FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE SET NULL;

-- 3. Modificar tabla estudiantes
ALTER TABLE estudiantes ADD COLUMN turno_id INT;
ALTER TABLE estudiantes ADD CONSTRAINT fk_estudiantes_turno FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE SET NULL;

-- 4. Modificar tabla matriculas (se asigna a cada matrícula individualmente)
ALTER TABLE matriculas ADD COLUMN turno_id INT;
ALTER TABLE matriculas ADD CONSTRAINT fk_matriculas_turno FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE SET NULL;
