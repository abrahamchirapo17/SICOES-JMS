-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sicoes_jms
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asignaturas`
--

DROP TABLE IF EXISTS `asignaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_asignaturas_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaturas`
--

LOCK TABLES `asignaturas` WRITE;
/*!40000 ALTER TABLE `asignaturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `asignaturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `constancias`
--

DROP TABLE IF EXISTS `constancias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `constancias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `tipo` enum('medica','legal','citacion','actividad_extracurricular') NOT NULL,
  `contenido` text DEFAULT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_constancias_estudiante` (`estudiante_id`),
  KEY `idx_constancias_tipo` (`tipo`),
  KEY `idx_constancias_fecha` (`fecha_emision`),
  KEY `idx_constancias_usuario` (`usuario_id`),
  CONSTRAINT `fk_constancias_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_constancias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `constancias`
--

LOCK TABLES `constancias` WRITE;
/*!40000 ALTER TABLE `constancias` DISABLE KEYS */;
/*!40000 ALTER TABLE `constancias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datos_antropometricos`
--

DROP TABLE IF EXISTS `datos_antropometricos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datos_antropometricos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) DEFAULT NULL,
  `docente_id` int(11) DEFAULT NULL,
  `talla_camisa` varchar(20) DEFAULT NULL,
  `talla_pantalon` varchar(20) DEFAULT NULL,
  `talla_zapato` varchar(20) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `estatura` decimal(5,2) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_antropometricos_estudiante` (`estudiante_id`),
  KEY `idx_antropometricos_docente` (`docente_id`),
  CONSTRAINT `fk_antropometricos_docente` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_antropometricos_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datos_antropometricos`
--

LOCK TABLES `datos_antropometricos` WRITE;
/*!40000 ALTER TABLE `datos_antropometricos` DISABLE KEYS */;
/*!40000 ALTER TABLE `datos_antropometricos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente_asignacion`
--

DROP TABLE IF EXISTS `docente_asignacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente_asignacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docente_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `grado_id` int(11) NOT NULL,
  `seccion_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodo_id` (`periodo_id`,`grado_id`,`seccion_id`),
  KEY `docente_id` (`docente_id`),
  KEY `grado_id` (`grado_id`),
  KEY `seccion_id` (`seccion_id`),
  CONSTRAINT `docente_asignacion_ibfk_1` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `docente_asignacion_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_escolares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `docente_asignacion_ibfk_3` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `docente_asignacion_ibfk_4` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente_asignacion`
--

LOCK TABLES `docente_asignacion` WRITE;
/*!40000 ALTER TABLE `docente_asignacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `docente_asignacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente_seccion`
--

DROP TABLE IF EXISTS `docente_seccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente_seccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docente_id` int(11) NOT NULL,
  `seccion_id` int(11) NOT NULL,
  `grado_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `es_maestro_grado` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_docente_seccion` (`docente_id`,`seccion_id`,`grado_id`,`periodo_id`),
  KEY `idx_docente_seccion_docente` (`docente_id`),
  KEY `idx_docente_seccion_seccion` (`seccion_id`),
  KEY `idx_docente_seccion_grado` (`grado_id`),
  KEY `idx_docente_seccion_periodo` (`periodo_id`),
  CONSTRAINT `fk_docente_seccion_docente` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_docente_seccion_grado` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_docente_seccion_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_docente_seccion_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente_seccion`
--

LOCK TABLES `docente_seccion` WRITE;
/*!40000 ALTER TABLE `docente_seccion` DISABLE KEYS */;
/*!40000 ALTER TABLE `docente_seccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docentes`
--

DROP TABLE IF EXISTS `docentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `grado_instruccion` varchar(100) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `telefono_personal` varchar(20) DEFAULT NULL,
  `telefono_habitacion` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `patologia` text DEFAULT NULL,
  `talla_camisa` varchar(10) DEFAULT NULL,
  `talla_pantalon` varchar(10) DEFAULT NULL,
  `talla_zapato` varchar(10) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `estatura` decimal(5,2) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `turno_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `fk_docentes_turno` (`turno_id`),
  CONSTRAINT `fk_docentes_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docentes`
--

LOCK TABLES `docentes` WRITE;
/*!40000 ALTER TABLE `docentes` DISABLE KEYS */;
/*!40000 ALTER TABLE `docentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_antecedentes_medicos`
--

DROP TABLE IF EXISTS `estudiante_antecedentes_medicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_antecedentes_medicos` (
  `estudiante_id` int(11) NOT NULL,
  `enfermedad_embarazo` text DEFAULT NULL,
  `como_fue_parto` text DEFAULT NULL,
  `como_es_sueno` text DEFAULT NULL,
  `con_quien_duerme` varchar(100) DEFAULT NULL,
  `hora_acuesta` time DEFAULT NULL,
  `hora_levanta` time DEFAULT NULL,
  `buen_apetito` tinyint(1) DEFAULT 1,
  `controla_esfinteres` tinyint(1) DEFAULT 1,
  `es_alergico` tinyint(1) DEFAULT 0,
  `alergico_a` text DEFAULT NULL,
  `ha_sido_operado` tinyint(1) DEFAULT 0,
  `operacion_de_que` text DEFAULT NULL,
  `toma_medicamento` tinyint(1) DEFAULT 0,
  `cuales_medicamentos` text DEFAULT NULL,
  `condicion_neurodivergente` tinyint(1) DEFAULT 0,
  `cual_condicion` text DEFAULT NULL,
  `tiene_informe` tinyint(1) DEFAULT 0,
  `especialidad_informe` varchar(100) DEFAULT NULL,
  `tiene_patologia` tinyint(1) DEFAULT 0,
  `cual_patologia` text DEFAULT NULL,
  PRIMARY KEY (`estudiante_id`),
  CONSTRAINT `estudiante_antecedentes_medicos_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_antecedentes_medicos`
--

LOCK TABLES `estudiante_antecedentes_medicos` WRITE;
/*!40000 ALTER TABLE `estudiante_antecedentes_medicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_antecedentes_medicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_antropometricos`
--

DROP TABLE IF EXISTS `estudiante_antropometricos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_antropometricos` (
  `estudiante_id` int(11) NOT NULL,
  `talla_camisa` varchar(10) DEFAULT NULL,
  `talla_pantalon` varchar(10) DEFAULT NULL,
  `talla_zapato` varchar(10) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `estatura` decimal(5,2) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  PRIMARY KEY (`estudiante_id`),
  CONSTRAINT `estudiante_antropometricos_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_antropometricos`
--

LOCK TABLES `estudiante_antropometricos` WRITE;
/*!40000 ALTER TABLE `estudiante_antropometricos` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_antropometricos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_otras_vacunas`
--

DROP TABLE IF EXISTS `estudiante_otras_vacunas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_otras_vacunas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_otras_vacunas_estudiante` (`estudiante_id`),
  CONSTRAINT `fk_otras_vacunas_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_otras_vacunas`
--

LOCK TABLES `estudiante_otras_vacunas` WRITE;
/*!40000 ALTER TABLE `estudiante_otras_vacunas` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_otras_vacunas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_preescolar`
--

DROP TABLE IF EXISTS `estudiante_preescolar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_preescolar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `quien_cuida` varchar(100) DEFAULT NULL,
  `actividades_preferidas` text DEFAULT NULL,
  `con_quien_juega` varchar(100) DEFAULT NULL,
  `estado_guarderia` tinyint(4) DEFAULT 0,
  `otro_prescolar` tinyint(4) DEFAULT 0,
  `cual_prescolar` varchar(100) DEFAULT NULL,
  `dificultad_observada` tinyint(4) DEFAULT 0,
  `tipo_dificultad` text DEFAULT NULL,
  `consulta_psicologo` tinyint(4) DEFAULT 0,
  `consulta_psicopedagogo` tinyint(4) DEFAULT 0,
  `consulta_neurologo` tinyint(4) DEFAULT 0,
  `consulta_terapeuta_lenguaje` tinyint(4) DEFAULT 0,
  `enfermedad_embarazo` varchar(255) DEFAULT NULL,
  `tipo_parto` varchar(50) DEFAULT NULL,
  `sueno_nino` varchar(50) DEFAULT NULL,
  `con_quien_duerme` varchar(100) DEFAULT NULL,
  `hora_acuesta` time DEFAULT NULL,
  `hora_levanta` time DEFAULT NULL,
  `buen_apetito` tinyint(4) DEFAULT 0,
  `controla_esfinteres` tinyint(4) DEFAULT 0,
  `alergico` tinyint(4) DEFAULT 0,
  `a_que_alergia` varchar(255) DEFAULT NULL,
  `operado` tinyint(4) DEFAULT 0,
  `tipo_operacion` varchar(255) DEFAULT NULL,
  `toma_medicamento` tinyint(4) DEFAULT 0,
  `cual_medicamento` varchar(255) DEFAULT NULL,
  `condicion_neurodivergente` tinyint(4) DEFAULT 0,
  `cual_condicion` varchar(255) DEFAULT NULL,
  `tiene_informe` tinyint(4) DEFAULT 0,
  `especialidad` varchar(100) DEFAULT NULL,
  `patologia` tinyint(4) DEFAULT 0,
  `cual_patologia` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estudiante_id` (`estudiante_id`),
  KEY `idx_preescolar_estudiante` (`estudiante_id`),
  CONSTRAINT `fk_preescolar_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_preescolar`
--

LOCK TABLES `estudiante_preescolar` WRITE;
/*!40000 ALTER TABLE `estudiante_preescolar` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_preescolar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_representante`
--

DROP TABLE IF EXISTS `estudiante_representante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_representante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `representante_id` int(11) NOT NULL,
  `es_principal` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_estudiante_representante` (`estudiante_id`,`representante_id`),
  KEY `idx_est_rep_estudiante` (`estudiante_id`),
  KEY `idx_est_rep_representante` (`representante_id`),
  KEY `idx_est_rep_principal` (`es_principal`),
  CONSTRAINT `fk_est_rep_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_est_rep_representante` FOREIGN KEY (`representante_id`) REFERENCES `representantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_representante`
--

LOCK TABLES `estudiante_representante` WRITE;
/*!40000 ALTER TABLE `estudiante_representante` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_representante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_sociales`
--

DROP TABLE IF EXISTS `estudiante_sociales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_sociales` (
  `estudiante_id` int(11) NOT NULL,
  `documento_legal_representante` tinyint(1) DEFAULT 0,
  `organismo_documento` varchar(100) DEFAULT NULL,
  `situacion_pareja` varchar(50) DEFAULT NULL,
  `viven_juntos` tinyint(1) DEFAULT 0,
  `con_quien_vive` varchar(100) DEFAULT NULL,
  `nombres_con_quien_vive` varchar(100) DEFAULT NULL,
  `parentesco_con_quien_vive` varchar(50) DEFAULT NULL,
  `numero_hermanos` int(11) DEFAULT 0,
  `lugar_ocupa` int(11) DEFAULT NULL,
  `tipo_vivienda` varchar(50) DEFAULT NULL,
  `tenencia_vivienda` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`estudiante_id`),
  CONSTRAINT `estudiante_sociales_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_sociales`
--

LOCK TABLES `estudiante_sociales` WRITE;
/*!40000 ALTER TABLE `estudiante_sociales` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_sociales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiante_vacunas`
--

DROP TABLE IF EXISTS `estudiante_vacunas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante_vacunas` (
  `estudiante_id` int(11) NOT NULL,
  `triple` tinyint(1) DEFAULT 0,
  `antivariolica` tinyint(1) DEFAULT 0,
  `sarampion` tinyint(1) DEFAULT 0,
  `rubeola` tinyint(1) DEFAULT 0,
  `antitifica` tinyint(1) DEFAULT 0,
  `antiamarilica` tinyint(1) DEFAULT 0,
  `bgc` tinyint(1) DEFAULT 0,
  `polio` tinyint(1) DEFAULT 0,
  `otras_vacunas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`estudiante_id`),
  CONSTRAINT `estudiante_vacunas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante_vacunas`
--

LOCK TABLES `estudiante_vacunas` WRITE;
/*!40000 ALTER TABLE `estudiante_vacunas` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiante_vacunas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `pais_entidad` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `parroquia` varchar(100) DEFAULT NULL,
  `cedula_escolar` varchar(20) NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `vive_con` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `representante_emergencia` varchar(100) DEFAULT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `nivel_id` int(11) NOT NULL,
  `representante_legal_id` int(11) NOT NULL,
  `turno_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula_escolar` (`cedula_escolar`),
  KEY `nivel_id` (`nivel_id`),
  KEY `representante_legal_id` (`representante_legal_id`),
  KEY `fk_estudiantes_turno` (`turno_id`),
  CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`nivel_id`) REFERENCES `niveles` (`id`),
  CONSTRAINT `estudiantes_ibfk_2` FOREIGN KEY (`representante_legal_id`) REFERENCES `representantes` (`id`),
  CONSTRAINT `fk_estudiantes_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiantes`
--

LOCK TABLES `estudiantes` WRITE;
/*!40000 ALTER TABLE `estudiantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `estudiantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grado_asignatura`
--

DROP TABLE IF EXISTS `grado_asignatura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grado_asignatura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grado_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `docente_id` int(11) DEFAULT NULL,
  `periodo_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_grado_asignatura` (`grado_id`,`asignatura_id`,`periodo_id`),
  KEY `idx_grado_asignatura_grado` (`grado_id`),
  KEY `idx_grado_asignatura_asignatura` (`asignatura_id`),
  KEY `idx_grado_asignatura_docente` (`docente_id`),
  KEY `idx_grado_asignatura_periodo` (`periodo_id`),
  CONSTRAINT `fk_grado_asignatura_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_grado_asignatura_docente` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_grado_asignatura_grado` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_grado_asignatura_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grado_asignatura`
--

LOCK TABLES `grado_asignatura` WRITE;
/*!40000 ALTER TABLE `grado_asignatura` DISABLE KEYS */;
/*!40000 ALTER TABLE `grado_asignatura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grados`
--

DROP TABLE IF EXISTS `grados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nivel_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nivel_id` (`nivel_id`),
  CONSTRAINT `grados_ibfk_1` FOREIGN KEY (`nivel_id`) REFERENCES `niveles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grados`
--

LOCK TABLES `grados` WRITE;
/*!40000 ALTER TABLE `grados` DISABLE KEYS */;
/*!40000 ALTER TABLE `grados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grados_secciones_activas`
--

DROP TABLE IF EXISTS `grados_secciones_activas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grados_secciones_activas` (
  `grado_id` int(11) NOT NULL,
  `seccion_id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`grado_id`,`seccion_id`,`turno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grados_secciones_activas`
--

LOCK TABLES `grados_secciones_activas` WRITE;
/*!40000 ALTER TABLE `grados_secciones_activas` DISABLE KEYS */;
/*!40000 ALTER TABLE `grados_secciones_activas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lapsos`
--

DROP TABLE IF EXISTS `lapsos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lapsos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `periodo_id` int(11) NOT NULL,
  `numero` tinyint(4) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodo_id` (`periodo_id`,`numero`),
  CONSTRAINT `lapsos_ibfk_1` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_escolares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lapsos`
--

LOCK TABLES `lapsos` WRITE;
/*!40000 ALTER TABLE `lapsos` DISABLE KEYS */;
/*!40000 ALTER TABLE `lapsos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_actividades`
--

DROP TABLE IF EXISTS `log_actividades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_actividades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `fecha_accion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_log_usuario` (`usuario_id`),
  KEY `idx_log_tabla` (`tabla_afectada`),
  KEY `idx_log_fecha` (`fecha_accion`),
  CONSTRAINT `fk_log_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_actividades`
--

LOCK TABLES `log_actividades` WRITE;
/*!40000 ALTER TABLE `log_actividades` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_actividades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 1,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matriculas`
--

DROP TABLE IF EXISTS `matriculas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `grado_id` int(11) NOT NULL,
  `seccion_id` int(11) NOT NULL,
  `condicion` enum('Nuevo Ingreso','Regular','Repitiente') DEFAULT 'Regular',
  `estado` enum('Inscrito','Retirado','Egresado','Promovido') DEFAULT 'Inscrito',
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `turno_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estudiante_id` (`estudiante_id`,`periodo_id`),
  KEY `periodo_id` (`periodo_id`),
  KEY `grado_id` (`grado_id`),
  KEY `seccion_id` (`seccion_id`),
  KEY `fk_matriculas_turno` (`turno_id`),
  CONSTRAINT `fk_matriculas_turno` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_escolares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matriculas_ibfk_3` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`),
  CONSTRAINT `matriculas_ibfk_4` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matriculas`
--

LOCK TABLES `matriculas` WRITE;
/*!40000 ALTER TABLE `matriculas` DISABLE KEYS */;
/*!40000 ALTER TABLE `matriculas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `niveles`
--

DROP TABLE IF EXISTS `niveles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `niveles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `niveles`
--

LOCK TABLES `niveles` WRITE;
/*!40000 ALTER TABLE `niveles` DISABLE KEYS */;
INSERT INTO `niveles` VALUES (1,'Preescolar'),(2,'Primaria');
/*!40000 ALTER TABLE `niveles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas`
--

DROP TABLE IF EXISTS `notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `lapso_id` int(11) NOT NULL,
  `nota_literal` varchar(50) DEFAULT NULL,
  `periodo_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nota_estudiante_asignatura_lapso` (`estudiante_id`,`asignatura_id`,`lapso_id`),
  KEY `idx_notas_estudiante` (`estudiante_id`),
  KEY `idx_notas_asignatura` (`asignatura_id`),
  KEY `idx_notas_lapso` (`lapso_id`),
  KEY `idx_notas_periodo` (`periodo_id`),
  CONSTRAINT `fk_notas_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notas_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notas_lapso` FOREIGN KEY (`lapso_id`) REFERENCES `lapsos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notas_periodo` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas`
--

LOCK TABLES `notas` WRITE;
/*!40000 ALTER TABLE `notas` DISABLE KEYS */;
/*!40000 ALTER TABLE `notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas_finales`
--

DROP TABLE IF EXISTS `notas_finales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notas_finales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricula_id` int(11) NOT NULL,
  `literal` enum('A','B','C','D','E') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula_id` (`matricula_id`),
  CONSTRAINT `notas_finales_ibfk_1` FOREIGN KEY (`matricula_id`) REFERENCES `matriculas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas_finales`
--

LOCK TABLES `notas_finales` WRITE;
/*!40000 ALTER TABLE `notas_finales` DISABLE KEYS */;
/*!40000 ALTER TABLE `notas_finales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observaciones`
--

DROP TABLE IF EXISTS `observaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `lapso_id` int(11) NOT NULL,
  `observacion` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_observaciones_estudiante` (`estudiante_id`),
  KEY `idx_observaciones_lapso` (`lapso_id`),
  KEY `idx_observaciones_usuario` (`usuario_id`),
  CONSTRAINT `fk_observaciones_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_observaciones_lapso` FOREIGN KEY (`lapso_id`) REFERENCES `lapsos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_observaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observaciones`
--

LOCK TABLES `observaciones` WRITE;
/*!40000 ALTER TABLE `observaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `observaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observaciones_lapsos`
--

DROP TABLE IF EXISTS `observaciones_lapsos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observaciones_lapsos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matricula_id` int(11) NOT NULL,
  `lapso_id` int(11) NOT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula_id` (`matricula_id`,`lapso_id`),
  KEY `lapso_id` (`lapso_id`),
  CONSTRAINT `observaciones_lapsos_ibfk_1` FOREIGN KEY (`matricula_id`) REFERENCES `matriculas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `observaciones_lapsos_ibfk_2` FOREIGN KEY (`lapso_id`) REFERENCES `lapsos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observaciones_lapsos`
--

LOCK TABLES `observaciones_lapsos` WRITE;
/*!40000 ALTER TABLE `observaciones_lapsos` DISABLE KEYS */;
/*!40000 ALTER TABLE `observaciones_lapsos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos`
--

DROP TABLE IF EXISTS `periodos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anio_escolar` varchar(20) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado` enum('abierto','cerrado') DEFAULT 'abierto',
  PRIMARY KEY (`id`),
  KEY `idx_periodos_anio` (`anio_escolar`),
  KEY `idx_periodos_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos`
--

LOCK TABLES `periodos` WRITE;
/*!40000 ALTER TABLE `periodos` DISABLE KEYS */;
/*!40000 ALTER TABLE `periodos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_escolares`
--

DROP TABLE IF EXISTS `periodos_escolares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periodos_escolares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('Abierto','Cerrado') DEFAULT 'Abierto',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos_escolares`
--

LOCK TABLES `periodos_escolares` WRITE;
/*!40000 ALTER TABLE `periodos_escolares` DISABLE KEYS */;
/*!40000 ALTER TABLE `periodos_escolares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `representantes`
--

DROP TABLE IF EXISTS `representantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `representantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `parentesco` varchar(50) NOT NULL,
  `profesion` varchar(100) DEFAULT NULL,
  `grado_instruccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono_habitacion` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `numero_hijos` int(11) DEFAULT 1,
  `representante_secundario` varchar(100) DEFAULT NULL,
  `telefono_secundario` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `representantes`
--

LOCK TABLES `representantes` WRITE;
/*!40000 ALTER TABLE `representantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `representantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_permiso`
--

DROP TABLE IF EXISTS `rol_permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol_permiso` (
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  PRIMARY KEY (`rol_id`,`permiso_id`),
  KEY `idx_rol_permiso_rol` (`rol_id`),
  KEY `idx_rol_permiso_permiso` (`permiso_id`),
  CONSTRAINT `fk_rol_permiso_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_permiso_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permiso`
--

LOCK TABLES `rol_permiso` WRITE;
/*!40000 ALTER TABLE `rol_permiso` DISABLE KEYS */;
/*!40000 ALTER TABLE `rol_permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador Principal','Tiene acceso total al sistema para editar, crear y eliminar registros.'),(2,'Observador','Solo puede ver la información registrada, sin posibilidad de edición.'),(3,'Secretaria','Puede gestionar estudiantes y docentes, pero sin permisos destructivos'),(4,'Director','Acceso gerencial y reportes');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `secciones`
--

DROP TABLE IF EXISTS `secciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secciones`
--

LOCK TABLES `secciones` WRITE;
/*!40000 ALTER TABLE `secciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `secciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turnos`
--

DROP TABLE IF EXISTS `turnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `turnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turnos`
--

LOCK TABLES `turnos` WRITE;
/*!40000 ALTER TABLE `turnos` DISABLE KEYS */;
INSERT INTO `turnos` VALUES (1,'Mañana'),(2,'Tarde');
/*!40000 ALTER TABLE `turnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `respuesta_seguridad` varchar(255) DEFAULT NULL,
  `pregunta_seguridad` varchar(255) DEFAULT '┐Cußl fue la primera escuela donde estudi¾?',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'lucero','$2y$10$7gTKqEX3.5I94tPlw8F1lOHEeRyO90iB4rdVWUGMZ28r92x7fr.nS','Patria','┐Cußl fue la primera escuela donde estudi¾?'),(10,'uptamca','$2y$10$h74pRjAWm4dHi8aCv9UF6e19h8ohw9mukm.whkFy9TWH/PD5wpCNi','2024','en que a±o iniciaste actividades en la uptamca?');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_perfiles`
--

DROP TABLE IF EXISTS `usuarios_perfiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios_perfiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_perfiles`
--

LOCK TABLES `usuarios_perfiles` WRITE;
/*!40000 ALTER TABLE `usuarios_perfiles` DISABLE KEYS */;
INSERT INTO `usuarios_perfiles` VALUES (1,1,1,'Activo','2026-05-31 11:34:22'),(11,10,1,'Activo','2026-07-27 00:01:06');
/*!40000 ALTER TABLE `usuarios_perfiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vacunas_estudiante`
--

DROP TABLE IF EXISTS `vacunas_estudiante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vacunas_estudiante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estudiante_id` int(11) NOT NULL,
  `vacuna` varchar(50) NOT NULL,
  `tiene` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_vacunas_estudiante` (`estudiante_id`),
  KEY `idx_vacunas_vacuna` (`vacuna`),
  CONSTRAINT `fk_vacunas_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vacunas_estudiante`
--

LOCK TABLES `vacunas_estudiante` WRITE;
/*!40000 ALTER TABLE `vacunas_estudiante` DISABLE KEYS */;
/*!40000 ALTER TABLE `vacunas_estudiante` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-26 22:45:33
