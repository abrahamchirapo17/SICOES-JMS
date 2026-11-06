<div id="vista-aulas" style="display:block;">
    <div class="card" style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 class="card-title" style="margin-bottom:0.25rem;"><i class="fas fa-school" style="color:var(--primary);"></i> Gestión de Aulas y Matrícula</h2>
            <p style="color:var(--text-muted); font-size:0.9rem;">Seleccione un aula para ver y gestionar la lista de estudiantes inscritos en el período actual.</p>
        </div>
        <div>
            <button class="btn btn--outline" onclick="cargarAulas()"><i class="fas fa-sync-alt"></i> Actualizar</button>
        </div>
    </div>

    <!-- Contenedor dinámico de las aulas -->
    <div id="contenedor-aulas">
        <div style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-spinner fa-spin fa-2x"></i><br>Cargando aulas activas...
        </div>
    </div>
</div>

<div id="vista-lista" style="display:none;">
    <div class="card" style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <button class="btn btn--outline" onclick="volverVistaAulas()" style="margin-bottom:0.75rem;"><i class="fas fa-arrow-left"></i> Volver a Aulas</button>
            <h2 class="card-title" style="margin-bottom:0.25rem;" id="titulo-aula">Lista de Estudiantes</h2>
            <p style="color:var(--text-muted); font-size:0.9rem;" id="subtitulo-aula">Período Actual</p>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button class="btn btn--primary" onclick="imprimirLista()"><i class="fas fa-print"></i> Imprimir Lista</button>
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="data-table" id="tabla-alumnos">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Cédula Escolar</th>
                        <th>Apellidos y Nombres</th>
                        <th>Sexo</th>
                        <th>Condición</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-alumnos">
                    <!-- Se llena con JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para cambiar estatus -->
<div id="modal-estado" class="modal">
    <div class="modal-content" style="max-width:400px;">
        <span class="close-modal" onclick="cerrarModalEstado()">&times;</span>
        <h3 style="margin-bottom:1rem; color:var(--text-dark);">Modificar Estatus</h3>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">Seleccione el nuevo estatus para <strong id="nombre-estudiante-estado"></strong>.</p>
        
        <input type="hidden" id="matricula-id-estado">
        
        <div class="form-group">
            <label class="form-label">Estatus de Matrícula</label>
            <select id="select-nuevo-estado" class="form-input">
                <option value="Inscrito">Inscrito</option>
                <option value="Retirado">Retirado</option>
                <option value="Egresado">Egresado</option>
                <option value="Promovido">Promovido</option>
            </select>
        </div>

        <div style="text-align:right; margin-top:1.5rem;">
            <button class="btn btn--outline" onclick="cerrarModalEstado()">Cancelar</button>
            <button class="btn btn--primary" onclick="guardarNuevoEstado()">Guardar Cambios</button>
        </div>
    </div>
</div>

<!-- Estilos específicos para las tarjetas de aula y modal -->
<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}
.modal-content {
    background-color: var(--surface);
    padding: 2rem;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    position: relative;
    animation: modalFadeIn 0.3s ease-out;
}
@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.close-modal {
    position: absolute;
    right: 1.5rem;
    top: 1.5rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}
.close-modal:hover {
    color: var(--danger);
}
.aula-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.5rem;
    transition: all 0.2s;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.aula-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}
.aula-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 4px; height: 100%;
    background: var(--primary);
}
.aula-card.vacia::before {
    background: var(--text-muted);
}
.etapa-section {
    margin-bottom: 2.5rem;
}
.etapa-title {
    font-size: 1.25rem;
    color: var(--text-dark);
    margin-bottom: 1.25rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--border);
    display: inline-block;
}
</style>

<script src="public/js/matricula.js?v=3"></script>
