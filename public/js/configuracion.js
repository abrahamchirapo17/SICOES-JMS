document.addEventListener('DOMContentLoaded', () => {
    cargarEstructura();

    // Formularios
    const formPeriodo = document.getElementById('formPeriodo');
    const formLapso = document.getElementById('formLapso');

    if (formPeriodo) {
        formPeriodo.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnGuardarPeriodo');
            const loader = document.getElementById('loaderPeriodo');
            
            btn.disabled = true;
            loader.style.display = 'inline-block';

            const formData = new FormData(formPeriodo);
            formData.append('action', 'crear_periodo');

            try {
                const res = await fetch('api/configuracion.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.status === 'success') {
                    mostrarAlerta('success', data.message);
                    formPeriodo.reset();
                    cargarEstructura(); // Recargar tablas y selects
                } else {
                    mostrarAlerta('error', data.message);
                }
            } catch (error) {
                mostrarAlerta('error', 'Error de red.');
            } finally {
                btn.disabled = false;
                loader.style.display = 'none';
            }
        });
    }

    if (formLapso) {
        formLapso.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formLapso);
            formData.append('action', 'crear_lapso');

            try {
                const res = await fetch('api/configuracion.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') {
                    mostrarAlerta('success', data.message);
                    formLapso.reset();
                    cargarEstructura();
                } else {
                    mostrarAlerta('error', data.message);
                }
            } catch (error) {
                mostrarAlerta('error', 'Error de red.');
            }
        });
    }

    const formGradoSeccion = document.getElementById('formGradoSeccion');
    if (formGradoSeccion) {
        formGradoSeccion.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formGradoSeccion);
            formData.append('action', 'crear_grado_seccion');
            try {
                const res = await fetch('api/configuracion.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') {
                    mostrarAlerta('success', data.message);
                    formGradoSeccion.reset();
                    document.getElementById('selectNombreGrado').innerHTML = '<option value="">Primero seleccione la etapa...</option>';
                    cargarEstructura();
                } else {
                    mostrarAlerta('error', data.message);
                }
            } catch (error) { console.error(error); }
        });
    }
});

function mostrarAlerta(tipo, mensaje) {
    if (tipo === 'success') {
        Swal.fire('¡Éxito!', mensaje, 'success');
    } else {
        Swal.fire('Error', mensaje, 'error');
    }
}

window.openTab = function(tabId) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

async function cargarEstructura() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar_estructura');
        
        const res = await fetch('api/configuracion.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            renderizarEstructura(data.data);
            renderizarPeriodos(data.data.periodos);
            renderizarLapsos(data.data.lapsos, data.data.periodos);
        }
    } catch (e) {
        console.error("Error cargando estructura:", e);
    }
}

function renderizarEstructura(data) {
    const { niveles, combinaciones, turnos } = data;
    
    // Llenar select de niveles
    const selectNivel = document.getElementById('selectNivelEstructura');
    if (selectNivel) {
        let opciones = '<option value="">Seleccione...</option>';
        niveles.forEach(n => {
            opciones += `<option value="${n.id}" data-nombre="${n.nombre}">${n.nombre}</option>`;
        });
        selectNivel.innerHTML = opciones;
    }

    // Llenar select de turnos
    const selectTurno = document.getElementById('selectTurnoEstructura');
    if (selectTurno && turnos) {
        let opcionesTurno = '<option value="">Seleccione turno...</option>';
        turnos.forEach(t => {
            opcionesTurno += `<option value="${t.id}">${t.nombre}</option>`;
        });
        selectTurno.innerHTML = opcionesTurno;
    }

    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">';
    
    niveles.forEach(n => {
        html += `<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 1.5rem; box-shadow: var(--shadow-sm);">
                 <h4 style="border-bottom:2px solid var(--primary); display:inline-block; padding-bottom:0.25rem; color:var(--primary); margin-bottom: 1.5rem; font-size:1.2rem;">${n.nombre}</h4>`;
        
        const combosNivel = combinaciones ? combinaciones.filter(c => c.nivel_id === n.id) : [];
        if (combosNivel.length === 0) {
            html += `<p style="color:var(--text-muted); font-size:0.875rem;">No hay clases registradas para esta etapa.</p>`;
        } else {
            // Group first by Turno, then by Grado
            const gruposPorTurno = {};
            combosNivel.forEach(c => {
                const turno = c.turno || 'Sin Turno';
                if(!gruposPorTurno[turno]) gruposPorTurno[turno] = {};
                if(!gruposPorTurno[turno][c.grado]) gruposPorTurno[turno][c.grado] = [];
                gruposPorTurno[turno][c.grado].push(c.seccion);
            });

            for(let turno in gruposPorTurno) {
                html += `<div style="margin-bottom: 1.5rem; margin-left: 0.5rem; border-left: 3px solid var(--secondary); padding-left: 1rem;">`;
                html += `<h5 style="color:var(--text-dark); margin-bottom:0.5rem; font-weight:600;"><i class="fas fa-clock"></i> Turno ${turno}</h5>`;
                html += `<ul style="margin-left:1.5rem; color:var(--text-main); list-style-type: square; line-height: 1.8;">`;
                for(let g in gruposPorTurno[turno]) {
                    html += `<li><strong>${g}</strong> (Secciones activas: <span style="color:var(--primary); font-weight:600;">${gruposPorTurno[turno][g].join(', ')}</span>)</li>`;
                }
                html += `</ul>`;
                html += `</div>`;
            }
        }
        html += `</div>`;
    });
             
    html += `</div>`;

    document.getElementById('contenedorEstructura').innerHTML = html;
}

window.actualizarOpcionesGrado = function() {
    const selectNivel = document.getElementById('selectNivelEstructura');
    const selectGrado = document.getElementById('selectNombreGrado');
    const selectSeccion = document.getElementById('selectSeccionEstructura');
    const selectedOption = selectNivel.options[selectNivel.selectedIndex];
    const labelGrado = document.getElementById('labelNombreGrado');
    
    if (!selectedOption.value) {
        selectGrado.innerHTML = '<option value="">Primero seleccione la etapa...</option>';
        if (selectSeccion) selectSeccion.innerHTML = '<option value="">Primero seleccione la etapa...</option>';
        if (labelGrado) labelGrado.innerText = 'Grado / Grupo';
        return;
    }

    const nombreNivel = selectedOption.getAttribute('data-nombre');
    let opciones = '';
    let opcionesSeccion = '';

    if (nombreNivel === 'Preescolar') {
        opciones += '<option value="Grupo 1">Grupo 1</option>';
        opciones += '<option value="Grupo 2">Grupo 2</option>';
        opciones += '<option value="Grupo 3">Grupo 3</option>';
        if (labelGrado) labelGrado.innerText = 'Grupo';
    } else if (nombreNivel === 'Primaria') {
        opciones += '<option value="1er Grado">1er Grado</option>';
        opciones += '<option value="2do Grado">2do Grado</option>';
        opciones += '<option value="3er Grado">3er Grado</option>';
        opciones += '<option value="4to Grado">4to Grado</option>';
        opciones += '<option value="5to Grado">5to Grado</option>';
        opciones += '<option value="6to Grado">6to Grado</option>';
        if (labelGrado) labelGrado.innerText = 'Grado';
    } else if (nombreNivel === 'Media General') {
        opciones += '<option value="1er Año">1er Año</option>';
        opciones += '<option value="2do Año">2do Año</option>';
        opciones += '<option value="3er Año">3er Año</option>';
        opciones += '<option value="4to Año">4to Año</option>';
        opciones += '<option value="5to Año">5to Año</option>';
        if (labelGrado) labelGrado.innerText = 'Año';
    } else {
        opciones += '<option value="Único">Único</option>';
        if (labelGrado) labelGrado.innerText = 'Grado / Grupo';
    }

    // Dynamic Secciones
    if (nombreNivel === 'Primaria') {
        const letras = ['A', 'B', 'C', 'D'];
        letras.forEach(l => opcionesSeccion += `<option value="${l}">Sección ${l}</option>`);
    } else {
        const letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        letras.forEach(l => opcionesSeccion += `<option value="${l}">Sección ${l}</option>`);
    }

    selectGrado.innerHTML = opciones;
    if (selectSeccion) selectSeccion.innerHTML = opcionesSeccion;
};

function renderizarPeriodos(periodos) {
    const tabla = document.getElementById('tablaPeriodos');
    const selectLapsos = document.getElementById('selectPeriodoLapsos');
    
    if (!periodos || periodos.length === 0) {
        tabla.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No hay períodos registrados.</td></tr>`;
        selectLapsos.innerHTML = '<option value="">Primero cree un período...</option>';
        return;
    }

    let filas = '';
    let opciones = '<option value="">Seleccione un período...</option>';

    periodos.forEach(p => {
        const estadoBadge = p.estado === 'Abierto' 
            ? '<span class="badge badge-success">Abierto</span>' 
            : '<span class="badge badge-warning">Cerrado</span>';
            
        filas += `<tr>
            <td style="font-weight:600;">${p.nombre}</td>
            <td>${p.fecha_inicio}</td>
            <td>${p.fecha_fin}</td>
            <td>${estadoBadge}</td>
        </tr>`;
        
        opciones += `<option value="${p.id}">${p.nombre} (${p.estado})</option>`;
    });

    tabla.innerHTML = filas;
    selectLapsos.innerHTML = opciones;
}

function renderizarLapsos(lapsos, periodos) {
    const tabla = document.getElementById('tablaLapsos');
    if (!lapsos || lapsos.length === 0) {
        tabla.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">No hay lapsos registrados.</td></tr>`;
        return;
    }

    let filas = '';
    lapsos.forEach(l => {
        // Find the period name
        const p = periodos.find(per => per.id === l.periodo_id);
        const pName = p ? p.nombre : 'Desconocido';
        
        filas += `<tr>
            <td style="font-weight:600;">${pName}</td>
            <td>Lapso ${l.numero}</td>
            <td>${l.fecha_inicio}</td>
            <td>${l.fecha_fin}</td>
        </tr>`;
    });
    tabla.innerHTML = filas;
}
