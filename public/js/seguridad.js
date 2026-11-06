document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios();
});

let rolesActuales = [];

async function cargarUsuarios() {
    const tbody = document.getElementById('tabla-usuarios');
    try {
        const formData = new FormData();
        formData.append('action', 'listar_usuarios');
        const res = await fetch('api/seguridad.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            renderUsuarios(data.data);
        } else {
            tbody.innerHTML = `<tr><td colspan="6" style="color:red; text-align:center;">Error: ${data.message}</td></tr>`;
        }
    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="6" style="color:red; text-align:center;">Error de conexión</td></tr>`;
    }
}

function renderUsuarios(usuarios) {
    const tbody = document.getElementById('tabla-usuarios');
    if (usuarios.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No hay usuarios.</td></tr>`;
        return;
    }
    
    let html = '';
    usuarios.forEach(u => {
        const estadoBadge = u.estado === 'Activo' ? 
            `<span style="background:#10B98120; color:#10B981; padding:0.2rem 0.5rem; border-radius:12px; font-size:0.8rem; font-weight:bold;">Activo</span>` : 
            `<span style="background:#EF444420; color:#EF4444; padding:0.2rem 0.5rem; border-radius:12px; font-size:0.8rem; font-weight:bold;">Inactivo</span>`;
        
        const fecha = new Date(u.created_at).toLocaleDateString();
        
        html += `
            <tr>
                <td>${u.id}</td>
                <td><strong>${u.username}</strong></td>
                <td>${u.rol || 'Sin Rol'}</td>
                <td>${estadoBadge}</td>
                <td>${fecha}</td>
                <td>
                    ${u.id != 1 ? `
                        <button class="btn btn--outline" style="padding:0.25rem 0.5rem; font-size:0.8rem; color:var(--primary);" onclick="resetearClave(${u.id}, '${u.username}')" title="Resetear Clave"><i class="fas fa-key"></i></button>
                        <button class="btn btn--outline" style="padding:0.25rem 0.5rem; font-size:0.8rem; color:${u.estado === 'Activo' ? 'var(--danger)' : 'var(--success)'};" onclick="cambiarEstadoUsuario(${u.id}, '${u.estado}')" title="Cambiar Estado"><i class="fas fa-power-off"></i></button>
                    ` : '<span style="color:var(--text-muted); font-size:0.8rem;">(Intocable)</span>'}
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}



function abrirModalUsuario() {
    document.getElementById('form-usuario').reset();
    document.getElementById('modal-usuario').style.display = 'flex';
}

function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

async function guardarUsuario(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('action', 'crear_usuario');
        formData.append('username', document.getElementById('usu_username').value);
        formData.append('password', document.getElementById('usu_password').value);
        formData.append('rol_id', document.getElementById('usu_rol').value);
        
        const res = await fetch('api/seguridad.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            Swal.fire('¡Éxito!', 'Usuario creado correctamente', 'success');
            cerrarModal('modal-usuario');
            cargarUsuarios();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'Problema de conexión', 'error');
    } finally {
        btn.disabled = false;
    }
}



async function cambiarEstadoUsuario(id, estadoActual) {
    const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
    const confirmText = estadoActual === 'Activo' ? 
        '¿Deseas suspender a este usuario? No podrá iniciar sesión.' : 
        '¿Deseas reactivar a este usuario?';
        
    const result = await Swal.fire({
        title: 'Confirmar Acción',
        text: confirmText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('action', 'cambiar_estado');
            formData.append('id', id);
            formData.append('estado', nuevoEstado);
            
            const res = await fetch('api/seguridad.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if(data.status === 'success') {
                Swal.fire('Actualizado', 'El estado del usuario ha cambiado.', 'success');
                cargarUsuarios();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch(e) {
            Swal.fire('Error', 'Error de conexión', 'error');
        }
    }
}

async function resetearClave(id, username) {
    const { value: password } = await Swal.fire({
        title: `Resetear Clave de ${username}`,
        input: 'text',
        inputLabel: 'Escriba la nueva contraseña temporal',
        inputPlaceholder: 'Ej: Abc123*',
        showCancelButton: true,
        inputValidator: (value) => {
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\*\,\.\+\-\/]).{6,10}$/;
            if (!regex.test(value)) {
                return 'La contraseña debe tener entre 6 y 10 caracteres, incluir una mayúscula, una minúscula, un número y un carácter especial (* , . + - /)';
            }
        }
    });

    if (password) {
        try {
            const formData = new FormData();
            formData.append('action', 'resetear_clave');
            formData.append('id', id);
            formData.append('password', password);
            
            const res = await fetch('api/seguridad.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if(data.status === 'success') {
                Swal.fire('Clave Actualizada', 'El usuario ahora puede ingresar con la nueva clave.', 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch(e) {
            Swal.fire('Error', 'Error de conexión', 'error');
        }
    }
}

async function hacerBackup() {
    Swal.fire({
        title: 'Copia de Seguridad',
        text: 'Generando archivo SQL con la base de datos completa. Por favor, espere...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Navegar a la url para forzar la descarga del sql
    window.location.href = 'api/backup.php';
    
    setTimeout(() => {
        Swal.fire('Completado', 'La descarga de su copia de seguridad ha iniciado. Recuerde guardarla en un lugar seguro cada semana.', 'success');
    }, 2500);
}
