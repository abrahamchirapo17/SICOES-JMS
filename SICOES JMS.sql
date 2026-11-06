DROP DATABASE IF EXISTS sicoes_jms;
CREATE DATABASE sicoes_jms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sicoes_jms;

-- ==========================================================
-- MODULO 6: SEGURIDAD Y AUDITORIA
-- ==========================================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
) ENGINE=InnoDB;

CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol_id INT,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE log_actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    detalle TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================================================
-- MODULO 1: CONFIGURACION DEL AÑO ESCOLAR
-- ==========================================================

CREATE TABLE periodos_escolares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE, -- ej. 2023-2024
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('Abierto', 'Cerrado') DEFAULT 'Abierto'
) ENGINE=InnoDB;

CREATE TABLE niveles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE -- Preescolar, Primaria
) ENGINE=InnoDB;

CREATE TABLE grados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel_id INT NOT NULL,
    nombre VARCHAR(50) NOT NULL, -- 1er Nivel, 1ro...
    FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE secciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(10) NOT NULL UNIQUE -- A, B, C, D
) ENGINE=InnoDB;

CREATE TABLE asignaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grado_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lapsos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periodo_id INT NOT NULL,
    numero TINYINT NOT NULL, -- 1, 2, 3
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    FOREIGN KEY (periodo_id) REFERENCES periodos_escolares(id) ON DELETE CASCADE,
    UNIQUE (periodo_id, numero)
) ENGINE=InnoDB;

-- ==========================================================
-- MODULO 3: PERSONAL Y CARGA ACADEMICA
-- ==========================================================

CREATE TABLE docentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    correo VARCHAR(100),
    grado_instruccion VARCHAR(100),
    especialidad VARCHAR(100),
    telefono_personal VARCHAR(20),
    telefono_habitacion VARCHAR(20),
    direccion TEXT,
    patologia TEXT,
    -- Datos Antropométricos
    talla_camisa VARCHAR(10),
    talla_pantalon VARCHAR(10),
    talla_zapato VARCHAR(10),
    peso DECIMAL(5,2),
    estatura DECIMAL(5,2),
    edad INT
) ENGINE=InnoDB;

CREATE TABLE docente_asignacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    periodo_id INT NOT NULL,
    grado_id INT NOT NULL,
    seccion_id INT NOT NULL,
    FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos_escolares(id) ON DELETE CASCADE,
    FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE,
    FOREIGN KEY (seccion_id) REFERENCES secciones(id) ON DELETE CASCADE,
    UNIQUE (periodo_id, grado_id, seccion_id) -- Asegura un docente por sección y grado
) ENGINE=InnoDB;

-- ==========================================================
-- MODULO 2: GESTION DE ESTUDIANTES Y REPRESENTANTES
-- ==========================================================

CREATE TABLE representantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    parentesco VARCHAR(50) NOT NULL,
    profesion VARCHAR(100),
    grado_instruccion VARCHAR(100),
    telefono VARCHAR(20),
    telefono_habitacion VARCHAR(20),
    correo VARCHAR(100),
    numero_hijos INT DEFAULT 1,
    representante_secundario VARCHAR(100),
    telefono_secundario VARCHAR(20)
) ENGINE=InnoDB;

CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    pais_entidad VARCHAR(100),
    municipio VARCHAR(100),
    parroquia VARCHAR(100),
    cedula_escolar VARCHAR(20) NOT NULL UNIQUE,
    sexo ENUM('M', 'F') NOT NULL,
    vive_con VARCHAR(100),
    direccion TEXT,
    representante_emergencia VARCHAR(100),
    telefono_emergencia VARCHAR(20),
    nivel_id INT NOT NULL,
    representante_legal_id INT NOT NULL,
    FOREIGN KEY (nivel_id) REFERENCES niveles(id),
    FOREIGN KEY (representante_legal_id) REFERENCES representantes(id)
) ENGINE=InnoDB;

CREATE TABLE estudiante_vacunas (
    estudiante_id INT PRIMARY KEY,
    triple BOOLEAN DEFAULT FALSE,
    antivariolica BOOLEAN DEFAULT FALSE,
    sarampion BOOLEAN DEFAULT FALSE,
    rubeola BOOLEAN DEFAULT FALSE,
    antitifica BOOLEAN DEFAULT FALSE,
    antiamarilica BOOLEAN DEFAULT FALSE,
    bgc BOOLEAN DEFAULT FALSE,
    polio BOOLEAN DEFAULT FALSE,
    otras_vacunas VARCHAR(255),
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE estudiante_sociales (
    estudiante_id INT PRIMARY KEY,
    documento_legal_representante BOOLEAN DEFAULT FALSE,
    organismo_documento VARCHAR(100),
    situacion_pareja VARCHAR(50),
    viven_juntos BOOLEAN DEFAULT FALSE,
    con_quien_vive VARCHAR(100),
    nombres_con_quien_vive VARCHAR(100),
    parentesco_con_quien_vive VARCHAR(50),
    numero_hermanos INT DEFAULT 0,
    lugar_ocupa INT,
    tipo_vivienda VARCHAR(50),
    tenencia_vivienda VARCHAR(50),
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE estudiante_antropometricos (
    estudiante_id INT PRIMARY KEY,
    talla_camisa VARCHAR(10),
    talla_pantalon VARCHAR(10),
    talla_zapato VARCHAR(10),
    peso DECIMAL(5,2),
    estatura DECIMAL(5,2),
    edad INT,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE estudiante_preescolar (
    estudiante_id INT PRIMARY KEY,
    quien_lo_cuida VARCHAR(100),
    actividades_prefiere TEXT,
    con_quien_juega VARCHAR(100),
    ha_estado_guarderia BOOLEAN DEFAULT FALSE,
    otro_preescolar_cual VARCHAR(100),
    dificultad_observada BOOLEAN DEFAULT FALSE,
    tipo_dificultad TEXT,
    asistido_psicologo BOOLEAN DEFAULT FALSE,
    asistido_psicopedagogo BOOLEAN DEFAULT FALSE,
    asistido_neurologo BOOLEAN DEFAULT FALSE,
    asistido_terapeuta_lenguaje BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE estudiante_antecedentes_medicos (
    estudiante_id INT PRIMARY KEY,
    enfermedad_embarazo TEXT,
    como_fue_parto TEXT,
    como_es_sueno TEXT,
    con_quien_duerme VARCHAR(100),
    hora_acuesta TIME,
    hora_levanta TIME,
    buen_apetito BOOLEAN DEFAULT TRUE,
    controla_esfinteres BOOLEAN DEFAULT TRUE,
    es_alergico BOOLEAN DEFAULT FALSE,
    alergico_a TEXT,
    ha_sido_operado BOOLEAN DEFAULT FALSE,
    operacion_de_que TEXT,
    toma_medicamento BOOLEAN DEFAULT FALSE,
    cuales_medicamentos TEXT,
    condicion_neurodivergente BOOLEAN DEFAULT FALSE,
    cual_condicion TEXT,
    tiene_informe BOOLEAN DEFAULT FALSE,
    especialidad_informe VARCHAR(100),
    tiene_patologia BOOLEAN DEFAULT FALSE,
    cual_patologia TEXT,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================================
-- MODULO 2 (MATRICULA), MODULO 4 Y 5 (REPORTES Y NOTAS)
-- ==========================================================

CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    periodo_id INT NOT NULL,
    grado_id INT NOT NULL,
    seccion_id INT NOT NULL,
    condicion ENUM('Regular', 'Repitiente') DEFAULT 'Regular',
    estado ENUM('Inscrito', 'Retirado', 'Egresado', 'Promovido') DEFAULT 'Inscrito',
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos_escolares(id) ON DELETE CASCADE,
    FOREIGN KEY (grado_id) REFERENCES grados(id),
    FOREIGN KEY (seccion_id) REFERENCES secciones(id),
    UNIQUE (estudiante_id, periodo_id) -- Previene inscripción doble en el mismo año escolar
) ENGINE=InnoDB;

CREATE TABLE notas_finales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL UNIQUE, -- Un único literal por estudiante en ese año
    literal ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE observaciones_lapsos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    lapso_id INT NOT NULL,
    observacion TEXT,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE,
    FOREIGN KEY (lapso_id) REFERENCES lapsos(id) ON DELETE CASCADE,
    UNIQUE (matricula_id, lapso_id) -- Solo una observación por alumno por lapso
) ENGINE=InnoDB;

CREATE TABLE constancias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    tipo ENUM('Médica', 'Legal', 'Citación', 'Actividad Extracurricular') NOT NULL,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_emisor_id INT,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_emisor_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================================================
-- DATOS POR DEFECTO (ROLES Y ADMINISTRADOR)
-- ==========================================================

INSERT INTO roles (nombre, descripcion) VALUES ('Administrador Principal', 'Tiene acceso total al sistema para editar, crear y eliminar registros.');
INSERT INTO roles (nombre, descripcion) VALUES ('Observador', 'Solo puede ver la información registrada, sin posibilidad de edición.');

-- Contraseña por defecto: admin123 (hash bcrypt)
INSERT INTO usuarios (username, password_hash, rol_id, estado) VALUES ('admin', '$2y$10$mopaWyu5XnazUObqzhuGj.rJvvWETkvxa.EyuoNhAfeakOqkDo2GO', 1, 'Activo');
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
