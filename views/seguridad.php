<?php if($_SESSION['rol_id'] != 1): ?>
    <div class="card" style="text-align:center; padding:5rem 2rem;">
        <i class="fas fa-lock fa-4x" style="color:#EF4444; margin-bottom:1.5rem;"></i>
        <h2 style="color:var(--text-dark); margin-bottom:1rem;">Acceso Restringido</h2>
        <p style="color:var(--text-muted); font-size:1.1rem;">Solo el administrador principal tiene acceso a este módulo.</p>
    </div>
<?php return; endif; ?>

<div class="card" style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 class="card-title" style="margin-bottom:0.25rem;"><i class="fas fa-shield-alt" style="color:var(--primary);"></i> Seguridad y Gestión de Usuarios</h2>
        <p style="color:var(--text-muted); font-size:0.9rem;">Cree cuentas para el personal, asigne roles y controle el acceso al sistema.</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <button class="btn btn--outline" style="border-color:#10B981; color:#10B981;" onclick="hacerBackup()"><i class="fas fa-database"></i> Copia de Seguridad</button>
        <button class="btn btn--primary" onclick="abrirModalUsuario()"><i class="fas fa-user-plus"></i> Nuevo Usuario</button>
    </div>
</div>

<div style="display:block;">
    <!-- Lista de Usuarios -->
    <div class="card">
        <h3 style="margin-bottom:1rem; color:var(--text-dark); border-bottom:1px solid var(--border); padding-bottom:0.5rem;"><i class="fas fa-users"></i> Usuarios del Sistema</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario (Username)</th>
                        <th>Rol</th>
                        <th>Estatus</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-usuarios">
                    <tr><td colspan="6" style="text-align:center;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Usuario -->
<div id="modal-usuario" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <span class="close-modal" onclick="cerrarModal('modal-usuario')">&times;</span>
        <h3 style="margin-bottom:1.5rem; color:var(--text-dark);"><i class="fas fa-user-plus"></i> Registrar Usuario</h3>
        
        <form id="form-usuario" onsubmit="guardarUsuario(event)">
            <div class="form-group">
                <label class="form-label">Nombre de Usuario (Login)</label>
                <input type="text" id="usu_username" class="form-input" required placeholder="Ej: maria.secretaria" maxlength="15">
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="text" id="usu_password" class="form-input" required placeholder="Ej: Abc123*" minlength="6" maxlength="15" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\*\,\.\+\-\/]).{6,15}$" title="Debe tener entre 6 y 15 caracteres, incluir al menos una mayúscula, una minúscula, un número y un carácter especial (* , . + - /)">
                <small style="color:var(--text-muted); font-size:0.75rem;">Requiere: mayúscula, minúscula, número y especial (* , . + - /). Máx 15 caracteres.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Asignar Rol</label>
                <select name="rol_id" id="usu_rol" class="form-input" required>
                    <option value="">Seleccione un Rol</option>
                    <option value="2">Observador</option>
                    <option value="3">Secretaria</option>
                </select>
            </div>
            <div style="text-align:right; margin-top:2rem;">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-usuario')">Cancelar</button>
                <button type="submit" class="btn btn--primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>



<!-- Estilos modales reutilizados (Si no están en global) -->
<style>
.modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; }
.modal-content { background:var(--surface); padding:2rem; border-radius:12px; width:90%; box-shadow:0 10px 25px rgba(0,0,0,0.1); position:relative; animation:modalFadeIn 0.3s ease-out; }
@keyframes modalFadeIn { from{opacity:0; transform:translateY(-20px);} to{opacity:1; transform:translateY(0);} }
.close-modal { position:absolute; right:1.5rem; top:1.5rem; font-size:1.5rem; cursor:pointer; color:var(--text-muted); }
.close-modal:hover { color:var(--danger); }
</style>

<script src="public/js/seguridad.js?v=2"></script>
