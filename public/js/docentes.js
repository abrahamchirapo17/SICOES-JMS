let docentesData = [];

document.addEventListener('DOMContentLoaded', () => {
    // Asteriscos y Validacion con SweetAlert2 para Docentes
        // Validacin segura con SweetAlert al hacer click en Guardar
    const btnGuardar = document.getElementById('btnGuardarDocente');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', (e) => {
            const form = document.getElementById('formDocente');
            if (!form.checkValidity()) {
                e.preventDefault(); // Evitar el submit nativo
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Incompletos',
                    text: 'Ingrese los datos en los campos obligatorios',
                    confirmButtonColor: '#3B82F6'
                }).then(() => {
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.reportValidity();
                    }
                });
            }
        });
    }

    const btnAsignar = document.getElementById('btnGuardarAsignacion');
    if (btnAsignar) {
        btnAsignar.addEventListener('click', (e) => {
            const form = document.getElementById('formAsignacion');
            if (!form.checkValidity()) {
                e.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Incompletos',
                    text: 'Ingrese los datos en los campos obligatorios',
                    confirmButtonColor: '#3B82F6'
                }).then(() => {
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.reportValidity();
                    }
                });
            }
        });
    }

    ['formDocente', 'formAsignacion'].forEach(id => {
        const form = document.getElementById(id);
        if (form) {
            // Asteriscos
            form.querySelectorAll('[required]').forEach(el => {
                const label = el.previousElementSibling;
                if (label && label.tagName === 'LABEL' && !label.innerHTML.includes('*')) {
                    label.innerHTML += ' <span style="color:#EF4444; font-weight:bold;">*</span>';
                }
            });

            }
    });
    cargarDatos();

    // Formularios
    const formDocente = document.getElementById('formDocente');
    const formAsignacion = document.getElementById('formAsignacion');

    if (formDocente) {
        formDocente.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnGuardarDocente');
            const loader = document.getElementById('loaderDocente');
            
            btn.disabled = true;
            loader.style.display = 'inline-block';

            const formData = new FormData(formDocente);
            const docId = document.getElementById('docente_id').value;
            formData.append('action', docId ? 'editar' : 'registrar');

            try {
                const res = await fetch('api/docentes.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.status === 'success') {
                    mostrarAlertaDocente('success', data.message);
                    formDocente.reset();
                    document.getElementById('docente_id').value = '';
                    const textGuardar = document.getElementById('textDocente');
                    if(textGuardar) textGuardar.textContent = 'Guardar Docente';
                    
                    const patDesc = document.getElementById('patologia_desc');
                    if(patDesc) patDesc.style.display = 'none';
                    cargarDatos(); // Recargar tablas
                } else {
                    mostrarAlertaDocente('error', data.message);
                }
            } catch (error) {
                mostrarAlertaDocente('error', 'Error de red.');
            } finally {
                btn.disabled = false;
                loader.style.display = 'none';
            }
        });
    }

    if (formAsignacion) {
        formAsignacion.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(formAsignacion);

            const claseJson = formData.get('clase_json');
            if (claseJson) {
                const parsed = JSON.parse(claseJson);
                formData.set('grado_id', parsed.grado_id);
                formData.set('seccion_id', parsed.seccion_id);
                formData.delete('clase_json');
            }

            formData.append('action', 'asignar_seccion');

            try {
                const res = await fetch('api/docentes.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.status === 'success') {
                    mostrarAlertaDocente('success', data.message);
                    formAsignacion.reset();
                } else {
                    mostrarAlertaDocente('error', data.message);
                }
            } catch (error) {
                mostrarAlertaDocente('error', 'Error de red.');
            }
        });
    }
});

function mostrarAlertaDocente(tipo, mensaje) {
    if (tipo === 'success') {
        Swal.fire('¡Éxito!', mensaje, 'success');
    } else {
        Swal.fire('Error', mensaje, 'error');
    }
}

// === TABS ===
window.openTabDocente = function(tabId, element) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
}

async function cargarDatos() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar');
        
        const res = await fetch('api/docentes.php', { method: 'POST', body: formData });
        const resData = await res.json();
        
        if (resData.status === 'success') {
            docentesData = resData.data.docentes;
            renderizarTablaDocentes(docentesData);
            llenarSelects(resData.data);
            cargarStatsDocentes();
        }
    } catch (e) {
        console.error("Error cargando datos:", e);
    }
}

function renderizarTablaDocentes(docentes) {
    const tabla = document.getElementById('tablaDocentes');
    if (!docentes || docentes.length === 0) {
        tabla.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--text-muted);">No hay docentes registrados.</td></tr>`;
        return;
    }

    let filas = '';
    docentes.forEach(d => {
        const clasesAsignadas = d.clases_asignadas ? `<span style="font-size:0.85rem;">${d.clases_asignadas}</span>` : '<span style="color:var(--text-muted); font-size:0.85rem;">Sin asignar</span>';
        filas += `<tr>
            <td style="font-weight:600;">${d.cedula}</td>
            <td>${d.nombres} ${d.apellidos}</td>
            <td>${d.especialidad || '-'}</td>
            <td><span class="badge badge-success">${d.turno_nombre || 'N/A'}</span></td>
            <td>${d.telefono_personal || '-'}</td>
            <td>${clasesAsignadas}</td>
            <td style="text-align:center;">
                <button class="btn btn--primary" style="padding:0.4rem 0.8rem; font-size:0.85rem;" onclick="verDetallesDocente(${d.id})" title="Ver Perfil Completo"><i class="fas fa-id-card"></i> Ficha</button>
            </td>
        </tr>`;
    });
    tabla.innerHTML = filas;
}

function filtrarDocentes() {
    const q = document.getElementById('filtro_busqueda_docente').value.toLowerCase();
    const turno = document.getElementById('filtro_turno_docente').value;

    const filtrados = docentesData.filter(d => {
        const matchQ = d.nombres.toLowerCase().includes(q) || d.apellidos.toLowerCase().includes(q) || d.cedula.toLowerCase().includes(q);
        const matchTurno = turno === '' || d.turno_nombre === turno;
        return matchQ && matchTurno;
    });

    renderizarTablaDocentes(filtrados);
}

async function cargarStatsDocentes() {
    try {
        const formData = new FormData();
        formData.append('action', 'stats_docentes');
        const res = await fetch('api/docentes.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            document.getElementById('kpi-total-docentes').textContent = data.data.total;
            
            let htmlTurnos = '';
            data.data.turnos.forEach(t => { htmlTurnos += `${t.nombre || 'N/A'}: ${t.cantidad}<br>`; });
            document.getElementById('kpi-turnos-docentes').innerHTML = htmlTurnos || 'Sin datos';
        }
    } catch(err) {
        console.error("Error stats docentes", err);
    }
}

async function verDetallesDocente(id) {
    const overlay = document.getElementById('panelOverlayDocente');
    const panel = document.getElementById('docenteSidePanel');
    const content = document.getElementById('panelContentDocente');
    
    overlay.style.display = 'block';
    panel.style.right = '0';
    content.innerHTML = '<div style="text-align:center; padding: 2rem;"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando...</div>';

    try {
        const formData = new FormData();
        formData.append('action', 'obtener_detalles');
        formData.append('id', id);
        
        const res = await fetch('api/docentes.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            const d = data.data.docente;
            const asignaciones = data.data.asignaciones;
            
            let htmlAsignaciones = '';
            if (data.data.asignaciones && data.data.asignaciones.length > 0) {
                htmlAsignaciones = '<ul style="list-style:none; padding:0; margin:0;">';
                data.data.asignaciones.forEach(a => {
                    htmlAsignaciones += `<li style="padding:0.8rem; border:1px solid var(--border); border-radius:8px; margin-bottom:0.5rem; background:var(--bg-color); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong>${a.grado} "${a.seccion}"</strong>
                        </div>
                        <span style="font-size:0.8rem; background:var(--surface); color:var(--text-muted); padding:0.2rem 0.5rem; border-radius:12px;">Período ${a.periodo}</span>
                    </li>`;
                });
                htmlAsignaciones += '</ul>';
            } else {
                htmlAsignaciones = '<p style="color:var(--text-muted);">No tiene secciones asignadas actualmente.</p>';
            }

            content.innerHTML = `
                <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--border); padding-bottom: 1rem;">
                    <div style="width:80px; height:80px; border-radius:50%; background:var(--bg-color); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:var(--text-muted);">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h2 style="margin:0; font-size:1.5rem; color:var(--text-main);">${d.nombres} ${d.apellidos}</h2>
                        <div style="color:var(--text-muted); margin-top:0.3rem;"><i class="fas fa-id-card"></i> ${d.cedula}</div>
                        <div style="margin-top:0.5rem;"><span style="background:var(--primary); color:white; padding:0.2rem 0.5rem; border-radius:12px; font-size:0.8rem;">Profesor(a) - Turno ${d.turno_nombre || 'N/A'}</span></div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color:var(--primary); margin-bottom:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.3rem;"><i class="fas fa-address-card"></i> Datos Personales y Profesionales</h3>
                    <p><strong>Especialidad:</strong> ${d.especialidad || 'N/A'}</p>
                    <p><strong>Grado de Instrucción:</strong> ${d.grado_instruccion || 'N/A'}</p>
                    <p><strong>Teléfono:</strong> ${d.telefono_personal || 'N/A'}</p>
                    <p><strong>Correo:</strong> ${d.correo || 'N/A'}</p>
                    <p><strong>Dirección:</strong> ${d.direccion || 'N/A'}</p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color:var(--primary); margin-bottom:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.3rem;"><i class="fas fa-heartbeat"></i> Médicos y Antropométricos</h3>
                    <p><strong>Edad:</strong> ${d.edad ? d.edad + ' años' : 'N/A'}</p>
                    <p><strong>Peso / Estatura:</strong> ${d.peso ? d.peso+' kg' : 'N/A'} / ${d.estatura ? d.estatura+' m' : 'N/A'}</p>
                    <p><strong>Tallas (Camisa/Pantalón/Zapato):</strong> ${d.talla_camisa || '-'} / ${d.talla_pantalon || '-'} / ${d.talla_zapato || '-'}</p>
                    <p><strong>Patología:</strong> ${d.patologia || 'No'}</p>
                </div>

                  <div style="margin-bottom: 1.5rem;">
                      <h3 style="color:var(--primary); margin-bottom:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.3rem;"><i class="fas fa-chalkboard"></i> Secciones Asignadas</h3>
                      ${htmlAsignaciones}
                  </div>
                  
                  <div style="display:flex; justify-content:flex-end; margin-top:2rem;">
                      ${USER_ROL_ID === 1 ? `<button class="btn btn--primary" onclick="editarDocente(${d.id})"><i class="fas fa-edit"></i> Editar Docente</button>` : ''}
                  </div>
            `;
        } else {
            content.innerHTML = `<div style="color:red; padding:2rem;">Error: ${data.message}</div>`;
        }
    } catch(err) {
        content.innerHTML = `<div style="color:red; padding:2rem;">Error de conexión</div>`;
    }
}

function cerrarSidePanelDocente() {
    document.getElementById('panelOverlayDocente').style.display = 'none';
    document.getElementById('docenteSidePanel').style.right = '-600px';
}

async function editarDocente(id) {
    cerrarSidePanelDocente();
    const btn = document.querySelector(`button[onclick="verDetallesDocente(${id})"]`);
    if(btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'obtener_detalles');
        formData.append('id', id);
        
        const res = await fetch('api/docentes.php', { method: 'POST', body: formData });
        const data = await res.json();
        if(btn) {
            btn.innerHTML = '<i class="fas fa-id-card"></i> Ficha';
            btn.disabled = false;
        }

        if(data.status === 'success') {
            const d = data.data.docente;
            const form = document.getElementById('formDocente');
            
            document.getElementById('docente_id').value = d.id;
            form.elements['cedula'].value = d.cedula;
            form.elements['nombres'].value = d.nombres;
            form.elements['apellidos'].value = d.apellidos;
            form.elements['grado_instruccion'].value = d.grado_instruccion || '';
            form.elements['especialidad'].value = d.especialidad || '';
            form.elements['telefono'].value = d.telefono_personal || '';
            form.elements['correo'].value = d.correo || '';
            form.elements['turno_id'].value = d.turno_id || '';
            form.elements['direccion'].value = d.direccion || '';
            
            let tienePat = d.patologia && d.patologia !== 'No' ? 'Si' : 'No';
            form.elements['tiene_patologia'].value = tienePat;
            const patDesc = document.getElementById('patologia_desc');
            if(tienePat === 'Si') {
                patDesc.style.display = 'block';
                form.elements['cual_patologia'].value = d.patologia.replace('Si - ', '');
            } else {
                patDesc.style.display = 'none';
                form.elements['cual_patologia'].value = '';
            }

            form.elements['talla_camisa'].value = d.talla_camisa || '';
            form.elements['talla_pantalon'].value = d.talla_pantalon || '';
            form.elements['talla_zapato'].value = d.talla_zapato || '';
            form.elements['edad'].value = d.edad || '';
            form.elements['peso'].value = d.peso || '';
            form.elements['estatura'].value = d.estatura || '';

            const btnRegistro = document.querySelector('button[onclick*="tab-registro"]');
            if(btnRegistro) btnRegistro.click();

            const textGuardar = document.getElementById('textDocente');
            if(textGuardar) textGuardar.textContent = 'Actualizar Docente';

        } else {
            alert('Error: ' + data.message);
        }
    } catch(err) {
        if(btn) {
            btn.innerHTML = '<i class="fas fa-id-card"></i> Ficha';
            btn.disabled = false;
        }
        alert('Error de conexión');
    }
}

function llenarSelects(data) {
    // Select Turno (Registro)
    const selectTurno = document.getElementById('selectTurnoRegistro');
    if (selectTurno) {
        let turnosHtml = '<option value="">Seleccione...</option>';
        data.turnos.forEach(t => { turnosHtml += `<option value="${t.id}">${t.nombre}</option>`; });
        selectTurno.innerHTML = turnosHtml;
    }

    // Select Periodo (Asignación)
    const selectPeriodo = document.getElementById('selectPeriodoAsig');
    if (selectPeriodo) {
        let periodosHtml = '<option value="">Seleccione periodo...</option>';
        data.periodos.forEach(p => { periodosHtml += `<option value="${p.id}">${p.nombre} (${p.estado})</option>`; });
        selectPeriodo.innerHTML = periodosHtml;
    }

    // Select Docente (Asignación)
    const selectDocente = document.getElementById('selectDocenteAsig');
    if (selectDocente) {
        let docentesHtml = '<option value="">Seleccione un docente...</option>';
        data.docentes.forEach(d => { docentesHtml += `<option value="${d.id}">${d.nombres} ${d.apellidos}</option>`; });
        selectDocente.innerHTML = docentesHtml;
    }

    // Select Grado
    // Select Grado/Seccion
    const selectGrado = document.getElementById('selectGradoAsig');
    if (selectGrado) {
        let gradosHtml = '<option value="">Seleccione grado y sección...</option>';
        if (data.clases_activas) {
            data.clases_activas.forEach(c => {
                const jsonVal = JSON.stringify({grado_id: c.grado_id, seccion_id: c.seccion_id});
                const safeVal = jsonVal.replace(/'/g, '&#39;').replace(/"/g, '&quot;');
                gradosHtml += `<option value="${safeVal}">${c.grado_nombre} - Sección ${c.seccion_nombre}</option>`;
            });
        }
        selectGrado.innerHTML = gradosHtml;
    }
}

function imprimirDirectorio() {
    const tablaOriginal = document.querySelector('#tabla_directorio_docentes').cloneNode(true);
    
    // Quitar la columna de "Ver Ficha" (última columna)
    const ths = tablaOriginal.querySelectorAll('thead th');
    const tds = tablaOriginal.querySelectorAll('tbody tr');
    
    if (ths.length > 0) ths[ths.length - 1].remove();
    
    tds.forEach(tr => {
        const celdas = tr.querySelectorAll('td');
        if (celdas.length > 0) {
            celdas[celdas.length - 1].remove();
        }
    });

    const baseUrl = window.location.origin + window.location.pathname.replace(/dashboard\.php$/, '');
    const logoUrl = baseUrl + 'public/img/logo.png';
    const fechaActual = new Date().toLocaleDateString('es-VE', { year: 'numeric', month: 'long', day: 'numeric' });

    const htmlPrint = `
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Directorio Oficial de Docentes Registrados</title>
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
                    <p class="subtitle">Directorio Oficial de Docentes Registrados</p>
                </div>
            </div>
            
            <div class="info-grid">
                <div>Fecha de Emisión: <span style="font-weight:normal;">${fechaActual}</span></div>
                <div>Total Docentes Registrados: <span style="font-weight:normal;">${tds.length}</span></div>
            </div>

            ${tablaOriginal.outerHTML}
            
            <script>
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