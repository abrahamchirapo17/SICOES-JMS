<?php if($_SESSION['rol_id'] != 1): ?>
    <div class="card" style="text-align:center; padding:5rem 2rem;">
        <i class="fas fa-lock fa-4x" style="color:#EF4444; margin-bottom:1.5rem;"></i>
        <h2 style="color:var(--text-dark); margin-bottom:1rem;">Acceso Restringido</h2>
        <p style="color:var(--text-muted); font-size:1.1rem;">Solo el administrador principal tiene acceso a este módulo.</p>
    </div>
<?php return; endif; ?>

<div class="card" style="padding: 0; overflow: hidden;">
    <!-- Pestañas de Navegación -->
    <div class="tabs" style="padding: 1rem 1.5rem 0 1.5rem; margin-bottom: 0; background: var(--bg-color);">
        <button class="tab-btn active" onclick="openTab('tab-periodos')">Períodos Escolares</button>
        <button class="tab-btn" onclick="openTab('tab-lapsos')">Lapsos Académicos</button>
        <button class="tab-btn" onclick="openTab('tab-estructura')">Estructura Académica</button>
    </div>

    <div style="padding: 1.5rem;">
        
        <div id="configAlert" class="alert" style="display:none;"></div>

        <!-- TAB: Periodos Escolares -->
        <div id="tab-periodos" class="tab-content active">
            <h3 class="card-title">Gestión de Períodos Escolares</h3>
            <div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap: wrap;">
                
                <form id="formPeriodo" style="flex:1; min-width:300px; background:var(--bg-color); padding:1.5rem; border-radius:8px; border:1px solid var(--border);">
                    <h4 style="margin-bottom:1rem;">Abrir Nuevo Año Escolar</h4>
                    <div class="form-group">
                        <label class="form-label">Nombre del Período (Ej. 2024-2025)</label>
                        <input type="text" name="nombre" class="form-input" required>
                    </div>
                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-input" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-input" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary" id="btnGuardarPeriodo">
                        <span class="loader" id="loaderPeriodo"></span> <span id="textPeriodo">Guardar Período</span>
                    </button>
                </form>

                <div style="flex:1.5; min-width:300px;">
                    <h4 style="margin-bottom:1rem;">Períodos Registrados</h4>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPeriodos">
                                <tr><td colspan="4" style="text-align:center;">Cargando períodos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: Lapsos -->
        <div id="tab-lapsos" class="tab-content">
            <h3 class="card-title">Configuración de Lapsos</h3>
            <div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap: wrap;">
                
                <form id="formLapso" style="flex:1; min-width:300px; background:var(--bg-color); padding:1.5rem; border-radius:8px; border:1px solid var(--border);">
                    <div class="form-group">
                        <label class="form-label">Período Escolar</label>
                        <select name="periodo_id" id="selectPeriodoLapsos" class="form-input" required>
                            <option value="">Seleccione un período...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de Lapso</label>
                        <select name="numero" class="form-input" required>
                            <option value="1">1er Lapso</option>
                            <option value="2">2do Lapso</option>
                            <option value="3">3er Lapso</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:1rem;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-input" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-input" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary">Guardar Lapso</button>
                </form>

                <div style="flex:1.5; min-width:300px;">
                    <h4 style="margin-bottom:1rem;">Lapsos Registrados</h4>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Período</th>
                                    <th>Lapso</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                </tr>
                            </thead>
                            <tbody id="tablaLapsos">
                                <tr><td colspan="4" style="text-align:center;">Cargando lapsos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: Estructura -->
        <div id="tab-estructura" class="tab-content">
            <h3 class="card-title">Estructura Académica</h3>
            <p style="color:var(--text-muted); margin-bottom:1.5rem;">Crea los niveles, grados y secciones que estarán disponibles para inscripciones y docentes.</p>
            
            <div style="display:flex; gap:2rem; align-items:flex-start; flex-wrap: wrap; margin-bottom: 2rem;">
                
                <!-- Form Unificado Grados y Secciones -->
                <form id="formGradoSeccion" style="flex:1; min-width:300px; background:var(--bg-color); padding:1.5rem; border-radius:8px; border:1px solid var(--border);">
                    <h4 style="margin-bottom:1rem; border-bottom:1px solid #ddd; padding-bottom:0.5rem; color:var(--primary);">Añadir Estructura de Clases</h4>
                    <div class="form-group">
                        <label class="form-label">Etapa Educativa</label>
                        <select name="nivel_id" id="selectNivelEstructura" class="form-input" required onchange="actualizarOpcionesGrado()">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="labelNombreGrado">Grado / Grupo</label>
                        <select name="grado_nombre" id="selectNombreGrado" class="form-input" required>
                            <option value="">Primero seleccione la etapa...</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex; gap:1rem;">
                        <div style="flex:1;">
                            <label class="form-label">Sección</label>
                            <select name="seccion_nombre" id="selectSeccionEstructura" class="form-input" required>
                                <option value="A">Sección A</option>
                                <option value="B">Sección B</option>
                                <option value="C">Sección C</option>
                                <option value="D">Sección D</option>
                                <option value="E">Sección E</option>
                                <option value="F">Sección F</option>
                                <option value="G">Sección G</option>
                                <option value="H">Sección H</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label class="form-label">Turno</label>
                            <select name="turno_id" id="selectTurnoEstructura" class="form-input" required>
                                <!-- Cargado dinámicamente -->
                                <option value="">Cargando...</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary" style="margin-top:0.5rem;">Registrar en la Estructura</button>
                </form>

            </div>

            <div id="contenedorEstructura" style="background:var(--surface); padding:1.5rem; border:1px solid var(--border); border-radius:8px;">
                Cargando...
            </div>
        </div>

    </div>
</div>

<script src="public/js/configuracion.js?v=7"></script>
