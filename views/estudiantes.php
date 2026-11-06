<div class="header">
    <h1><i class="fas fa-user-graduate"></i> Módulo de Estudiantes</h1>
    <p>Gestión completa de inscripciones, representantes y matrícula escolar.</p>
</div>

<div class="tabs">
    <?php if($_SESSION['rol_id'] == 1): ?>
    <button class="tab-btn active" onclick="openTabEstudiante('tab-inscripcion', this)"><i class="fas fa-user-plus"></i> Nueva Inscripción</button>
    <button class="tab-btn" onclick="openTabEstudiante('tab-lista', this)"><i class="fas fa-users"></i> Matrícula Escolar</button>
    <button class="tab-btn" onclick="openTabEstudiante('tab-representantes', this)"><i class="fas fa-users-cog"></i> Listado de Representantes</button>
    <?php else: ?>
    <button class="tab-btn active" onclick="openTabEstudiante('tab-lista', this)"><i class="fas fa-users"></i> Matrícula Escolar</button>
    <button class="tab-btn" onclick="openTabEstudiante('tab-representantes', this)"><i class="fas fa-users-cog"></i> Listado de Representantes</button>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:0; border-top-left-radius:0;">
    
    <?php if($_SESSION['rol_id'] == 1): ?>
    <!-- PESTAÑA: NUEVA INSCRIPCIÓN -->
    <div id="tab-inscripcion" class="tab-content active">
        <div id="estudiantesAlert" class="alert" style="display:none;"></div>

        <!-- SELECCION DE NIVEL (PREESCOLAR O PRIMARIA) -->
        <div id="seleccion-nivel" style="text-align:center; padding: 4rem 2rem;">
            <h2 style="margin-bottom:2rem; color:var(--primary);"><i class="fas fa-layer-group"></i> Seleccione el Nivel de Ingreso</h2>
            <div style="display:flex; justify-content:center; gap:3rem; flex-wrap:wrap;">
                <button type="button" onclick="iniciarInscripcion('Preescolar')" style="padding:2.5rem; border-radius:16px; background:var(--bg-color); border:3px solid var(--border); font-size:1.5rem; cursor:pointer; width:280px; transition:all 0.3s ease; color:var(--text-main);" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)';">
                    <i class="fas fa-shapes fa-3x" style="margin-bottom:1rem; display:block; color:#F59E0B;"></i>
                    <strong>Preescolar</strong>
                    <div style="font-size:0.9rem; color:#64748B; margin-top:0.5rem; font-weight:normal;">Registro con evaluación psicosocial</div>
                </button>
                <button type="button" onclick="iniciarInscripcion('Primaria')" style="padding:2.5rem; border-radius:16px; background:var(--bg-color); border:3px solid var(--border); font-size:1.5rem; cursor:pointer; width:280px; transition:all 0.3s ease; color:var(--text-main);" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)';">
                    <i class="fas fa-book-reader fa-3x" style="margin-bottom:1rem; display:block; color:#3B82F6;"></i>
                    <strong>Primaria</strong>
                    <div style="font-size:0.9rem; color:#64748B; margin-top:0.5rem; font-weight:normal;">Registro estándar de educación básica</div>
                </button>
            </div>
        </div>

        <div id="wizard-inscripcion" style="display:none;">
            <button type="button" class="btn btn--outline" style="margin-bottom: 1.5rem;" onclick="cancelarInscripcion()"><i class="fas fa-arrow-left"></i> Cambiar Nivel</button>
            
            <!-- Progress Tracker -->
        <div style="display:flex; justify-content:space-between; margin-bottom: 2rem; border-bottom: 2px solid var(--border); padding-bottom:1rem;">
            <div id="step1-indicator" style="color:var(--primary); font-weight:600;"><i class="fas fa-1"></i> Estudiante</div>
            <div id="step2-indicator" style="color:#94A3B8;"><i class="fas fa-2"></i> Médicos y Antropo.</div>
            <div id="step-pre-indicator" style="color:#94A3B8; display:none;"><i class="fas fa-star"></i> Preescolar</div>
            <div id="step3-indicator" style="color:#94A3B8;"><i class="fas fa-3"></i> Representante</div>
            <div id="step4-indicator" style="color:#94A3B8;"><i class="fas fa-4"></i> Académico</div>
        </div>

        <form id="formInscripcion" onsubmit="event.preventDefault();">
            <input type="hidden" name="action" id="form_action" value="registrar_estudiante">
            <input type="hidden" name="estudiante_id" id="estudiante_id" value="">
            <input type="hidden" name="es_preescolar" id="es_preescolar" value="0">
            
            <!-- PASO 1: DATOS DEL ESTUDIANTE -->
            <div id="step1-content">
                <h3 style="margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-id-card"></i> Datos Personales del Estudiante</h3>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Cédula Escolar</label>
                        <input type="text" name="est_cedula" class="form-input" required placeholder="Ej: V12345678901" maxlength="13" oninput="let v = this.value.replace(/[^0-9]/g, ''); this.value = v ? 'V' + v.substring(0,12) : '';">
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="est_nombres" class="form-input" required maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="est_apellidos" class="form-input" required maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="est_fecha_nac" class="form-input" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">Sexo</label>
                        <select name="est_sexo" class="form-input" required>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">País / Entidad</label>
                        <input type="text" name="est_pais" class="form-input" placeholder="Ej: Venezuela / Carabobo" maxlength="100" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Municipio</label>
                        <input type="text" name="est_municipio" class="form-input" maxlength="50" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Parroquia</label>
                        <input type="text" name="est_parroquia" class="form-input" maxlength="50" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Dirección Exacta</label>
                    <input type="text" name="est_direccion" class="form-input" maxlength="250" required>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">¿Con quién vive?</label>
                        <input type="text" name="est_vive_con" class="form-input" placeholder="Ej: Mamá y Abuela" maxlength="50" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">En caso de emergencia llamar a:</label>
                        <input type="text" name="est_emergencia_nombre" class="form-input" placeholder="Nombre de contacto" maxlength="50" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Teléfono de contacto de emergencia</label>
                        <input type="text" name="est_emergencia_telefono" class="form-input" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                </div>

                <div style="text-align:right; margin-top:1.5rem;">
                    <button type="button" class="btn btn--primary" onclick="if(validarPasoActual()) irPaso(2)">Siguiente <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- PASO 2: MÉDICOS Y ANTROPOMÉTRICOS -->
            <div id="step2-content" style="display:none;">
                <h3 style="margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-ruler"></i> Datos Antropométricos</h3>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:100px;">
                        <label class="form-label">Edad (años)</label>
                        <input type="text" name="antro_edad" class="form-input" maxlength="2" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:100px;">
                        <label class="form-label">Peso (kg)</label>
                        <input type="text" name="antro_peso" class="form-input" maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:100px;">
                        <label class="form-label">Estatura (cm)</label>
                        <input type="text" name="antro_estatura" class="form-input" maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Talla Camisa</label>
                        <input type="text" name="antro_camisa" class="form-input" maxlength="2" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Talla Pantalón</label>
                        <input type="text" name="antro_pantalon" class="form-input" maxlength="2" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Talla Zapato</label>
                        <input type="text" name="antro_zapato" class="form-input" maxlength="2" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                </div>

                <h3 style="margin-top:2rem; margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-heartbeat"></i> Antecedentes Médicos</h3>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">¿Enfermedad en embarazo?</label>
                        <input type="text" name="med_embarazo" class="form-input" placeholder="Detalles o N/A" maxlength="50" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">¿Cómo fue el parto?</label>
                        <input type="text" name="med_parto" class="form-input" placeholder="Normal, Cesárea, etc." maxlength="20" required>
                    </div>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Alergias?</label>
                        <select name="med_alergias" class="form-input" onchange="let e=document.getElementById('med_alergias_txt'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                        <input type="text" name="med_alergias_txt" id="med_alergias_txt" class="form-input" style="display:none; margin-top:0.5rem;" placeholder="Especifique a qué...">
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Medicamentos?</label>
                        <select name="med_medicamentos" class="form-input" onchange="let e=document.getElementById('med_medicamentos_txt'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                        <input type="text" name="med_medicamentos_txt" id="med_medicamentos_txt" class="form-input" style="display:none; margin-top:0.5rem;" placeholder="Especifique cuáles...">
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Neurodivergente?</label>
                        <select name="med_neurodivergente" class="form-input" onchange="let e=document.getElementById('med_neurodivergente_txt'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                        <input type="text" name="med_neurodivergente_txt" id="med_neurodivergente_txt" class="form-input" style="display:none; margin-top:0.5rem;" placeholder="Especifique condición...">
                    </div>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Tiene Informe?</label>
                        <select name="med_informe" class="form-input" onchange="let e=document.getElementById('med_informe_txt'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                        <input type="text" name="med_informe_txt" id="med_informe_txt" class="form-input" style="display:none; margin-top:0.5rem;" placeholder="Especifique especialidad...">
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Operado?</label>
                        <select name="med_operaciones" class="form-input" onchange="let e=document.getElementById('med_operaciones_txt'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                        <input type="text" name="med_operaciones_txt" id="med_operaciones_txt" class="form-input" style="display:none; margin-top:0.5rem;" placeholder="Especifique de qué...">
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Alguna Patología?</label>
                        <select name="med_patologia" class="form-input" onchange="let e=document.getElementById('med_patologia_txt'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                        <input type="text" name="med_patologia_txt" id="med_patologia_txt" class="form-input" style="display:none; margin-top:0.5rem;" placeholder="Especifique cuál...">
                    </div>
                </div>

                <h3 style="margin-top:2rem; margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-syringe"></i> Vacunas</h3>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Triple</label>
                        <select name="vac_triple" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Antivariólica</label>
                        <select name="vac_antivariolica" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Sarampión</label>
                        <select name="vac_sarampion" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Rubeola</label>
                        <select name="vac_rubeola" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Antitífica</label>
                        <select name="vac_antitifica" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Antiamarílica</label>
                        <select name="vac_antiamarilica" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">BGC</label>
                        <select name="vac_bgc" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Polio</label>
                        <select name="vac_polio" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label">Otras Vacunas (Especifique cuáles o escriba N/A)</label>
                    <input type="text" name="vac_otras" class="form-input" placeholder="Ej: COVID-19, Influenza..." maxlength="20" required>
                </div>

                <div style="display:flex; justify-content:space-between; margin-top:1.5rem;">
                    <button type="button" class="btn btn--outline" onclick="irPaso(1)"><i class="fas fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn btn--primary" onclick="if(validarPasoActual()) irPaso('siguiente-despues-medico')">Siguiente <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- PASO PREESCOLAR (Oculto dinámicamente) -->
            <div id="step-pre-content" style="display:none;">
                <h3 style="margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-child"></i> Datos Exploratorios de Preescolar</h3>
                
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-bottom:1rem;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Cuando el(la) niño(a) no está en el preescolar, ¿Quién lo(a) cuida?</label>
                        <input type="text" name="pre_quien_cuida" class="form-input" placeholder="Ej: Abuela, Madre, Niñera..." maxlength="20" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Tipo de actividades que prefiere</label>
                        <input type="text" name="pre_actividades_prefiere" class="form-input" placeholder="Ej: Dibujar, Jugar con bloques..." maxlength="50" required>
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-bottom:1rem;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">¿Con quién juega?</label>
                        <input type="text" name="pre_con_quien_juega" class="form-input" placeholder="Ej: Primos, Hermanos, Vecinos..." maxlength="20" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Ha estado en guardería?</label>
                        <select name="pre_estado_guarderia" class="form-input" onchange="let e=document.getElementById('pre_guarderia_nombre'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:2; min-width:250px; display:none;" id="pre_guarderia_nombre">
                        <label class="form-label">¿En cuál guardería?</label>
                        <input type="text" name="pre_guarderia_nombre_txt" class="form-input" placeholder="Nombre de la guardería">
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-bottom:1rem;">
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Ha estado en otro preescolar?</label>
                        <select name="pre_otro_preescolar" class="form-input" onchange="let e=document.getElementById('pre_cual_preescolar'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:2; min-width:250px; display:none;" id="pre_cual_preescolar">
                        <label class="form-label">¿Cuál preescolar?</label>
                        <input type="text" name="pre_cual_preescolar_txt" class="form-input" placeholder="Nombre del otro preescolar">
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-bottom:1rem; background:var(--warning-bg); padding:1rem; border-radius:8px; border:1px solid var(--warning-border);">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">¿Ha observado alguna dificultad con su hijo(a)?</label>
                        <select name="pre_dificultad_observada" class="form-input" onchange="let e=document.getElementById('pre_dificultad_tipo'); e.style.display = this.value === 'Si' ? 'block' : 'none'; e.required = this.value === 'Si';" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:2; min-width:250px; display:none;" id="pre_dificultad_tipo">
                        <label class="form-label">Especifique de qué tipo sería (Puede seleccionar varias)</label>
                        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:0.5rem;" id="dificultades_checkboxes">
                            <label style="display:flex; align-items:center; gap:0.25rem;"><input type="checkbox" value="Lenguaje" onchange="updateDificultades()"> Lenguaje</label>
                            <label style="display:flex; align-items:center; gap:0.25rem;"><input type="checkbox" value="Auditiva" onchange="updateDificultades()"> Auditiva</label>
                            <label style="display:flex; align-items:center; gap:0.25rem;"><input type="checkbox" value="Física" onchange="updateDificultades()"> Física</label>
                            <label style="display:flex; align-items:center; gap:0.25rem;"><input type="checkbox" value="Visual" onchange="updateDificultades()"> Visual</label>
                            <label style="display:flex; align-items:center; gap:0.25rem;"><input type="checkbox" value="Otro" onchange="updateDificultades()"> Otro</label>
                        </div>
                        <input type="hidden" name="pre_dificultad_tipo_txt" id="pre_dificultad_tipo_hidden">
                        <script>
                            function updateDificultades() {
                                const checkboxes = document.querySelectorAll('#dificultades_checkboxes input[type="checkbox"]:checked');
                                const vals = Array.from(checkboxes).map(cb => cb.value);
                                document.getElementById('pre_dificultad_tipo_hidden').value = vals.join(', ');
                            }
                        </script>
                    </div>
                </div>

                <h4 style="margin-bottom:1rem; color:var(--text-muted);"><i class="fas fa-user-md"></i> Consultas Previas (Marque Sí/No)</h4>
                <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">Psicólogo</label>
                        <select name="pre_consulta_psicologo" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">Psicopedagogo</label>
                        <select name="pre_consulta_psicopedagogo" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">Neurólogo</label>
                        <select name="pre_consulta_neurologo" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">Ter. Lenguaje</label>
                        <select name="pre_consulta_terapeuta" class="form-input" required><option value="No">No</option><option value="Si">Sí</option></select>
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between; margin-top:1.5rem;">
                    <button type="button" class="btn btn--outline" onclick="irPaso(2)"><i class="fas fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn btn--primary" onclick="if(validarPasoActual()) irPaso(3)">Siguiente <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- PASO 3: REPRESENTANTE -->
            <div id="step3-content" style="display:none;">
                <h3 style="margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-user-tie"></i> Datos del Representante</h3>
                
                <div style="background: var(--bg-color); padding: 1rem; border-radius:8px; border: 1px solid var(--border); margin-bottom: 1rem;">
                    <label class="form-label">Buscar Representante Existente</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="text" id="buscar_cedula_rep" class="form-input" placeholder="Cédula del Representante..." style="max-width:300px;" >
                        <button type="button" class="btn btn--primary" onclick="buscarRepresentante()"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                    <small style="color:var(--text-light); margin-top:0.5rem; display:block;">Si el representante ya existe, sus datos se autocompletarán.</small>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Cédula</label>
                        <input type="text" id="rep_cedula" name="rep_cedula" class="form-input" required maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Nombres</label>
                        <input type="text" id="rep_nombres" name="rep_nombres" class="form-input" required maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Apellidos</label>
                        <input type="text" id="rep_apellidos" name="rep_apellidos" class="form-input" required maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Parentesco</label>
                        <select id="rep_parentesco" name="rep_parentesco" class="form-input" required>
                            <option value="Madre/Padre">Madre/Padre</option>
                            <option value="Abuelo/a">Abuelo/a</option>
                            <option value="Tío/a">Tío/a</option>
                            <option value="Hermano/a">Hermano/a</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Teléfono Móvil</label>
                        <input type="text" id="rep_telefono" name="rep_telefono" class="form-input" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Teléfono Habitación</label>
                        <input type="text" id="rep_telefono_hab" name="rep_telefono_hab" class="form-input" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Profesión u Oficio</label>
                        <input type="text" id="rep_profesion" name="rep_profesion" class="form-input" maxlength="30" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Grado de Instrucción</label>
                        <input type="text" id="rep_instruccion" name="rep_instruccion" class="form-input" placeholder="Ej: Bachiller, Universitario" maxlength="30" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" id="rep_correo" name="rep_correo" class="form-input" maxlength="100" required>
                    </div>
                </div>

                <h3 style="margin-top:2rem; margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-home"></i> Datos Legales y Familiares</h3>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">¿Cuenta con documento legal del niño(a)?</label>
                        <select name="soc_doc_legal" class="form-input" required>
                            <option value="No">No (Soy Representante Legal / No poseo)</option>
                            <option value="Si">Sí (Poseo documento)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">¿De cuál organismo?</label>
                        <input type="text" name="soc_organismo" class="form-input" placeholder="Ej: Tribunal de Protección">
                    </div>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Situación de la pareja</label>
                        <input type="text" name="soc_pareja" class="form-input" placeholder="Ej: Casados, Divorciados, Soltero" maxlength="20" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:150px;">
                        <label class="form-label">¿Viven Juntos?</label>
                        <select name="soc_viven_juntos" class="form-input" required>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">¿Con quién vive el (la) niño (a)? (Nombres)</label>
                        <input type="text" name="soc_con_quien" class="form-input" placeholder="Ej: Maria Perez, Juan Perez" maxlength="50" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Parentesco (de con quién vive)</label>
                        <input type="text" name="soc_parentesco_vive" class="form-input" placeholder="Ej: Abuelos" maxlength="20" required>
                    </div>
                </div>
                                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top: 1rem;">
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Número de Hermanos</label>
                        <input type="number" name="soc_hermanos" class="form-input" value="0" min="0" max="20" oninput="this.value = this.value.slice(0, 2)" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <label class="form-label">Lugar que ocupa</label>
                        <input type="number" name="soc_lugar_hermanos" class="form-input" placeholder="Ej: 1" min="1" max="20" oninput="this.value = this.value.slice(0, 2)" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:180px;">
                        <label class="form-label">Tipo de Vivienda</label>
                        <select name="soc_tipo_vivienda" class="form-input" required>
                            <option value="Casa">Casa</option>
                            <option value="Apartamento">Apartamento</option>
                            <option value="Rancho">Rancho</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:180px;">
                        <label class="form-label">Tenencia de Vivienda</label>
                        <select name="soc_tenencia" class="form-input" required>
                            <option value="Propia">Propia</option>
                            <option value="Alquilada">Alquilada</option>
                            <option value="Prestada">Prestada</option>
                            <option value="Hipotecada">Hipotecada</option>
                            <option value="Al cuido">Al cuido</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; margin-top:1.5rem;">
                    <button type="button" class="btn btn--outline" onclick="irPaso(2)"><i class="fas fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn btn--primary" onclick="if(validarPasoActual()) irPaso(4)">Siguiente <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- PASO 4: ACADÉMICO -->
            <div id="step4-content" style="display:none;">
                <h3 style="margin-bottom:1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;"><i class="fas fa-school"></i> Datos Académicos y Asignación</h3>
                
                <div style="display:flex; flex-wrap:wrap; gap:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Período Escolar</label>
                        <select name="mat_periodo" id="mat_periodo" class="form-input" required>
                            <!-- Llenado dinámico JS -->
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Condición</label>
                        <select name="mat_condicion" class="form-input" required>
                            <option value="Nuevo Ingreso">Nuevo Ingreso</option>
                            <option value="Regular">Regular</option>
                            <option value="Repitiente">Repitiente</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:1.5rem; margin-top:1.5rem; padding: 1.5rem; background: var(--warning-bg); border: 1px solid var(--warning-border); border-radius: 8px;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Etapa Educativa</label>
                        <select id="mat_etapa" name="mat_etapa" class="form-input" onchange="actualizarClasesDisponibles()" required>
                            <option value="">Seleccione Etapa...</option>
                            <option value="Preescolar">Preescolar</option>
                            <option value="Primaria">Primaria</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:2; min-width:250px;">
                        <label class="form-label">Asignar a Clase (Turno, Grado y Sección)</label>
                        <select name="mat_clase" id="mat_clase" class="form-input" required>
                            <option value="">Seleccione primero la etapa...</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; margin-top:2rem;">
                    <button type="button" class="btn btn--outline" onclick="irPaso(3)"><i class="fas fa-arrow-left"></i> Atrás</button>
                    <button type="submit" class="btn btn--primary" id="btnGuardarInscripcion" style="font-size: 1.1rem; padding: 0.8rem 2rem;"><i class="fas fa-save" style="margin-right: 8px;"></i><span id="txtBtnGuardar">Procesar Inscripción Completa</span></button>
                </div>
            </div>
        </form>
    </div>
    </div>
    <?php endif; ?>

    <!-- PESTAÑA: LISTA DE ESTUDIANTES -->
    <!-- TAB LISTADO DE REPRESENTANTES -->
<div id="tab-representantes" class="tab-content">
    
    <!-- Tarjetas de KPIs Representantes -->
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <div style="flex:1; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-users"></i> Total de Representantes</div>
            <div style="font-size: 2.5rem; font-weight: 700; margin-top:0.5rem;" id="kpi-total-rep">0</div>
        </div>
        <div style="flex:1; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-child"></i> Rep. con Múltiples Estudiantes</div>
            <div style="font-size: 2.5rem; font-weight: 700; margin-top:0.5rem;" id="kpi-multiples-rep">0</div>
        </div>
    </div>
    
    <!-- Barra de Búsqueda y Filtros -->
    <div style="display:flex; gap:1rem; margin-bottom: 1.5rem; background: var(--bg-color); padding: 1rem; border-radius:8px; align-items:flex-end;">
        <div style="flex:2;">
            <label class="form-label" style="font-size:0.85rem;"><i class="fas fa-search"></i> Buscar Representante</label>
            <input type="text" id="filtro_busqueda_rep" class="form-input" placeholder="Nombre, apellido o cédula..." onkeyup="filtrarRepresentantes()">
          </div>
          <div style="flex:1;">
              <label class="form-label" style="font-size:0.85rem;"><i class="fas fa-calendar-alt"></i> Periodo Escolar</label>
              <select id="filtro_periodo_rep" class="form-input" onchange="cargarRepresentantes()"><option value="">Cargando...</option></select>
          </div>
        <div style="flex:1; display:flex; gap:0.5rem; align-items:flex-end; justify-content:flex-end;">
            <button class="btn btn--primary" onclick="filtrarRepresentantes()"><i class="fas fa-filter"></i> Filtrar</button>
            <button type="button" class="btn btn--outline" onclick="imprimirRepresentantes()"><i class="fas fa-print"></i> Imprimir Directorio</button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table" id="tablaRepresentantes">
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombres y Apellidos</th>
                    <th>Teléfono</th>
                    <th>Correo Electrónico</th>
                    <th>Estudiante(s) Asociado(s)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="listaRepresentantes">
                <tr><td colspan="5" style="text-align:center;">Cargando representantes...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div id="tab-lista" class="tab-content <?= $_SESSION['rol_id'] == 1 ? '' : 'active' ?>">
        
        <!-- Tarjetas de KPIs -->
        <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
            <div style="flex:1; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-users"></i> Matrícula Total</div>
                <div style="font-size: 2.5rem; font-weight: 700; margin-top:0.5rem;" id="kpi-total">0</div>
            </div>
            <div style="flex:1; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-sun"></i> Turnos (Mañana/Tarde)</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top:0.5rem; line-height:1.2;" id="kpi-turnos">Cargando...</div>
            </div>
            <div style="flex:1; background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-shapes"></i> Preescolar/Primaria</div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-top:0.5rem; line-height:1.2;" id="kpi-etapas">Cargando...</div>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <!-- Barra de Búsqueda y Filtros -->
        <div style="display:flex; gap:1rem; margin-bottom: 1.5rem; background: var(--bg-color); padding: 1rem; border-radius:8px; align-items:flex-end; flex-wrap: wrap;">
            <div style="flex:2; min-width: 200px;">
                <label class="form-label" style="font-size:0.85rem;"><i class="fas fa-search"></i> Buscar Estudiante</label>
                <input type="text" id="filtro_busqueda" class="form-input" placeholder="Nombre, apellido o cédula..." onkeyup="filtrarEstudiantes()" required>
            </div>
            <div style="flex:1;">
                <label class="form-label" style="font-size:0.85rem;"><i class="fas fa-calendar-alt"></i> Periodo Escolar</label>
                <select id="filtro_periodo_est" class="form-input" onchange="cargarEstudiantes()"><option value="">Cargando...</option></select>
            </div>
            <div style="flex:1;">
                <label class="form-label" style="font-size:0.85rem;">Nivel</label>
                <select id="filtro_nivel" class="form-input" onchange="filtrarEstudiantes()">
                    <option value="">Todos</option>
                    <option value="Preescolar">Preescolar</option>
                    <option value="Primaria">Primaria</option>
                </select>
            </div>
            <div style="flex:1;">
                <label class="form-label" style="font-size:0.85rem;">Turno</label>
                <select id="filtro_turno" class="form-input" onchange="filtrarEstudiantes()">
                    <option value="">Todos</option>
                    <option value="Mañana">Mañana</option>
                    <option value="Tarde">Tarde</option>
                </select>
            </div>
            <div style="flex:1;">
                <label class="form-label" style="font-size:0.85rem;">Condición</label>
                <select id="filtro_condicion" class="form-input" onchange="filtrarEstudiantes()">
                    <option value="">Todas</option>
                    <option value="Nuevo Ingreso">Nuevo Ingreso</option>
                    <option value="Regular">Regular</option>
                    <option value="Repitiente">Repitiente</option>
                </select>
            </div>
            <div>
                <button type="button" class="btn btn--outline" onclick="imprimirLista()"><i class="fas fa-print"></i> Imprimir</button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table" id="tabla_matricula">
                <thead>
                    <tr>
                        <th>Cédula Esc.</th>
                        <th>Estudiante</th>
                        <th>Edad</th>
                        <th>Grado y Sección</th>
                        <th>Turno</th>
                        <th>Condición</th>
                        <th>Representante</th>
                        <th>Ver Ficha</th>
                    </tr>
                </thead>
                <tbody id="listaEstudiantes">
                    <tr><td colspan="8" style="text-align:center;">Cargando estudiantes...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PANEL LATERAL (Offcanvas) PARA VER DETALLES -->
<div id="panelOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;" onclick="cerrarSidePanel()"></div>
<div id="studentSidePanel" style="position:fixed; top:0; right:-600px; width:600px; max-width:100%; height:100%; background:var(--surface); z-index:1000; box-shadow:-5px 0 15px rgba(0,0,0,0.1); transition: right 0.3s ease; overflow-y:auto; padding:2rem;">
    <button onclick="cerrarSidePanel()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#64748B;"><i class="fas fa-times"></i></button>
    <div id="panelContent">Cargando...</div>
</div>

<script src="public/js/estudiantes.js?v=<?= time() ?>"></script>
