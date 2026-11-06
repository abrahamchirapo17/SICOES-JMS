USE sicoes_jms;

ALTER TABLE docentes 
ADD COLUMN grado_instruccion VARCHAR(100) NULL,
ADD COLUMN telefono VARCHAR(20) NULL,
ADD COLUMN direccion TEXT NULL,
ADD COLUMN patologia_estado ENUM('Si', 'No') DEFAULT 'No',
ADD COLUMN patologia_descripcion TEXT NULL,
ADD COLUMN talla_camisa VARCHAR(10) NULL,
ADD COLUMN talla_pantalon VARCHAR(10) NULL,
ADD COLUMN talla_zapato VARCHAR(10) NULL,
ADD COLUMN peso DECIMAL(5,2) NULL,
ADD COLUMN estatura DECIMAL(4,2) NULL,
ADD COLUMN edad INT NULL;
