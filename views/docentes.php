<div class="card" style="padding: 0; overflow: hidden;">
    <!-- Pestañas de Navegación -->
    <div class="tabs" style="padding: 1rem 1.5rem 0 1.5rem; margin-bottom: 0; background: var(--bg-color);">
        <?php if($_SESSION['rol_id'] == 1): ?>
        <button class="tab-btn active" onclick="openTabDocente('tab-registro', this)"><i class="fas fa-user-plus"></i> Registro de Docentes</button>
        <button class="tab-btn" onclick="openTabDocente('tab-directorio', this)"><i class="fas fa-address-book"></i> Directorio</button>
        <button class="tab-btn" onclick="openTabDocente('tab-asignacion', this)"><i class="fas fa-chalkboard-teacher"></i> Asignación de Secciones</button>
        <?php else: ?>
        <button class="tab-btn active" onclick="openTabDocente('tab-directorio', this)"><i class="fas fa-address-book"></i> Directorio</button>
        <?php endif; ?>
    </div>

    <div style="padding: 1.5rem;">
        
        <div id="docentesAlert" class="alert" style="display:none;"></div>

        <?php if($_SESSION['rol_id'] == 1): ?>
        <!-- TAB: Registro -->
        <div id="tab-registro" class="tab-content active">
            <h3 class="card-title">Gestión de Docentes</h3>
            <div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap: wrap;">
                
                <form id="formDocente" style="flex:1; min-width:300px; background:var(--bg-color); padding:1.5rem; border-radius:8px; border:1px solid var(--border);">
                    <input type="hidden" name="docente_id" id="docente_id">
                    <h4 style="margin-bottom:1rem; border-bottom:1px solid #ddd; padding-bottom:0.5rem; color:var(--primary);">Datos Personales y Profesionales</h4>
                    
                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Cédula</label>
                            <input type="text" name="cedula" class="form-input" placeholder="Ej: V12345678" required maxlength="9" oninput="let v = this.value.replace(/[^0-9]/g, ''); this.value = v ? 'V' + v.substring(0,8) : '';">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" class="form-input" required maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-input" required maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')">
                        </div>
                    </div>

                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Grado de Instrucción</label>
                            <input type="text" name="grado_instruccion" class="form-input" placeholder="Ej. Licenciado" required maxlength="30">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Especialidad (Opcional)</label>
                            <input type="text" name="especialidad" class="form-input" maxlength="30">
                        </div>
                    </div>

                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-input" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-input" maxlength="80">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Turno</label>
                            <select name="turno_id" id="selectTurnoRegistro" class="form-input" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Dirección de Habitación</label>
                        <input type="text" name="direccion" class="form-input" required maxlength="250">
                    </div>

                    <h4 style="margin-bottom:1rem; margin-top:1.5rem; border-bottom:1px solid #ddd; padding-bottom:0.5rem; color:var(--primary);">Datos Médicos</h4>
                    <div style="display:flex; gap:1rem; align-items: flex-end;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">¿Tiene alguna patología?</label>
                            <select name="tiene_patologia" class="form-input" onchange="document.getElementById('patologia_desc').style.display = this.value === 'Si' ? 'block' : 'none'">
                                <option value="No">No</option>
                                <option value="Si">Sí</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:2; display:none;" id="patologia_desc">
                            <label class="form-label">¿Cuál patología?</label>
                            <input type="text" name="cual_patologia" class="form-input" placeholder="Especifique...">
                        </div>
                    </div>

                    <h4 style="margin-bottom:1rem; margin-top:1.5rem; border-bottom:1px solid #ddd; padding-bottom:0.5rem; color:var(--primary);">Datos Antropométricos</h4>
                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Edad</label>
                            <input type="text" name="edad" class="form-input" required maxlength="2" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Peso (kg)</label>
                            <input type="text" name="peso" class="form-input" placeholder="Ej. 70" required maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Estatura (m)</label>
                            <input type="text" name="estatura" class="form-input" placeholder="Ej. 175" required maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Talla Camisa</label>
                            <input type="text" name="talla_camisa" class="form-input" placeholder="Ej. M" required maxlength="2">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Talla Pantalón</label>
                            <input type="text" name="talla_pantalon" class="form-input" placeholder="Ej. 32" required maxlength="2">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Talla Zapato</label>
                            <input type="text" name="talla_zapato" class="form-input" placeholder="Ej. 40" required maxlength="2" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <button type="submit" class="btn btn--primary btn--full" id="btnGuardarDocente" style="margin-top:1rem;">
                        <span class="loader" id="loaderDocente"></span> <span id="textDocente">Guardar Docente</span>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- TAB: Directorio de Docentes -->
        <div id="tab-directorio" class="tab-content <?= $_SESSION['rol_id'] == 1 ? '' : 'active' ?>">
            <!-- Tarjetas de KPIs -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                <div style="flex:1; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-chalkboard-teacher"></i> Total Docentes</div>
                    <div style="font-size: 2.5rem; font-weight: 700; margin-top:0.5rem;" id="kpi-total-docentes">0</div>
                </div>
                <div style="flex:1; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:white; padding:1.5rem; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 0.9rem; opacity: 0.9; text-transform:uppercase; letter-spacing:1px; font-weight:600;"><i class="fas fa-sun"></i> Distribución de Turnos</div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-top:0.5rem; line-height:1.2;" id="kpi-turnos-docentes">Cargando...</div>
                </div>
            </div>

            <!-- Barra de Búsqueda y Filtros -->
            <div style="display:flex; gap:1rem; margin-bottom: 1.5rem; background: var(--bg-color); padding: 1rem; border-radius:8px; align-items:flex-end;">
                <div style="flex:2;">
                    <label class="form-label" style="font-size:0.85rem;"><i class="fas fa-search"></i> Buscar Docente</label>
                    <input type="text" id="filtro_busqueda_docente" class="form-input" placeholder="Nombre, apellido o cédula..." onkeyup="filtrarDocentes()">
                </div>
                <div style="flex:1;">
                    <label class="form-label" style="font-size:0.85rem;">Turno</label>
                    <select id="filtro_turno_docente" class="form-input" onchange="filtrarDocentes()">
                        <option value="">Todos</option>
                        <option value="Mañana">Mañana</option>
                        <option value="Tarde">Tarde</option>
                    </select>
                </div>
                <div>
                    <button type="button" class="btn btn--outline" onclick="imprimirDirectorio()"><i class="fas fa-print"></i> Imprimir</button>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table" id="tabla_directorio_docentes">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombres y Apellidos</th>
                            <th>Especialidad</th>
                            <th>Turno</th>
                            <th>Teléfono</th>
                            <th>Clase Asignada</th>
                            <th style="text-align:center;">Ver Ficha</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDocentes">
                        <tr><td colspan="7" style="text-align:center;">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($_SESSION['rol_id'] == 1): ?>
        <!-- TAB: Asignación -->
        <div id="tab-asignacion" class="tab-content">
            <h3 class="card-title">Asignación de Docente a Sección</h3>
            <p style="color:var(--text-muted); margin-bottom:1.5rem;">Asigna los profesores especialistas o guías a sus respectivas secciones activas del periodo actual.</p>
            
            <form id="formAsignacion" style="background:var(--bg-color); padding:1.5rem; border-radius:8px; border:1px solid var(--border); width: 100%;">
                
                <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
                    <div class="form-group" style="flex:1; min-width:200px;">
                        <label class="form-label">Periodo Escolar *</label>
                        <select name="periodo_id" id="selectPeriodoAsig" class="form-input" required>
                            <option value="">Seleccione periodo...</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:2; min-width:250px;">
                        <label class="form-label">Docente *</label>
                        <select name="docente_id" id="selectDocenteAsig" class="form-input" required>
                            <option value="">Seleccione un docente...</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Grado / Grupo y Sección *</label>
                        <select name="clase_json" id="selectGradoAsig" class="form-input" required>
                            <option value="">Seleccione grado y sección...</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">Asignar Sección</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- PANEL LATERAL (Offcanvas) PARA VER DETALLES DEL DOCENTE -->
<div id="panelOverlayDocente" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;" onclick="cerrarSidePanelDocente()"></div>
<div id="docenteSidePanel" style="position:fixed; top:0; right:-600px; width:600px; max-width:100%; height:100%; background:var(--surface); z-index:1000; box-shadow:-5px 0 15px rgba(0,0,0,0.1); transition: right 0.3s ease; overflow-y:auto; padding:2rem;">
    <button onclick="cerrarSidePanelDocente()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#64748B;"><i class="fas fa-times"></i></button>
    <div id="panelContentDocente">Cargando...</div>
</div>

<script src="public/js/docentes.js?v=<?= time() ?>"></script>
