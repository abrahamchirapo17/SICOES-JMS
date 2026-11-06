<?php
require_once '../conexion.php';
require_once '../utils/helpers.php';

$conexion = new Conexion();
$conn = $conexion->getConexion();
if (!$conn) {
    send_json_response('error', 'Error de conexión a la base de datos.');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'buscar_representante':
        $cedula = sanitize_input($_POST['cedula'] ?? '');
        if (!$cedula) send_json_response('error', 'Cédula no proporcionada.');
        try {
            $stmt = $conn->prepare("SELECT * FROM representantes WHERE cedula = ?");
            $stmt->execute([$cedula]);
            $rep = $stmt->fetch();
            if ($rep) {
                send_json_response('success', 'Representante encontrado.', $rep);
            } else {
                send_json_response('error', 'No encontrado.');
            }
        } catch(Throwable $e) {
            send_json_response('error', 'Error en busqueda.');
        }
        break;

    case 'listar_clases_etapa':
        $etapa = sanitize_input($_POST['etapa_nombre'] ?? '');
        if (!$etapa) send_json_response('error', 'Etapa no proporcionada.');
        try {
            $nivel_id = $conn->query("SELECT id FROM niveles WHERE nombre = '$etapa'")->fetchColumn();
            if (!$nivel_id) send_json_response('error', 'Nivel no encontrado.');

            $query = "
                SELECT gs.grado_id, gs.seccion_id, gs.turno_id, g.nombre AS grado, s.nombre AS seccion, t.nombre AS turno
                FROM grados_secciones_activas gs
                JOIN grados g ON gs.grado_id = g.id
                JOIN secciones s ON gs.seccion_id = s.id
                LEFT JOIN turnos t ON gs.turno_id = t.id
                WHERE g.nivel_id = $nivel_id
                ORDER BY t.nombre, g.nombre, s.nombre
            ";
            $clases = $conn->query($query)->fetchAll();
            // Inyectamos nivel_id para que el front lo tenga
            foreach ($clases as &$c) {
                $c['nivel_id'] = $nivel_id;
            }
            send_json_response('success', 'Clases cargadas', $clases);
        } catch(Throwable $e) {
            send_json_response('error', 'Error al cargar clases.');
        }
        break;

    case 'registrar_estudiante':
        if ($_SESSION['rol_id'] != 1) {
            send_json_response('error', 'Solo los administradores pueden registrar estudiantes.');
            break;
        }
        // ========================================
        // 1. Validaciones Básicas
        // ========================================
        $est_cedula = sanitize_input($_POST['est_cedula'] ?? '');
        $est_nombres = sanitize_input($_POST['est_nombres'] ?? '');
        $est_apellidos = sanitize_input($_POST['est_apellidos'] ?? '');
        $est_fecha_nac = sanitize_input($_POST['est_fecha_nac'] ?? '');
        $est_sexo = sanitize_input($_POST['est_sexo'] ?? '');
        $est_vive_con = sanitize_input($_POST['est_vive_con'] ?? '');
        $est_pais = sanitize_input($_POST['est_pais'] ?? '');
        $est_municipio = sanitize_input($_POST['est_municipio'] ?? '');
        $est_parroquia = sanitize_input($_POST['est_parroquia'] ?? '');
        $est_direccion = sanitize_input($_POST['est_direccion'] ?? '');
        
        $est_emergencia_nombre = sanitize_input($_POST['est_emergencia_nombre'] ?? '');
        $est_emergencia_telefono = sanitize_input($_POST['est_emergencia_telefono'] ?? '');
        
        // Datos Académicos (Matrícula)
        $mat_periodo = sanitize_input($_POST['mat_periodo'] ?? '');
        $mat_condicion = sanitize_input($_POST['mat_condicion'] ?? 'Regular');
        $mat_clase = $_POST['mat_clase'] ?? '';
        
        $clase = json_decode($mat_clase, true);
        if (!$est_cedula || !$est_nombres || !$est_apellidos || !$mat_periodo || !$clase || !isset($clase['grado_id'])) {
            send_json_response('error', 'Faltan datos obligatorios del estudiante o asignación.');
        }

        // Representante
        $rep_cedula = sanitize_input($_POST['rep_cedula'] ?? '');
        $rep_nombres = sanitize_input($_POST['rep_nombres'] ?? '');
        $rep_apellidos = sanitize_input($_POST['rep_apellidos'] ?? '');
        $rep_parentesco = sanitize_input($_POST['rep_parentesco'] ?? 'Madre/Padre');
        $rep_telefono = sanitize_input($_POST['rep_telefono'] ?? '');
        
        if (!$rep_cedula || !$rep_nombres || !$rep_apellidos) {
            send_json_response('error', 'Faltan datos obligatorios del representante.');
        }

        try {
            $conn->beginTransaction();

            // ========================================
            // 2. Registro del Representante
            // ========================================
            $rep_id = $conn->query("SELECT id FROM representantes WHERE cedula = '$rep_cedula'")->fetchColumn();
            
            if (!$rep_id) {
                $stmt = $conn->prepare("INSERT INTO representantes (cedula, nombres, apellidos, parentesco, telefono, telefono_habitacion, profesion, grado_instruccion, correo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $rep_cedula, $rep_nombres, $rep_apellidos, $rep_parentesco, $rep_telefono,
                    sanitize_input($_POST['rep_telefono_hab'] ?? ''),
                    sanitize_input($_POST['rep_profesion'] ?? ''),
                    sanitize_input($_POST['rep_instruccion'] ?? ''),
                    sanitize_input($_POST['rep_correo'] ?? '')
                ]);
                $rep_id = $conn->lastInsertId();
            } else {
                $stmt = $conn->prepare("UPDATE representantes SET nombres=?, apellidos=?, parentesco=?, telefono=?, telefono_habitacion=?, profesion=?, grado_instruccion=?, correo=? WHERE id=?");
                $stmt->execute([
                    $rep_nombres, $rep_apellidos, $rep_parentesco, $rep_telefono,
                    sanitize_input($_POST['rep_telefono_hab'] ?? ''),
                    sanitize_input($_POST['rep_profesion'] ?? ''),
                    sanitize_input($_POST['rep_instruccion'] ?? ''),
                    sanitize_input($_POST['rep_correo'] ?? ''),
                    $rep_id
                ]);
            }

            // ========================================
            // 3. Registro del Estudiante
            // ========================================
            $est_existe = $conn->query("SELECT id FROM estudiantes WHERE cedula_escolar = '$est_cedula'")->fetchColumn();
            if ($est_existe) {
                $conn->rollBack();
                send_json_response('error', 'Ya existe un estudiante con la cédula escolar ingresada.');
            }

            $stmt = $conn->prepare("INSERT INTO estudiantes (cedula_escolar, nombres, apellidos, fecha_nacimiento, sexo, vive_con, pais_entidad, municipio, parroquia, direccion, representante_emergencia, telefono_emergencia, nivel_id, representante_legal_id, turno_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $est_cedula, $est_nombres, $est_apellidos, $est_fecha_nac, $est_sexo, $est_vive_con,
                $est_pais, $est_municipio, $est_parroquia, $est_direccion,
                $est_emergencia_nombre, $est_emergencia_telefono,
                $clase['nivel_id'], $rep_id, $clase['turno_id']
            ]);
            $est_id = $conn->lastInsertId();

            // ========================================
            // 4. Relación Estudiante - Representante
            // ========================================
            $conn->query("INSERT INTO estudiante_representante (estudiante_id, representante_id, es_principal) VALUES ($est_id, $rep_id, 1)");

            // ========================================
            // 5. Datos Médicos y Antropométricos
            // ========================================
            $stmt = $conn->prepare("INSERT INTO estudiante_antropometricos (estudiante_id, edad, peso, estatura, talla_camisa, talla_pantalon, talla_zapato) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $est_id,
                sanitize_input($_POST['antro_edad'] ?? null) ?: null,
                sanitize_input($_POST['antro_peso'] ?? null) ?: null,
                sanitize_input($_POST['antro_estatura'] ?? null) ?: null,
                sanitize_input($_POST['antro_camisa'] ?? ''),
                sanitize_input($_POST['antro_pantalon'] ?? ''),
                sanitize_input($_POST['antro_zapato'] ?? '')
            ]);

            $stmt = $conn->prepare("INSERT INTO estudiante_antecedentes_medicos (
                estudiante_id, enfermedad_embarazo, como_fue_parto, 
                es_alergico, alergico_a, 
                toma_medicamento, cuales_medicamentos, 
                condicion_neurodivergente, cual_condicion, 
                tiene_informe, especialidad_informe, 
                ha_sido_operado, operacion_de_que, 
                tiene_patologia, cual_patologia
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $est_id,
                sanitize_input($_POST['med_embarazo'] ?? ''),
                sanitize_input($_POST['med_parto'] ?? ''),
                (sanitize_input($_POST['med_alergias'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_alergias_txt'] ?? ''),
                (sanitize_input($_POST['med_medicamentos'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_medicamentos_txt'] ?? ''),
                (sanitize_input($_POST['med_neurodivergente'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_neurodivergente_txt'] ?? ''),
                (sanitize_input($_POST['med_informe'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_informe_txt'] ?? ''),
                (sanitize_input($_POST['med_operaciones'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_operaciones_txt'] ?? ''),
                (sanitize_input($_POST['med_patologia'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_patologia_txt'] ?? '')
            ]);

            // ========================================
            // 5.5. Vacunas y Sociales
            // ========================================
            $stmt = $conn->prepare("INSERT INTO estudiante_vacunas (estudiante_id, triple, antivariolica, sarampion, rubeola, antitifica, antiamarilica, bgc, polio, otras_vacunas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $est_id,
                (sanitize_input($_POST['vac_triple'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_antivariolica'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_sarampion'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_rubeola'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_antitifica'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_antiamarilica'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_bgc'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_polio'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['vac_otras'] ?? '')
            ]);

            $stmt = $conn->prepare("INSERT INTO estudiante_sociales (estudiante_id, documento_legal_representante, organismo_documento, situacion_pareja, viven_juntos, con_quien_vive, nombres_con_quien_vive, parentesco_con_quien_vive, numero_hermanos, lugar_ocupa, tipo_vivienda, tenencia_vivienda) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $est_id,
                (sanitize_input($_POST['soc_doc_legal'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['soc_organismo'] ?? ''),
                sanitize_input($_POST['soc_pareja'] ?? ''),
                (sanitize_input($_POST['soc_viven_juntos'] ?? 'Si') === 'Si') ? 1 : 0,
                sanitize_input($_POST['soc_parentesco_vive'] ?? ''),
                sanitize_input($_POST['soc_con_quien'] ?? ''),
                sanitize_input($_POST['soc_parentesco_vive'] ?? ''),
                intval($_POST['soc_hermanos'] ?? 0),
                intval($_POST['soc_lugar_hermanos'] ?? 1),
                sanitize_input($_POST['soc_tipo_vivienda'] ?? ''),
                sanitize_input($_POST['soc_tenencia'] ?? '')
            ]);

            // ========================================
            // 6. Preescolar Exclusivo
            // ========================================
            if (sanitize_input($_POST['es_preescolar'] ?? '0') === '1') {
                $stmt = $conn->prepare("INSERT INTO estudiante_preescolar (
                    estudiante_id, quien_cuida, actividades_preferidas, con_quien_juega, 
                    estado_guarderia, otro_prescolar, cual_prescolar, 
                    dificultad_observada, tipo_dificultad, 
                    consulta_psicologo, consulta_psicopedagogo, consulta_neurologo, consulta_terapeuta_lenguaje
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $est_id,
                    sanitize_input($_POST['pre_quien_cuida'] ?? ''),
                    sanitize_input($_POST['pre_actividades_prefiere'] ?? ''),
                    sanitize_input($_POST['pre_con_quien_juega'] ?? ''),
                    (sanitize_input($_POST['pre_estado_guarderia'] ?? 'No') === 'Si' ? 1 : 0),
                    (sanitize_input($_POST['pre_otro_preescolar'] ?? 'No') === 'Si' ? 1 : 0),
                    sanitize_input($_POST['pre_cual_preescolar_txt'] ?? ''),
                    (sanitize_input($_POST['pre_dificultad_observada'] ?? 'No') === 'Si' ? 1 : 0),
                    sanitize_input($_POST['pre_dificultad_tipo_txt'] ?? ''),
                    (sanitize_input($_POST['pre_consulta_psicologo'] ?? 'No') === 'Si' ? 1 : 0),
                    (sanitize_input($_POST['pre_consulta_psicopedagogo'] ?? 'No') === 'Si' ? 1 : 0),
                    (sanitize_input($_POST['pre_consulta_neurologo'] ?? 'No') === 'Si' ? 1 : 0),
                    (sanitize_input($_POST['pre_consulta_terapeuta'] ?? 'No') === 'Si' ? 1 : 0)
                ]);
            }

            // ========================================
            // 7. Generar Matrícula (Inscripción Académica)
            // ========================================
            $grado_id = $clase['grado_id'];
            $seccion_id = $clase['seccion_id'];
            $stmt = $conn->prepare("INSERT INTO matriculas (estudiante_id, periodo_id, grado_id, seccion_id, condicion, turno_id, estado, fecha_inscripcion) VALUES (?, ?, ?, ?, ?, ?, 'Inscrito', CURRENT_DATE())");
            $stmt->execute([
                $est_id,
                $mat_periodo,
                $grado_id,
                $seccion_id,
                $mat_condicion,
                $clase['turno_id']
            ]);

            $gradoNombre = $conn->query("SELECT nombre FROM grados WHERE id = $grado_id")->fetchColumn();
            $seccionNombre = $conn->query("SELECT nombre FROM secciones WHERE id = $seccion_id")->fetchColumn();

            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Inscripción Estudiante', "Se inscribió al estudiante $est_nombres $est_apellidos en $gradoNombre sección $seccionNombre");

            $conn->commit();
            send_json_response('success', 'Estudiante inscrito exitosamente con todos sus datos.');

        } catch (Throwable $e) {
            $conn->rollBack();
            // Registramos línea y archivo para debugging rápido del usuario si vuelve a fallar
            send_json_response('error', 'Error fatal en la transacción: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
        }
        break;

        case 'editar_estudiante':
        if ($_SESSION['rol_id'] != 1) {
            send_json_response('error', 'Solo los administradores pueden editar estudiantes.');
            break;
        }
        
        $est_id = intval($_POST['estudiante_id'] ?? 0);
        if (!$est_id) {
            send_json_response('error', 'ID del estudiante no proporcionado.');
        }

        // ========================================
        // 1. Validaciones Básicas
        // ========================================
        $est_cedula = sanitize_input($_POST['est_cedula'] ?? '');
        $est_nombres = sanitize_input($_POST['est_nombres'] ?? '');
        $est_apellidos = sanitize_input($_POST['est_apellidos'] ?? '');
        $est_fecha_nac = sanitize_input($_POST['est_fecha_nac'] ?? '');
        $est_sexo = sanitize_input($_POST['est_sexo'] ?? '');
        $est_vive_con = sanitize_input($_POST['est_vive_con'] ?? '');
        $est_pais = sanitize_input($_POST['est_pais'] ?? '');
        $est_municipio = sanitize_input($_POST['est_municipio'] ?? '');
        $est_parroquia = sanitize_input($_POST['est_parroquia'] ?? '');
        $est_direccion = sanitize_input($_POST['est_direccion'] ?? '');
        
        $est_emergencia_nombre = sanitize_input($_POST['est_emergencia_nombre'] ?? '');
        $est_emergencia_telefono = sanitize_input($_POST['est_emergencia_telefono'] ?? '');
        
        // Datos Académicos (Matrícula)
        $mat_periodo = sanitize_input($_POST['mat_periodo'] ?? '');
        $mat_condicion = sanitize_input($_POST['mat_condicion'] ?? 'Regular');
        $mat_clase = $_POST['mat_clase'] ?? '';
        
        $clase = json_decode($mat_clase, true);
        if (!$est_cedula || !$est_nombres || !$est_apellidos || !$mat_periodo || !$clase || !isset($clase['grado_id'])) {
            send_json_response('error', 'Faltan datos obligatorios del estudiante o asignación.');
        }

        // Representante
        $rep_cedula = sanitize_input($_POST['rep_cedula'] ?? '');
        $rep_nombres = sanitize_input($_POST['rep_nombres'] ?? '');
        $rep_apellidos = sanitize_input($_POST['rep_apellidos'] ?? '');
        $rep_parentesco = sanitize_input($_POST['rep_parentesco'] ?? 'Madre/Padre');
        $rep_telefono = sanitize_input($_POST['rep_telefono'] ?? '');
        
        if (!$rep_cedula || !$rep_nombres || !$rep_apellidos) {
            send_json_response('error', 'Faltan datos obligatorios del representante.');
        }

        try {
            $conn->beginTransaction();

            // ========================================
            // 2. Registro/Actualización del Representante
            // ========================================
            $rep_id = $conn->query("SELECT id FROM representantes WHERE cedula = '$rep_cedula'")->fetchColumn();
            
            if (!$rep_id) {
                $stmt = $conn->prepare("INSERT INTO representantes (cedula, nombres, apellidos, parentesco, telefono, telefono_habitacion, profesion, grado_instruccion, correo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $rep_cedula, $rep_nombres, $rep_apellidos, $rep_parentesco, $rep_telefono,
                    sanitize_input($_POST['rep_telefono_hab'] ?? ''),
                    sanitize_input($_POST['rep_profesion'] ?? ''),
                    sanitize_input($_POST['rep_instruccion'] ?? ''),
                    sanitize_input($_POST['rep_correo'] ?? '')
                ]);
                $rep_id = $conn->lastInsertId();
                // Link them
                $conn->query("INSERT INTO estudiante_representante (estudiante_id, representante_id, es_principal) VALUES ($est_id, $rep_id, 1)");
            } else {
                $stmt = $conn->prepare("UPDATE representantes SET nombres=?, apellidos=?, parentesco=?, telefono=?, telefono_habitacion=?, profesion=?, grado_instruccion=?, correo=? WHERE id=?");
                $stmt->execute([
                    $rep_nombres, $rep_apellidos, $rep_parentesco, $rep_telefono,
                    sanitize_input($_POST['rep_telefono_hab'] ?? ''),
                    sanitize_input($_POST['rep_profesion'] ?? ''),
                    sanitize_input($_POST['rep_instruccion'] ?? ''),
                    sanitize_input($_POST['rep_correo'] ?? ''),
                    $rep_id
                ]);
                
                // Asegurar link principal
                $link_existe = $conn->query("SELECT id FROM estudiante_representante WHERE estudiante_id=$est_id AND representante_id=$rep_id")->fetchColumn();
                if (!$link_existe) {
                    $conn->query("UPDATE estudiante_representante SET es_principal=0 WHERE estudiante_id=$est_id");
                    $conn->query("INSERT INTO estudiante_representante (estudiante_id, representante_id, es_principal) VALUES ($est_id, $rep_id, 1)");
                } else {
                    $conn->query("UPDATE estudiante_representante SET es_principal=0 WHERE estudiante_id=$est_id");
                    $conn->query("UPDATE estudiante_representante SET es_principal=1 WHERE estudiante_id=$est_id AND representante_id=$rep_id");
                }
            }

            // ========================================
            // 3. Actualización del Estudiante
            // ========================================
            $est_existe = $conn->query("SELECT id FROM estudiantes WHERE cedula_escolar = '$est_cedula' AND id != $est_id")->fetchColumn();
            if ($est_existe) {
                $conn->rollBack();
                send_json_response('error', 'Ya existe OTRO estudiante con la cédula escolar ingresada.');
            }

            $stmt = $conn->prepare("UPDATE estudiantes SET cedula_escolar=?, nombres=?, apellidos=?, fecha_nacimiento=?, sexo=?, vive_con=?, pais_entidad=?, municipio=?, parroquia=?, direccion=?, representante_emergencia=?, telefono_emergencia=?, nivel_id=?, representante_legal_id=?, turno_id=? WHERE id=?");
            $stmt->execute([
                $est_cedula, $est_nombres, $est_apellidos, $est_fecha_nac, $est_sexo, $est_vive_con,
                $est_pais, $est_municipio, $est_parroquia, $est_direccion,
                $est_emergencia_nombre, $est_emergencia_telefono,
                $clase['nivel_id'], $rep_id, $clase['turno_id'],
                $est_id
            ]);

            // ========================================
            // 5. Datos Médicos y Antropométricos
            // ========================================
            $stmt = $conn->prepare("UPDATE estudiante_antropometricos SET edad=?, peso=?, estatura=?, talla_camisa=?, talla_pantalon=?, talla_zapato=? WHERE estudiante_id=?");
            $stmt->execute([
                sanitize_input($_POST['antro_edad'] ?? null) ?: null,
                sanitize_input($_POST['antro_peso'] ?? null) ?: null,
                sanitize_input($_POST['antro_estatura'] ?? null) ?: null,
                sanitize_input($_POST['antro_camisa'] ?? ''),
                sanitize_input($_POST['antro_pantalon'] ?? ''),
                sanitize_input($_POST['antro_zapato'] ?? ''),
                $est_id
            ]);

            $stmt = $conn->prepare("UPDATE estudiante_antecedentes_medicos SET
                enfermedad_embarazo=?, como_fue_parto=?, 
                es_alergico=?, alergico_a=?, 
                toma_medicamento=?, cuales_medicamentos=?, 
                condicion_neurodivergente=?, cual_condicion=?, 
                tiene_informe=?, especialidad_informe=?, 
                ha_sido_operado=?, operacion_de_que=?, 
                tiene_patologia=?, cual_patologia=?
                WHERE estudiante_id=?");
            $stmt->execute([
                sanitize_input($_POST['med_embarazo'] ?? ''),
                sanitize_input($_POST['med_parto'] ?? ''),
                (sanitize_input($_POST['med_alergias'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_alergias_txt'] ?? ''),
                (sanitize_input($_POST['med_medicamentos'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_medicamentos_txt'] ?? ''),
                (sanitize_input($_POST['med_neurodivergente'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_neurodivergente_txt'] ?? ''),
                (sanitize_input($_POST['med_informe'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_informe_txt'] ?? ''),
                (sanitize_input($_POST['med_operaciones'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_operaciones_txt'] ?? ''),
                (sanitize_input($_POST['med_patologia'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['med_patologia_txt'] ?? ''),
                $est_id
            ]);

            // ========================================
            // 5.5. Vacunas y Sociales
            // ========================================
            $stmt = $conn->prepare("UPDATE estudiante_vacunas SET triple=?, antivariolica=?, sarampion=?, rubeola=?, antitifica=?, antiamarilica=?, bgc=?, polio=?, otras_vacunas=? WHERE estudiante_id=?");
            $stmt->execute([
                (sanitize_input($_POST['vac_triple'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_antivariolica'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_sarampion'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_rubeola'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_antitifica'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_antiamarilica'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_bgc'] ?? 'No') === 'Si') ? 1 : 0,
                (sanitize_input($_POST['vac_polio'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['vac_otras'] ?? ''),
                $est_id
            ]);

            $stmt = $conn->prepare("UPDATE estudiante_sociales SET documento_legal_representante=?, organismo_documento=?, situacion_pareja=?, viven_juntos=?, con_quien_vive=?, nombres_con_quien_vive=?, parentesco_con_quien_vive=?, numero_hermanos=?, lugar_ocupa=?, tipo_vivienda=?, tenencia_vivienda=? WHERE estudiante_id=?");
            $stmt->execute([
                (sanitize_input($_POST['soc_doc_legal'] ?? 'No') === 'Si') ? 1 : 0,
                sanitize_input($_POST['soc_organismo'] ?? ''),
                sanitize_input($_POST['soc_pareja'] ?? ''),
                (sanitize_input($_POST['soc_viven_juntos'] ?? 'Si') === 'Si') ? 1 : 0,
                sanitize_input($_POST['soc_parentesco_vive'] ?? ''),
                sanitize_input($_POST['soc_con_quien'] ?? ''),
                sanitize_input($_POST['soc_parentesco_vive'] ?? ''),
                intval($_POST['soc_hermanos'] ?? 0),
                intval($_POST['soc_lugar_hermanos'] ?? 1),
                sanitize_input($_POST['soc_tipo_vivienda'] ?? ''),
                sanitize_input($_POST['soc_tenencia'] ?? ''),
                $est_id
            ]);

            // ========================================
            // 6. Preescolar Exclusivo
            // ========================================
            if (sanitize_input($_POST['es_preescolar'] ?? '0') === '1') {
                $existe_pre = $conn->query("SELECT estudiante_id FROM estudiante_preescolar WHERE estudiante_id=$est_id")->fetchColumn();
                if ($existe_pre) {
                    $stmt = $conn->prepare("UPDATE estudiante_preescolar SET 
                        quien_cuida=?, actividades_preferidas=?, con_quien_juega=?, 
                        estado_guarderia=?, otro_prescolar=?, cual_prescolar=?, 
                        dificultad_observada=?, tipo_dificultad=?, 
                        consulta_psicologo=?, consulta_psicopedagogo=?, consulta_neurologo=?, consulta_terapeuta_lenguaje=?
                        WHERE estudiante_id=?");
                    $stmt->execute([
                        sanitize_input($_POST['pre_quien_cuida'] ?? ''),
                        sanitize_input($_POST['pre_actividades_prefiere'] ?? ''),
                        sanitize_input($_POST['pre_con_quien_juega'] ?? ''),
                        (sanitize_input($_POST['pre_estado_guarderia'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_otro_preescolar'] ?? 'No') === 'Si' ? 1 : 0),
                        sanitize_input($_POST['pre_cual_preescolar_txt'] ?? ''),
                        (sanitize_input($_POST['pre_dificultad_observada'] ?? 'No') === 'Si' ? 1 : 0),
                        sanitize_input($_POST['pre_dificultad_tipo_txt'] ?? ''),
                        (sanitize_input($_POST['pre_consulta_psicologo'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_consulta_psicopedagogo'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_consulta_neurologo'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_consulta_terapeuta'] ?? 'No') === 'Si' ? 1 : 0),
                        $est_id
                    ]);
                } else {
                    $stmt = $conn->prepare("INSERT INTO estudiante_preescolar (
                        estudiante_id, quien_cuida, actividades_preferidas, con_quien_juega, 
                        estado_guarderia, otro_prescolar, cual_prescolar, 
                        dificultad_observada, tipo_dificultad, 
                        consulta_psicologo, consulta_psicopedagogo, consulta_neurologo, consulta_terapeuta_lenguaje
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $est_id,
                        sanitize_input($_POST['pre_quien_cuida'] ?? ''),
                        sanitize_input($_POST['pre_actividades_prefiere'] ?? ''),
                        sanitize_input($_POST['pre_con_quien_juega'] ?? ''),
                        (sanitize_input($_POST['pre_estado_guarderia'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_otro_preescolar'] ?? 'No') === 'Si' ? 1 : 0),
                        sanitize_input($_POST['pre_cual_preescolar_txt'] ?? ''),
                        (sanitize_input($_POST['pre_dificultad_observada'] ?? 'No') === 'Si' ? 1 : 0),
                        sanitize_input($_POST['pre_dificultad_tipo_txt'] ?? ''),
                        (sanitize_input($_POST['pre_consulta_psicologo'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_consulta_psicopedagogo'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_consulta_neurologo'] ?? 'No') === 'Si' ? 1 : 0),
                        (sanitize_input($_POST['pre_consulta_terapeuta'] ?? 'No') === 'Si' ? 1 : 0)
                    ]);
                }
            } else {
                $conn->query("DELETE FROM estudiante_preescolar WHERE estudiante_id=$est_id");
            }

            // ========================================
            // 7. Actualizar Matrícula (Inscripción Académica)
            // ========================================
            $grado_id = $clase['grado_id'];
            $seccion_id = $clase['seccion_id'];
            
            // Verificamos si existe matrícula para ese periodo
            $mat_id = $conn->query("SELECT id FROM matriculas WHERE estudiante_id=$est_id AND periodo_id=$mat_periodo")->fetchColumn();
            
            if ($mat_id) {
                $stmt = $conn->prepare("UPDATE matriculas SET grado_id=?, seccion_id=?, condicion=?, turno_id=? WHERE id=?");
                $stmt->execute([$grado_id, $seccion_id, $mat_condicion, $clase['turno_id'], $mat_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO matriculas (estudiante_id, periodo_id, grado_id, seccion_id, condicion, turno_id, estado, fecha_inscripcion) VALUES (?, ?, ?, ?, ?, ?, 'Inscrito', CURRENT_DATE())");
                $stmt->execute([$est_id, $mat_periodo, $grado_id, $seccion_id, $mat_condicion, $clase['turno_id']]);
            }

            registrar_log($conn, $_SESSION['usuario_id'] ?? null, 'Edición Estudiante', "Se actualizaron los datos del estudiante $est_nombres $est_apellidos");

            $conn->commit();
            send_json_response('success', 'Datos del estudiante actualizados exitosamente.');

        } catch (Throwable $e) {
            $conn->rollBack();
            send_json_response('error', 'Error fatal en la actualización: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
        }
        break;

    case 'stats_matricula':
        try {
            $total = $conn->query("SELECT COUNT(*) FROM matriculas WHERE estado = 'Inscrito'")->fetchColumn();
            
            // Distribución por Turno (1: Mañana, 2: Tarde usualmente, usamos nombres)
            $turnos_data = $conn->query("
                SELECT t.nombre, COUNT(m.estudiante_id) as cantidad
                FROM matriculas m
                JOIN turnos t ON m.turno_id = t.id
                WHERE m.estado = 'Inscrito'
                GROUP BY t.id
            ")->fetchAll();

            $etapas_data = $conn->query("
                SELECT n.nombre, COUNT(m.estudiante_id) as cantidad
                FROM matriculas m
                JOIN grados g ON m.grado_id = g.id
                JOIN niveles n ON g.nivel_id = n.id
                WHERE m.estado = 'Inscrito'
                GROUP BY n.id
            ")->fetchAll();

            send_json_response('success', 'Stats cargadas', [
                'total' => $total,
                'turnos' => $turnos_data,
                'etapas' => $etapas_data
            ]);
        } catch(Throwable $e) {
            send_json_response('error', 'Error cargando stats');
        }
        break;

    case 'obtener_detalles':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$id) send_json_response('error', 'ID no válido');
        try {
            $estudiante = $conn->query("
                SELECT e.*, 
                       TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad,
                       m.condicion, m.estado, m.fecha_inscripcion, m.grado_id, m.seccion_id, m.turno_id, m.periodo_id,
                       g.nombre AS grado, s.nombre AS seccion, t.nombre AS turno, n.nombre AS nivel
                FROM estudiantes e
                JOIN matriculas m ON e.id = m.estudiante_id
                JOIN grados g ON m.grado_id = g.id
                JOIN secciones s ON m.seccion_id = s.id
                LEFT JOIN turnos t ON m.turno_id = t.id
                JOIN niveles n ON g.nivel_id = n.id
                WHERE e.id = $id
            ")->fetch();

            if (!$estudiante) send_json_response('error', 'Estudiante no encontrado');

            $representante = $conn->query("
                SELECT r.* 
                FROM representantes r
                JOIN estudiante_representante er ON r.id = er.representante_id
                WHERE er.estudiante_id = $id AND er.es_principal = 1
            ")->fetch();

            $antropometricos = $conn->query("SELECT * FROM estudiante_antropometricos WHERE estudiante_id = $id")->fetch();
            $medicos = $conn->query("SELECT * FROM estudiante_antecedentes_medicos WHERE estudiante_id = $id")->fetch();
            $vacunas = $conn->query("SELECT * FROM estudiante_vacunas WHERE estudiante_id = $id")->fetch();
            $sociales = $conn->query("SELECT * FROM estudiante_sociales WHERE estudiante_id = $id")->fetch();
            $preescolar = $conn->query("SELECT * FROM estudiante_preescolar WHERE estudiante_id = $id")->fetch();

            send_json_response('success', 'Detalles cargados', [
                'estudiante' => $estudiante,
                'representante' => $representante,
                'antropometricos' => $antropometricos,
                'medicos' => $medicos,
                'vacunas' => $vacunas,
                'sociales' => $sociales,
                'preescolar' => $preescolar
            ]);
        } catch(Throwable $e) {
            send_json_response('error', 'Error cargando detalles');
        }
        break;

    case 'listar':
    default:
        $periodo_id = $_POST['periodo_id'] ?? $_GET['periodo_id'] ?? null;
        if (!$periodo_id) {
            $periodo_id = $conn->query("SELECT id FROM periodos_escolares WHERE estado = 'Abierto' ORDER BY id DESC LIMIT 1")->fetchColumn();
        }

        $query = "
            SELECT e.id, e.cedula_escolar, e.nombres, e.apellidos, m.estado, m.condicion,
                   TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad,
                   g.nombre AS grado, s.nombre AS seccion, t.nombre AS turno, n.nombre AS nivel,
                   r.nombres AS rep_nombres, r.apellidos AS rep_apellidos
            FROM estudiantes e
            JOIN matriculas m ON e.id = m.estudiante_id
            JOIN grados g ON m.grado_id = g.id
            JOIN niveles n ON g.nivel_id = n.id
            JOIN secciones s ON m.seccion_id = s.id
            LEFT JOIN turnos t ON m.turno_id = t.id
            JOIN estudiante_representante er ON e.id = er.estudiante_id
            JOIN representantes r ON er.representante_id = r.id
            WHERE m.estado = 'Inscrito' AND er.es_principal = 1
              AND m.periodo_id = :periodo_id
            ORDER BY e.apellidos, e.nombres
        ";
        $stmt = $conn->prepare($query);
        $stmt->execute([':periodo_id' => $periodo_id]);
        $estudiantes = $stmt->fetchAll();
        send_json_response('success', 'Lista cargada', $estudiantes);
        break;
}
?>
