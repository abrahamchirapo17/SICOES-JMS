let aulasActuales = [];
let aulaSeleccionada = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarAulas();
});

async function cargarAulas() {
    const contenedor = document.getElementById('contenedor-aulas');
    contenedor.innerHTML = '<div style="text-align:center; padding:3rem; color:var(--text-muted);"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Cargando aulas activas...</div>';

    try {
        const formData = new FormData();
        formData.append('action', 'listar_aulas');
        
        const res = await fetch('api/matricula.php', { method: 'POST', body: formData });
        const resData = await res.json();

        if (resData.status === 'success') {
            aulasActuales = resData.data;
            renderizarAulas();
        } else {
            contenedor.innerHTML = `<div style="color:var(--danger); padding:2rem;">Error: ${resData.message}</div>`;
        }
    } catch (e) {
        contenedor.innerHTML = `<div style="color:var(--danger); padding:2rem;">Error de conexión.</div>`;
    }
}

function renderizarAulas() {
    const contenedor = document.getElementById('contenedor-aulas');
    
    if (aulasActuales.length === 0) {
        contenedor.innerHTML = '<div class="card"><p style="color:var(--text-muted); text-align:center;">No hay aulas configuradas en la estructura académica actual.</p></div>';
        return;
    }

    // Agrupar por nivel y luego por turno
    const grupos = {};
    aulasActuales.forEach(a => {
        if (!grupos[a.nivel]) grupos[a.nivel] = {};
        if (!grupos[a.nivel][a.turno]) grupos[a.nivel][a.turno] = [];
        grupos[a.nivel][a.turno].push(a);
    });

    let html = '';

    for (const nivel in grupos) {
        html += `<div class="etapa-section">`;
        html += `<h3 class="etapa-title"><i class="fas fa-layer-group" style="color:var(--primary); margin-right:0.5rem;"></i> ${nivel}</h3>`;
        html += `<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap:2.5rem; align-items:start;">`;
        
        for (const turno in grupos[nivel]) {
            html += `<div>`;
            html += `<h4 style="margin-bottom:1rem; color:var(--text-main); font-size:1.1rem; border-left: 3px solid var(--secondary); padding-left: 0.5rem;"><i class="fas fa-clock"></i> Turno ${turno}</h4>`;
            html += `<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap:1.25rem; margin-bottom: 1.5rem;">`;
            
            grupos[nivel][turno].forEach(aula => {
                const inscritos = parseInt(aula.inscritos) || 0;
                const claseVacia = inscritos === 0 ? 'vacia' : '';
                const textInscritos = inscritos === 1 ? '1 estudiante' : `${inscritos} estudiantes`;
                const colorBadge = inscritos > 0 ? 'background:var(--primary-light); color:var(--primary);' : 'background:#F1F5F9; color:var(--text-muted);';
                
                const docenteInfo = aula.docente_asignado 
                    ? `<div style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;"><i class="fas fa-chalkboard-teacher"></i> ${aula.docente_asignado}</div>` 
                    : `<div style="font-size:0.85rem; color:#9ca3af; margin-top:0.25rem;"><i class="fas fa-chalkboard-teacher"></i> Sin asignar</div>`;

                html += `
                <div class="aula-card ${claseVacia}" onclick="verAula(${aula.grado_id}, ${aula.seccion_id}, ${aula.turno_id}, '${aula.grado}', '${aula.seccion}', '${aula.turno}', '${aula.docente_asignado || ''}')">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <h4 style="font-size:1.1rem; color:var(--text-dark); margin-bottom:0;">${aula.grado} "${aula.seccion}"</h4>
                            ${docenteInfo}
                        </div>
                        <div style="padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem; font-weight:600; text-align:center; white-space:nowrap; ${colorBadge}">
                            ${textInscritos}
                        </div>
                    </div>
                    <div style="margin-top:1rem; font-size:0.85rem; color:var(--primary); font-weight:500;">
                        Ver Lista <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i>
                    </div>
                </div>`;
            });
            html += `</div></div>`;
        }

        html += `</div></div>`;
    }

    contenedor.innerHTML = html;
}

function verAula(grado_id, seccion_id, turno_id, gradoStr, seccionStr, turnoStr, docenteAsignado) {
    aulaSeleccionada = { grado_id, seccion_id, turno_id, gradoStr, seccionStr, turnoStr, docenteAsignado, titulo: `${gradoStr} "${seccionStr}" - Turno ${turnoStr}` };
    
    document.getElementById('vista-aulas').style.display = 'none';
    document.getElementById('vista-lista').style.display = 'block';
    
    document.getElementById('titulo-aula').textContent = aulaSeleccionada.titulo;
    
    cargarListaAlumnos();
}

function volverVistaAulas() {
    document.getElementById('vista-lista').style.display = 'none';
    document.getElementById('vista-aulas').style.display = 'block';
    cargarAulas(); // Refrescar conteos
}

async function cargarListaAlumnos() {
    const tbody = document.getElementById('tbody-alumnos');
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Cargando estudiantes...</td></tr>';

    try {
        const formData = new FormData();
        formData.append('action', 'listar_alumnos');
        formData.append('grado_id', aulaSeleccionada.grado_id);
        formData.append('seccion_id', aulaSeleccionada.seccion_id);
        formData.append('turno_id', aulaSeleccionada.turno_id);
        
        const res = await fetch('api/matricula.php', { method: 'POST', body: formData });
        const resData = await res.json();

        if (resData.status === 'success') {
            renderizarTablaAlumnos(resData.data);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--danger);">Error: ${resData.message}</td></tr>`;
        }
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--danger);">Error de conexión.</td></tr>`;
    }
}

function renderizarTablaAlumnos(alumnos) {
    const tbody = document.getElementById('tbody-alumnos');
    
    if (alumnos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">No hay estudiantes inscritos en esta aula.</td></tr>';
        return;
    }

    let html = '';
    alumnos.forEach((al, idx) => {
        let badgeColor = '#10B981'; // Verde Inscrito
        if (al.estado === 'Retirado') badgeColor = '#EF4444';
        if (al.estado === 'Egresado') badgeColor = '#3B82F6';
        if (al.estado === 'Promovido') badgeColor = '#8B5CF6';
        
        let botonEstatus = USER_ROL_ID === 1 ? 
            `<button class="btn" style="background-color: #3b82f6; color: white; border: none; padding: 0.4rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; box-shadow: var(--shadow-sm); display: inline-flex; align-items: center; gap: 0.4rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'" onclick="abrirModalEstado(${al.matricula_id}, '${al.nombres} ${al.apellidos}', '${al.estado}')" title="Modificar Estatus">
                <i class="fas fa-edit"></i> Estatus
            </button>` : 
            `<span style="color:#aaa; font-size:0.85rem;">Sin permiso</span>`;

        html += `
        <tr>
            <td style="font-weight:600; color:var(--text-muted);">${idx + 1}</td>
            <td>${al.cedula_escolar || 'N/A'}</td>
            <td style="font-weight:500;">${al.apellidos}, ${al.nombres}</td>
            <td>${al.sexo}</td>
            <td>${al.condicion || 'Regular'}</td>
            <td><span style="background:${badgeColor}20; color:${badgeColor}; padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem; font-weight:600;">${al.estado}</span></td>
            <td>
                ${botonEstatus}
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function abrirModalEstado(matricula_id, nombreCompleto, estadoActual) {
    document.getElementById('matricula-id-estado').value = matricula_id;
    document.getElementById('nombre-estudiante-estado').textContent = nombreCompleto;
    document.getElementById('select-nuevo-estado').value = estadoActual;
    document.getElementById('modal-estado').style.display = 'flex';
}

function cerrarModalEstado() {
    document.getElementById('modal-estado').style.display = 'none';
}

async function guardarNuevoEstado() {
    const id = document.getElementById('matricula-id-estado').value;
    const estado = document.getElementById('select-nuevo-estado').value;

    try {
        const formData = new FormData();
        formData.append('action', 'cambiar_estado');
        formData.append('matricula_id', id);
        formData.append('estado', estado);
        
        const res = await fetch('api/matricula.php', { method: 'POST', body: formData });
        const resData = await res.json();

        if (resData.status === 'success') {
            cerrarModalEstado();
            cargarListaAlumnos(); // Refrescar lista
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: 'El estatus se actualizó correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', resData.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Hubo un problema de conexión.', 'error');
    }
}

function imprimirLista() {
    // Clonar la tabla original para no alterar el DOM
    const tablaOriginal = document.querySelector('#vista-lista .data-table').cloneNode(true);
    
    // Quitar la columna de "Acciones" (última columna)
    const ths = tablaOriginal.querySelectorAll('th');
    const tds = tablaOriginal.querySelectorAll('tbody tr');
    
    if (ths.length > 0) ths[ths.length - 1].remove();
    tds.forEach(tr => {
        const celdas = tr.querySelectorAll('td');
        if (celdas.length > 0) celdas[celdas.length - 1].remove();
    });

    const docenteText = aulaSeleccionada.docenteAsignado ? aulaSeleccionada.docenteAsignado : 'Sin asignar';
    const baseUrl = window.location.origin + window.location.pathname.replace(/dashboard\.php$/, '');
    const logoUrl = baseUrl + 'public/img/logo.png';

    const htmlPrint = `
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Lista de Estudiantes - ${aulaSeleccionada.titulo}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 20px; 
                    color: #333; 
                    background: #fff;
                }
                .header-container {
                    display: flex;
                    align-items: center;
                    border-bottom: 3px solid #003366;
                    padding-bottom: 15px;
                    margin-bottom: 25px;
                }
                .logo {
                    width: 90px;
                    height: auto;
                    margin-right: 20px;
                }
                .header-text {
                    flex: 1;
                    text-align: center;
                }
                .title-institucion { 
                    margin: 0; 
                    color: #003366; 
                    font-size: 1.6rem; 
                    text-transform: uppercase; 
                    font-weight: 800;
                }
                .subtitle { 
                    margin: 5px 0 0 0; 
                    font-size: 1.2rem; 
                    color: #555;
                }
                .info-grid { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 20px; 
                    font-weight: bold; 
                    font-size: 1rem; 
                    background: #f8fafc;
                    padding: 15px;
                    border-radius: 8px;
                    border: 1px solid #e2e8f0;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    font-size: 0.95rem; 
                }
                th, td { 
                    border: 1px solid #cbd5e1; 
                    padding: 10px; 
                    text-align: left; 
                }
                th { 
                    background-color: #003366; 
                    color: #ffffff; 
                    font-weight: 600;
                }
                tbody tr:nth-child(even) {
                    background-color: #f1f5f9;
                }
                @media print {
                    @page { margin: 1.5cm; }
                    body { padding: 0; }
                    .info-grid { border: none; padding: 0; margin-bottom: 15px; background: transparent; }
                    th { background-color: #003366 !important; color: #ffffff !important; -webkit-print-color-adjust: exact; }
                    tbody tr:nth-child(even) { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
                }
            </style>
        </head>
        <body>
            <div class="header-container">
                <img src="${logoUrl}" class="logo" alt="Logo Institución" onerror="this.style.display='none'">
                <div class="header-text">
                    <h1 class="title-institucion">U.E.N. "Jesús María Sifontes"</h1>
                    <p class="subtitle">Lista Oficial de Estudiantes Inscritos</p>
                </div>
            </div>
            
            <div class="info-grid">
                <div>Grado / Grupo: <span style="font-weight:normal;">${aulaSeleccionada.gradoStr}</span></div>
                <div>Sección: <span style="font-weight:normal;">${aulaSeleccionada.seccionStr}</span></div>
                <div>Turno: <span style="font-weight:normal;">${aulaSeleccionada.turnoStr}</span></div>
                <div>Docente: <span style="font-weight:normal;">${docenteText}</span></div>
            </div>

            ${tablaOriginal.outerHTML}
            
            <script>
                // Esperar a que la imagen cargue para abrir el diálogo de impresión
                setTimeout(() => {
                    window.print();
                    window.close();
                }, 500);
            </script>
        </body>
        </html>
    `;

    const printWindow = window.open('', '_blank', 'width=900,height=700');
    printWindow.document.open();
    printWindow.document.write(htmlPrint);
    printWindow.document.close();
}
