let estudiantesData = [];

function iniciarInscripcion(nivel) {
    document.getElementById('seleccion-nivel').style.display = 'none';
    document.getElementById('wizard-inscripcion').style.display = 'block';
    
    const esPreescolar = (nivel === 'Preescolar');
    document.getElementById('es_preescolar').value = esPreescolar ? '1' : '0';
    
    let preInputs = document.getElementById('step-pre-content').querySelectorAll('input, select, textarea');
    preInputs.forEach(input => {
        input.disabled = !esPreescolar;
    });

    if (esPreescolar) {
        document.getElementById('step-pre-indicator').style.display = 'block';
    } else {
        document.getElementById('step-pre-indicator').style.display = 'none';
    }
    
    // Configurar el select de nivel por defecto
    const matEtapa = document.getElementById('mat_etapa');
    if (matEtapa) {
        matEtapa.value = nivel;
        if(typeof actualizarClasesDisponibles === 'function') {
            actualizarClasesDisponibles();
        }
    }
    
    irPaso(1);
}

function cancelarInscripcion() {
    document.getElementById('formInscripcion').reset();
    document.getElementById('form_action').value = 'registrar_estudiante';
    document.getElementById('estudiante_id').value = '';
    document.getElementById('txtBtnGuardar').textContent = 'Procesar Inscripción Completa';
    
    document.getElementById('wizard-inscripcion').style.display = 'none';
    document.getElementById('seleccion-nivel').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => {
    // Agregar asteriscos a campos obligatorios
    document.querySelectorAll('#formInscripcion [required]').forEach(el => {
        const label = el.previousElementSibling;
        if (label && label.tagName === 'LABEL' && !label.innerHTML.includes('*')) {
            label.innerHTML += ' <span style="color:#EF4444; font-weight:bold;">*</span>';
        }
    });

    cargarEstudiantes();
    cargarRepresentantes();
    cargarPeriodosActivos();
    
    const formInscripcion = document.getElementById('formInscripcion');
    if (formInscripcion) {
                formInscripcion.addEventListener('invalid', (e) => {
            e.preventDefault();
            const invalidField = e.target;
            const stepElement = invalidField.closest('[id$="-content"]');
            if (stepElement) {
                const stepId = stepElement.id;
                let pasoNumerico;
                if (stepId === 'step-pre-content') {
                    pasoNumerico = 'pre';
                } else {
                    pasoNumerico = parseInt(stepId.replace('step', '').replace('-content', ''));
                }
                irPaso(pasoNumerico);
                setTimeout(() => invalidField.reportValidity(), 300);
            }
        }, true);

      formInscripcion.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnGuardarInscripcion');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span id="txtBtnGuardar">Guardando...</span>';
            btn.disabled = true;

            const formData = new FormData(formInscripcion);
            
            try {
                const res = await fetch('api/estudiantes.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.status === 'success') {
                    mostrarAlertaEstudiante('success', data.message);
                    document.getElementById('wizard-inscripcion').style.display = 'none';
                    document.getElementById('formInscripcion').reset();
                    document.getElementById('form_action').value = 'registrar_estudiante';
                    document.getElementById('estudiante_id').value = '';
                    document.getElementById('txtBtnGuardar').textContent = 'Procesar Inscripción Completa';
                    document.getElementById('step-pre-indicator').style.display = 'none';
                    
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarAlertaEstudiante('error', data.message);
                }
            } catch (err) {
                console.error("Error en inscripcion:", err);
                mostrarAlertaEstudiante('error', 'Error de conexión: ' + err.message);
            } finally {
                btn.innerHTML = '<i class="fas fa-save"></i> <span id="txtBtnGuardar">Procesar Inscripción Completa</span>';
                btn.disabled = false;
            }
        });
    }
});

function openTabEstudiante(tabId, btnElement) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    
    if (btnElement) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');
    }
}

function mostrarAlertaEstudiante(tipo, mensaje) {
    if (tipo === 'success') {
        Swal.fire('¡Éxito!', mensaje, 'success');
    } else {
        Swal.fire('Error', mensaje, 'error');
    }
}

// Navegación del Wizard
function irPaso(pasoDestino) {
    // Si estamos en el paso 2 y queremos avanzar
    if (pasoDestino === 'siguiente-despues-medico') {
        const etapa = document.getElementById('mat_etapa').value;
        if (etapa === 'Preescolar') {
            pasoDestino = 'pre';
        } else {
            pasoDestino = 3;
        }
    }

    // Ocultar todos los pasos
    document.getElementById('step1-content').style.display = 'none';
    document.getElementById('step2-content').style.display = 'none';
    document.getElementById('step-pre-content').style.display = 'none';
    document.getElementById('step3-content').style.display = 'none';
    document.getElementById('step4-content').style.display = 'none';

    // Reset de colores en indicadores
    document.getElementById('step1-indicator').style.color = '#94A3B8';
    document.getElementById('step1-indicator').style.fontWeight = 'normal';
    document.getElementById('step2-indicator').style.color = '#94A3B8';
    document.getElementById('step2-indicator').style.fontWeight = 'normal';
    document.getElementById('step-pre-indicator').style.color = '#94A3B8';
    document.getElementById('step-pre-indicator').style.fontWeight = 'normal';
    document.getElementById('step3-indicator').style.color = '#94A3B8';
    document.getElementById('step3-indicator').style.fontWeight = 'normal';
    document.getElementById('step4-indicator').style.color = '#94A3B8';
    document.getElementById('step4-indicator').style.fontWeight = 'normal';

    // Mostrar el paso actual
    let currentIndicator = '';
    if (pasoDestino === 1) {
        document.getElementById('step1-content').style.display = 'block';
        currentIndicator = 'step1-indicator';
    } else if (pasoDestino === 2) {
        document.getElementById('step2-content').style.display = 'block';
        currentIndicator = 'step2-indicator';
    } else if (pasoDestino === 'pre') {
        document.getElementById('step-pre-content').style.display = 'block';
        document.getElementById('step-pre-indicator').style.display = 'block';
        currentIndicator = 'step-pre-indicator';
    } else if (pasoDestino === 3) {
        document.getElementById('step3-content').style.display = 'block';
        currentIndicator = 'step3-indicator';
    } else if (pasoDestino === 4) {
        document.getElementById('step4-content').style.display = 'block';
        currentIndicator = 'step4-indicator';
    }

    // Resaltar indicador
    const ind = document.getElementById(currentIndicator);
    if(ind) {
        ind.style.color = 'var(--primary)';
        ind.style.fontWeight = '600';
    }
}

// Búsqueda de representante
async function buscarRepresentante() {
    const cedula = document.getElementById('buscar_cedula_rep').value.trim();
    if (!cedula) return;

    try {
        const formData = new FormData();
        formData.append('action', 'buscar_representante');
        formData.append('cedula', cedula);

        const res = await fetch('api/estudiantes.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            const rep = data.data;
            document.getElementById('rep_cedula').value = rep.cedula;
            document.getElementById('rep_nombres').value = rep.nombres;
            document.getElementById('rep_apellidos').value = rep.apellidos;
            document.getElementById('rep_parentesco').value = rep.parentesco || 'Madre/Padre';
            document.getElementById('rep_telefono').value = rep.telefono;
            document.getElementById('rep_telefono_hab').value = rep.telefono_habitacion || '';
            document.getElementById('rep_profesion').value = rep.profesion || '';
            document.getElementById('rep_instruccion').value = rep.grado_instruccion || '';
            document.getElementById('rep_correo').value = rep.correo || '';
            
            // Highlight para feedback visual
            document.getElementById('rep_nombres').style.background = '#e0ffe0';
            setTimeout(() => document.getElementById('rep_nombres').style.background = '', 1000);
        } else {
            alert('Representante no encontrado en la base de datos. Por favor, introduzca los datos manualmente.');
            document.getElementById('rep_cedula').value = cedula;
            document.getElementById('rep_nombres').value = '';
            document.getElementById('rep_apellidos').value = '';
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al buscar representante.');
    }
}

async function cargarPeriodosActivos() {
    try {
        const res = await fetch('api/configuracion.php', {
            method: 'POST',
            body: new URLSearchParams({'action': 'listar_estructura'})
        });
        const data = await res.json();
        
        const selPeriodo = document.getElementById('mat_periodo');
        const filtroEst = document.getElementById('filtro_periodo_est');
        const filtroRep = document.getElementById('filtro_periodo_rep');
        
        if (data.status === 'success') {
            let opciones = '';
            let opcionesFiltro = '';
            let hayActivo = false;
            data.data.periodos.forEach(p => {
                if (p.estado === 'Abierto' && !hayActivo) {
                    opciones += `<option value="${p.id}" selected>${p.nombre} (Activo)</option>`;
                    opcionesFiltro += `<option value="${p.id}" selected>${p.nombre} (Activo)</option>`;
                    hayActivo = true;
                } else {
                    opciones += `<option value="${p.id}">${p.nombre}</option>`;
                    opcionesFiltro += `<option value="${p.id}">${p.nombre}</option>`;
                }
            });
            if (selPeriodo) selPeriodo.innerHTML = opciones;
            if (filtroEst) {
                filtroEst.innerHTML = opcionesFiltro;
                // Emitir evento change para que cargue la lista automáticamente con este valor
                filtroEst.dispatchEvent(new Event('change'));
            }
            if (filtroRep) {
                filtroRep.innerHTML = opcionesFiltro;
                filtroRep.dispatchEvent(new Event('change'));
            }
        }
    } catch(err) {
        console.error(err);
    }
}

async function actualizarClasesDisponibles() {
    const etapa = document.getElementById('mat_etapa').value;
    const selClase = document.getElementById('mat_clase');
    
    // Mostrar u ocultar el paso de Preescolar en el wizard
    const preIndicator = document.getElementById('step-pre-indicator');
    const preInputs = document.querySelectorAll('#step-pre-content input, #step-pre-content select, #step-pre-content textarea');
    if (etapa === 'Preescolar') {
        if(preIndicator) preIndicator.style.display = 'block';
        preInputs.forEach(input => input.disabled = false);
    } else {
        if(preIndicator) preIndicator.style.display = 'none';
        preInputs.forEach(input => input.disabled = true);
    }

    if (!etapa) {
        selClase.innerHTML = '<option value="">Seleccione primero la etapa...</option>';
        return;
    }

    selClase.innerHTML = '<option value="">Cargando clases...</option>';
    
    try {
        const formData = new FormData();
        formData.append('action', 'listar_clases_etapa');
        formData.append('etapa_nombre', etapa);

        const res = await fetch('api/estudiantes.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            let opciones = '<option value="">Seleccione Clase (Turno - Grado - Sección)...</option>';
            data.data.forEach(c => {
                const turno = c.turno || 'Mañana';
                const turnoId = c.turno_id ? c.turno_id : 1;
                const valStr = JSON.stringify({
                    grado_id: c.grado_id, 
                    seccion_id: c.seccion_id, 
                    turno_id: turnoId,
                    nivel_id: c.nivel_id
                });
                const safeVal = valStr.replace(/'/g, '&apos;');
                opciones += `<option value='${safeVal}'>Turno ${turno} - ${c.grado} - Sección ${c.seccion}</option>`;
            });
            selClase.innerHTML = opciones;
        } else {
            selClase.innerHTML = `<option value="">Error: ${data.message}</option>`;
        }
    } catch(err) {
        console.error(err);
        selClase.innerHTML = '<option value="">Error de conexión</option>';
    }
}

async function cargarEstudiantes() {
    const tbody = document.getElementById('listaEstudiantes');
    if(!tbody) return;

    try {
        const periodo_id = document.getElementById('filtro_periodo_est') ? document.getElementById('filtro_periodo_est').value : '';
        const formData = new FormData();
        formData.append('action', 'listar');
        if (periodo_id) formData.append('periodo_id', periodo_id);
        const res = await fetch('api/estudiantes.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            estudiantesData = data.data;
            renderEstudiantes(estudiantesData);
            cargarStatsMatricula();
        }
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="8" style="color:red; text-align:center;">Error cargando estudiantes.</td></tr>';
    }
}

function renderEstudiantes(datos) {
    const tbody = document.getElementById('listaEstudiantes');
    tbody.innerHTML = '';
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No se encontraron estudiantes con esos criterios.</td></tr>';
        return;
    }

    datos.forEach(e => {
        const condicionClass = e.condicion === 'Nuevo Ingreso' ? 'background: #3B82F6;' : (e.condicion === 'Regular' ? 'background: #10B981;' : 'background: #F59E0B;');
        tbody.innerHTML += `
            <tr>
                <td><strong>${e.cedula_escolar}</strong></td>
                <td>${e.nombres} ${e.apellidos}</td>
                <td>${e.edad} años</td>
                <td>${e.grado} "${e.seccion}"</td>
                <td>${e.turno || 'N/A'}</td>
                <td><span style="${condicionClass} color:white; padding: 0.2rem 0.5rem; border-radius:12px; font-size:0.85rem;">${e.condicion}</span></td>
                <td>${e.rep_nombres} ${e.rep_apellidos}</td>
                <td style="text-align:center;">
                    <button class="btn btn--primary" style="padding:0.4rem 0.8rem; font-size:0.85rem;" onclick="verDetallesEstudiante(${e.id})" title="Ver Ficha Completa"><i class="fas fa-id-card"></i> Ficha</button>
                </td>
            </tr>
        `;
    });
}

function filtrarEstudiantes() {
    const q = document.getElementById('filtro_busqueda').value.toLowerCase();
    const turno = document.getElementById('filtro_turno').value;
    const condicion = document.getElementById('filtro_condicion').value;
    const nivel = document.getElementById('filtro_nivel') ? document.getElementById('filtro_nivel').value : '';

    const filtrados = estudiantesData.filter(e => {
        const matchQ = e.nombres.toLowerCase().includes(q) || e.apellidos.toLowerCase().includes(q) || e.cedula_escolar.toLowerCase().includes(q);
        const matchTurno = turno === '' || e.turno === turno;
        const matchCondicion = condicion === '' || e.condicion === condicion;
        const matchNivel = nivel === '' || e.nivel === nivel;
        return matchQ && matchTurno && matchCondicion && matchNivel;
    });

    renderEstudiantes(filtrados);
}

async function cargarStatsMatricula() {
    try {
        const formData = new FormData();
        formData.append('action', 'stats_matricula');
        const res = await fetch('api/estudiantes.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            document.getElementById('kpi-total').textContent = data.data.total;
            
            let htmlTurnos = '';
            data.data.turnos.forEach(t => { htmlTurnos += `${t.nombre}: ${t.cantidad}<br>`; });
            document.getElementById('kpi-turnos').innerHTML = htmlTurnos || 'Sin datos';

            let htmlEtapas = '';
            data.data.etapas.forEach(t => { htmlEtapas += `${t.nombre}: ${t.cantidad}<br>`; });
            document.getElementById('kpi-etapas').innerHTML = htmlEtapas || 'Sin datos';
        }
    } catch(err) {
        console.error("Error al cargar stats", err);
    }
}

async function verDetallesEstudiante(id) {
    const overlay = document.getElementById('panelOverlay');
    const panel = document.getElementById('studentSidePanel');
    const content = document.getElementById('panelContent');
    
    overlay.style.display = 'block';
    panel.style.right = '0';
    content.innerHTML = '<div style="text-align:center; padding: 2rem;"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando...</div>';

    try {
        const formData = new FormData();
        formData.append('action', 'obtener_detalles');
        formData.append('id', id);
        
        const res = await fetch('api/estudiantes.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            const d = data.data;
            const e = d.estudiante;
            const r = d.representante;
            const m = d.medicos || {};
            const v = d.vacunas || {};
            const s = d.sociales || {};
            const p = d.preescolar || null;
            
            let preescolarHtml = '';
            if (p) {
                preescolarHtml = `
                <div style="margin-bottom: 1.5rem; background: var(--secondary-light); padding: 1rem; border-radius: 8px;">
                    <h3 style="color:var(--secondary-dark); margin-bottom:0.5rem; border-bottom:1px solid #CBD5E1; padding-bottom:0.3rem;"><i class="fas fa-shapes"></i> Datos de Preescolar</h3>
                    <p><strong>¿Quién lo cuida?:</strong> ${p.quien_lo_cuida || 'N/A'}</p>
                    <p><strong>Actividades preferidas:</strong> ${p.tipo_actividades || 'N/A'}</p>
                    <p><strong>¿Con quién juega?:</strong> ${p.con_quien_juega || 'N/A'}</p>
                    <p><strong>¿Ha estado en guardería?:</strong> ${p.ha_estado_guarderia === 'Si' ? 'Sí' : 'No'}</p>
                    ${p.otro_preescolar === 'Si' ? `<p><strong>Preescolar anterior:</strong> ${p.cual_preescolar || 'N/A'}</p>` : ''}
                    ${p.observado_dificultad === 'Si' ? `<p><strong>Dificultades observadas:</strong> ${p.dificultad_observada || 'N/A'}</p>` : ''}
                    <p><strong>Especialistas consultados:</strong> 
                        Psicólogo (${p.consulta_psicologo === 'Si' ? 'Sí' : 'No'}), 
                        Psicopedagogo (${p.consulta_psicopedagogo === 'Si' ? 'Sí' : 'No'}), 
                        Neurólogo (${p.consulta_neurologo === 'Si' ? 'Sí' : 'No'}), 
                        Terapeuta Lenguaje (${p.consulta_terapeuta_lenguaje === 'Si' ? 'Sí' : 'No'})
                    </p>
                </div>`;
            }

            content.innerHTML = `
                <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--border); padding-bottom: 1rem;">
                    <div style="width:80px; height:80px; border-radius:50%; background:var(--bg-color); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:var(--text-muted);">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h2 style="margin:0; font-size:1.5rem; color:var(--text-main);">${e.nombres} ${e.apellidos}</h2>
                        <div style="color:var(--text-muted); margin-top:0.3rem;"><i class="fas fa-id-card"></i> ${e.cedula_escolar} | <i class="fas fa-calendar"></i> ${e.edad} años</div>
                        <div style="margin-top:0.5rem;"><span style="background:var(--primary); color:white; padding:0.2rem 0.5rem; border-radius:12px; font-size:0.8rem;">${e.nivel} - ${e.grado} "${e.seccion}" (${e.turno || ''})</span></div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color:var(--primary); margin-bottom:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.3rem;"><i class="fas fa-user-tie"></i> Representante Legal</h3>
                    <p><strong>Nombre:</strong> ${r.nombres} ${r.apellidos} (${r.parentesco})</p>
                    <p><strong>Cédula:</strong> ${r.cedula}</p>
                    <p><strong>Teléfonos:</strong> ${r.telefono} / ${r.telefono_habitacion || 'N/A'}</p>
                    <p><strong>En caso de emergencia llamar a:</strong> ${e.representante_emergencia || 'N/A'}</p>
                    <p><strong>Teléfono de contacto de emergencia:</strong> ${e.telefono_emergencia || 'N/A'}</p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color:var(--primary); margin-bottom:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.3rem;"><i class="fas fa-heartbeat"></i> Médicos y Vacunas</h3>
                    <p><strong>Alergias:</strong> ${m.es_alergico === 'Si' ? 'Sí (' + m.alergico_a + ')' : 'No'}</p>
                    <p><strong>Medicamentos:</strong> ${m.toma_medicamento === 'Si' ? 'Sí (' + m.medicamentos_que_toma + ')' : 'No'}</p>
                    <p><strong>Operaciones:</strong> ${m.ha_sido_operado === 'Si' ? 'Sí (' + m.de_que_operado + ')' : 'No'}</p>
                    <p><strong>Patología/Enfermedad:</strong> ${m.tiene_patologia === 'Si' ? 'Sí (' + m.patologia_que_padece + ')' : 'No'}</p>
                    <p><strong>Condición Neurodivergente:</strong> ${m.condicion_neurodivergente === 'Si' ? 'Sí (' + m.informe_neurologico + ')' : 'No'}</p>
                    <p><strong>Vacunas Principales:</strong> 
                        Triple (${v.triple ? 'Sí' : 'No'}), 
                        Polio (${v.polio ? 'Sí' : 'No'}), 
                        BGC (${v.bgc ? 'Sí' : 'No'})
                    </p>
                    <p><strong>Otras vacunas:</strong> ${v.otras_vacunas || 'Ninguna'}</p>
                </div>
                
                ${preescolarHtml}
                
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color:var(--primary); margin-bottom:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.3rem;"><i class="fas fa-home"></i> Datos Sociales</h3>
                    <p><strong>Vive con:</strong> ${s.con_quien_vive || 'N/A'} (${s.parentesco_con_quien_vive || 'N/A'})</p>
                    <p><strong>N° Hermanos:</strong> ${s.numero_hermanos || 0} (Lugar: ${s.lugar_ocupa || 'N/A'})</p>
                    <p><strong>Vivienda:</strong> ${s.tipo_vivienda || 'N/A'} (${s.tenencia_vivienda || 'N/A'})</p>
                </div>
                
                <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:2rem;">
                    ${USER_ROL_ID === 1 ? `<button class="btn btn--primary" onclick="editarEstudiante(${id})"><i class="fas fa-edit"></i> Editar Estudiante</button>` : ''}
                    <button class="btn btn--outline" onclick="imprimirConstancia(${id})"><i class="fas fa-file-signature"></i> Constancia de Estudio</button>
                </div>
            `;
        } else {
            content.innerHTML = `<div style="color:var(--danger); padding:2rem;">Error: ${data.message}</div>`;
        }
    } catch(err) {
        content.innerHTML = `<div style="color:var(--danger); padding:2rem;">Error de conexión</div>`;
    }
}

function cerrarSidePanel() {
    document.getElementById('panelOverlay').style.display = 'none';
    document.getElementById('studentSidePanel').style.right = '-600px';
}

function imprimirLista() {
    const tablaOriginal = document.querySelector('#tabla_matricula').cloneNode(true);
    
    // Quitar la columna de "Acciones" (última columna)
    const ths = tablaOriginal.querySelectorAll('thead th');
    const tds = tablaOriginal.querySelectorAll('tbody tr');
    
    if (ths.length > 0) ths[ths.length - 1].remove();
    
    tds.forEach(tr => {
        const celdas = tr.querySelectorAll('td');
        if (celdas.length > 0) {
            // Eliminar botón de acciones
            celdas[celdas.length - 1].remove();
            
            // Quitar avatar de Nombres (índice 1)
            const divAvatar = celdas[1].querySelector('div');
            if (divAvatar) divAvatar.remove();
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
            <title>Directorio Oficial de Estudiantes Registrados</title>
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
                    <p class="subtitle">Directorio Oficial de Estudiantes Registrados</p>
                </div>
            </div>
            
            <div class="info-grid">
                <div>Fecha de Emisión: <span style="font-weight:normal;">${fechaActual}</span></div>
                <div>Total Estudiantes Registrados: <span style="font-weight:normal;">${tds.length}</span></div>
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

function imprimirPlanilla(id) {
    window.open('views/reporte_ficha.php?id=' + id, '_blank');
}

function imprimirConstancia(id) {
    window.open('views/reporte_constancia.php?id=' + id, '_blank');
}

async function editarEstudiante(id) {
    try {
        const response = await fetch(`api/estudiantes.php?action=obtener_detalles&id=${id}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            const data = result.data;
            const e = data.estudiante || {};
            const r = data.representante || {};
            const antro = data.antropometricos || {};
            const med = data.medicos || {};
            const vac = data.vacunas || {};
            const soc = data.sociales || {};
            const pre = data.preescolar || {};
            
            // Set action to update
            document.getElementById('form_action').value = 'editar_estudiante';
            document.getElementById('estudiante_id').value = e.id;
            document.getElementById('txtBtnGuardar').textContent = 'Actualizar Estudiante';
            
            // Populate Estudiante (Step 1)
            document.querySelector('[name="est_cedula"]').value = e.cedula_escolar || '';
            document.querySelector('[name="est_nombres"]').value = e.nombres || '';
            document.querySelector('[name="est_apellidos"]').value = e.apellidos || '';
            document.querySelector('[name="est_fecha_nac"]').value = e.fecha_nacimiento || '';
            document.querySelector('[name="est_sexo"]').value = e.sexo || '';
            document.querySelector('[name="est_pais"]').value = e.pais_entidad || '';
            document.querySelector('[name="est_municipio"]').value = e.municipio || '';
            document.querySelector('[name="est_parroquia"]').value = e.parroquia || '';
            document.querySelector('[name="est_direccion"]').value = e.direccion || '';
            document.querySelector('[name="est_vive_con"]').value = e.vive_con || '';
            document.querySelector('[name="est_emergencia_nombre"]').value = e.representante_emergencia || '';
            document.querySelector('[name="est_emergencia_telefono"]').value = e.telefono_emergencia || '';
            
            // Populate Médico y Antropometrico (Step 2)
            if (Object.keys(antro).length > 0) {
                document.querySelector('[name="antro_edad"]').value = antro.edad || '';
                document.querySelector('[name="antro_peso"]').value = antro.peso || '';
                document.querySelector('[name="antro_estatura"]').value = antro.estatura || '';
                document.querySelector('[name="antro_camisa"]').value = antro.talla_camisa || '';
                document.querySelector('[name="antro_pantalon"]').value = antro.talla_pantalon || '';
                document.querySelector('[name="antro_zapato"]').value = antro.talla_zapato || '';
            }
            if (Object.keys(med).length > 0) {
                document.querySelector('[name="med_embarazo"]').value = med.enfermedad_embarazo || '';
                document.querySelector('[name="med_parto"]').value = med.como_fue_parto || '';
                
                const setSelectAndTxt = (selectName, txtName, valueSiNo, txtValue) => {
                    if(valueSiNo == 1) {
                        document.querySelector(`[name="${selectName}"]`).value = 'Si';
                        document.querySelector(`[name="${txtName}"]`).style.display = 'block';
                        document.querySelector(`[name="${txtName}"]`).value = txtValue || '';
                    } else {
                        document.querySelector(`[name="${selectName}"]`).value = 'No';
                        document.querySelector(`[name="${txtName}"]`).style.display = 'none';
                        document.querySelector(`[name="${txtName}"]`).value = '';
                    }
                };
                
                setSelectAndTxt('med_alergias', 'med_alergias_txt', med.es_alergico, med.alergico_a);
                setSelectAndTxt('med_medicamentos', 'med_medicamentos_txt', med.toma_medicamento, med.cuales_medicamentos);
                setSelectAndTxt('med_neurodivergente', 'med_neurodivergente_txt', med.condicion_neurodivergente, med.cual_condicion);
                setSelectAndTxt('med_informe', 'med_informe_txt', med.tiene_informe, med.especialidad_informe);
                setSelectAndTxt('med_operaciones', 'med_operaciones_txt', med.ha_sido_operado, med.operacion_de_que);
                setSelectAndTxt('med_patologia', 'med_patologia_txt', med.tiene_patologia, med.cual_patologia);
            }
            if (Object.keys(vac).length > 0) {
                const vacunasMap = {
                    'vac_triple': vac.triple, 'vac_antivariolica': vac.antivariolica, 'vac_sarampion': vac.sarampion,
                    'vac_rubeola': vac.rubeola, 'vac_antitifica': vac.antitifica, 'vac_antiamarilica': vac.antiamarilica,
                    'vac_bgc': vac.bgc, 'vac_polio': vac.polio
                };
                for (let vName in vacunasMap) {
                    document.querySelector(`[name="${vName}"]`).value = vacunasMap[vName] == 1 ? 'Si' : 'No';
                }
                document.querySelector('[name="vac_otras"]').value = vac.otras_vacunas || '';
            }

            // Populate Preescolar (Step Pre)
            if (e.nivel_id == 1 || Object.keys(pre).length > 0) {
                document.getElementById('es_preescolar').value = '1';
                document.getElementById('step-pre-indicator').style.display = 'block';
                
                if (Object.keys(pre).length > 0) {
                    document.querySelector('[name="pre_quien_cuida"]').value = pre.quien_cuida || '';
                    document.querySelector('[name="pre_actividades_prefiere"]').value = pre.actividades_preferidas || '';
                    document.querySelector('[name="pre_con_quien_juega"]').value = pre.con_quien_juega || '';
                    
                    if(pre.estado_guarderia == 1) {
                        document.querySelector('[name="pre_estado_guarderia"]').value = 'Si';
                        document.getElementById('pre_guarderia_nombre').style.display = 'none'; // Since we dont save name anymore just si or no
                    } else {
                        document.querySelector('[name="pre_estado_guarderia"]').value = 'No';
                    }
                    
                    if(pre.otro_prescolar == 1) {
                        document.querySelector('[name="pre_otro_preescolar"]').value = 'Si';
                        document.getElementById('pre_cual_preescolar').style.display = 'block';
                        document.querySelector('[name="pre_cual_preescolar_txt"]').value = pre.cual_prescolar || '';
                    } else {
                        document.querySelector('[name="pre_otro_preescolar"]').value = 'No';
                        document.getElementById('pre_cual_preescolar').style.display = 'none';
                    }
                    
                    if(pre.dificultad_observada == 1) {
                        document.querySelector('[name="pre_dificultad_observada"]').value = 'Si';
                        document.getElementById('pre_dificultad_tipo').style.display = 'block';
                        document.getElementById('pre_dificultad_tipo_hidden').value = pre.tipo_dificultad || '';
                        
                        let difs = (pre.tipo_dificultad || '').split(', ');
                        document.querySelectorAll('#dificultades_checkboxes input').forEach(cb => {
                            cb.checked = difs.includes(cb.value);
                        });
                    } else {
                        document.querySelector('[name="pre_dificultad_observada"]').value = 'No';
                        document.getElementById('pre_dificultad_tipo').style.display = 'none';
                        document.querySelectorAll('#dificultades_checkboxes input').forEach(cb => cb.checked = false);
                        document.getElementById('pre_dificultad_tipo_hidden').value = '';
                    }
                    
                    document.querySelector('[name="pre_consulta_psicologo"]').value = pre.consulta_psicologo == 1 ? 'Si' : 'No';
                    document.querySelector('[name="pre_consulta_psicopedagogo"]').value = pre.consulta_psicopedagogo == 1 ? 'Si' : 'No';
                    document.querySelector('[name="pre_consulta_neurologo"]').value = pre.consulta_neurologo == 1 ? 'Si' : 'No';
                    document.querySelector('[name="pre_consulta_terapeuta"]').value = pre.consulta_terapeuta_lenguaje == 1 ? 'Si' : 'No';
                }
            } else {
                document.getElementById('es_preescolar').value = '0';
                document.getElementById('step-pre-indicator').style.display = 'none';
            }

            // Populate Representante (Step 3)
            if (Object.keys(r).length > 0) {
                document.getElementById('rep_cedula').value = r.cedula || '';
                document.getElementById('rep_nombres').value = r.nombres || '';
                document.getElementById('rep_apellidos').value = r.apellidos || '';
                document.getElementById('rep_parentesco').value = r.parentesco || '';
                document.getElementById('rep_telefono').value = r.telefono || '';
                document.getElementById('rep_telefono_hab').value = r.telefono_habitacion || '';
                document.getElementById('rep_profesion').value = r.profesion || '';
                document.getElementById('rep_instruccion').value = r.grado_instruccion || '';
                document.getElementById('rep_correo').value = r.correo || '';
            }

            // Populate Social (Step 3 part 2)
            if (Object.keys(soc).length > 0) {
                document.querySelector('[name="soc_doc_legal"]').value = soc.documento_legal_representante == 1 ? 'Si' : 'No';
                document.querySelector('[name="soc_organismo"]').value = soc.organismo_documento || '';
                document.querySelector('[name="soc_pareja"]').value = soc.situacion_pareja || '';
                document.querySelector('[name="soc_viven_juntos"]').value = soc.viven_juntos == 1 ? 'Si' : 'No';
                document.querySelector('[name="soc_con_quien"]').value = soc.nombres_con_quien_vive || '';
                document.querySelector('[name="soc_parentesco_vive"]').value = soc.parentesco_con_quien_vive || '';
                document.querySelector('[name="soc_hermanos"]').value = soc.numero_hermanos || '';
                document.querySelector('[name="soc_lugar_hermanos"]').value = soc.lugar_ocupa || '';
                document.querySelector('[name="soc_tipo_vivienda"]').value = soc.tipo_vivienda || '';
                document.querySelector('[name="soc_tenencia"]').value = soc.tenencia_vivienda || '';
            }

            // Populate Matricula (Step 4)
            if (e.periodo_id) {
                document.getElementById('mat_periodo').value = e.periodo_id;
                document.querySelector('[name="mat_condicion"]').value = e.condicion || '';
                
                const matEtapa = document.getElementById('mat_etapa');
                if (matEtapa) {
                    matEtapa.value = e.nivel_id == 1 ? 'Preescolar' : 'Primaria';
                    await actualizarClasesDisponibles();
                }
                
                let valMatClase = JSON.stringify({
                    grado_id: e.grado_id,
                    seccion_id: e.seccion_id,
                    turno_id: e.turno_id,
                    nivel_id: e.nivel_id
                });
                document.getElementById('mat_clase').value = valMatClase;
            }

            // Close Side Panel and Switch Tab
            cerrarSidePanel();
            let selNivelEl = document.getElementById('seleccion-nivel');
            if(selNivelEl) selNivelEl.style.display = 'none';
            document.getElementById('wizard-inscripcion').style.display = 'block';
            
            // Switch to form tab
            const tabBtn = document.querySelector('button[onclick*="tab-inscripcion"]');
            if(tabBtn) {
                openTabEstudiante('tab-inscripcion', tabBtn);
            }
            
            // Start at step 1
            irPaso(1);

        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Error JS', e.stack ? e.stack.toString() : e.message, 'error');
    }
}


function validarPasoActual() {
    let contenedores = ['step1-content', 'step2-content', 'step-pre-content', 'step3-content', 'step4-content'];
    for(let id of contenedores) {
        let el = document.getElementById(id);
        if(el && el.style.display !== 'none') {
            let inputs = el.querySelectorAll('input[required], select[required], textarea[required]');
                          for(let input of inputs) {
                  if(input.offsetParent !== null && !input.checkValidity()) {
                      Swal.fire({
                          icon: 'warning',
                          title: 'Campos Incompletos',
                          text: 'Ingrese los datos en los campos obligatorios',
                          confirmButtonColor: '#3B82F6'
                      }).then(() => {
                          input.reportValidity();
                      });
                      return false;
                  }
              }
        }
    }
    return true;
}


let representantesData = [];

async function cargarRepresentantes() {
    const tbody = document.getElementById('listaRepresentantes');
    if(!tbody) return;

    try {
        const periodo_id = document.getElementById('filtro_periodo_rep') ? document.getElementById('filtro_periodo_rep').value : '';
        const formData = new FormData();
        formData.append('action', 'listar');
        if (periodo_id) formData.append('periodo_id', periodo_id);
        const res = await fetch('api/representantes.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status === 'success') {
            representantesData = data.data;
            renderRepresentantes(representantesData);
        }
    } catch (err) {
        tbody.innerHTML = '<tr><td colspan="5" style="color:red; text-align:center;">Error cargando representantes.</td></tr>';
        console.error(err);
    }
}

function renderRepresentantes(data) {
    const tbody = document.getElementById('listaRepresentantes');
    if(!tbody) return;
    tbody.innerHTML = '';

    // Actualizar KPIs
    document.getElementById('kpi-total-rep').innerText = data.length;
    let multiples = 0;
    data.forEach(rep => {
        if (rep.estudiantes_asociados && rep.estudiantes_asociados.split(',').length > 1) {
            multiples++;
        }
    });
    document.getElementById('kpi-multiples-rep').innerText = multiples;

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#64748b;">No hay representantes registrados</td></tr>';
        return;
    }

    data.forEach(rep => {
        const tr = document.createElement('tr');
        
        // Generar etiquetas (badges) para estudiantes
        let estAsocHTML = '<em style="color:#94a3b8;">Ninguno</em>';
        if (rep.estudiantes_asociados) {
            const estudiantes = rep.estudiantes_asociados.split(',');
            estAsocHTML = estudiantes.map(est => `<span class="badge badge--success" style="margin-right:4px; margin-bottom:4px; display:inline-block;">${est.trim()}</span>`).join('');
        }

        // Generar Avatar con Iniciales
        const inicial = rep.nombres ? rep.nombres.charAt(0).toUpperCase() : '?';
        const avatarHTML = `<div style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; background:var(--primary); color:white; font-weight:bold; margin-right:10px;">${inicial}</div>`;

        tr.innerHTML = `
            <td>${rep.cedula}</td>
            <td style="display:flex; align-items:center; font-weight:bold;">${avatarHTML} ${rep.nombres} ${rep.apellidos}</td>
            <td>${rep.telefono}</td>
            <td>${rep.correo || '-'}</td>
            <td>${estAsocHTML}</td>
            <td>
                <button type="button" class="btn btn--primary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;" onclick="verDetallesRepresentante(${rep.id})"><i class="fas fa-eye"></i> Detalles</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filtrarRepresentantes() {
    const input = document.getElementById('filtro_busqueda_rep').value.toLowerCase();
    const filtrados = representantesData.filter(rep => {
        return rep.nombres.toLowerCase().includes(input) || 
               rep.apellidos.toLowerCase().includes(input) || 
               rep.cedula.toLowerCase().includes(input);
    });
    renderRepresentantes(filtrados);
}
function imprimirRepresentantes() {
    const tablaOriginal = document.querySelector('#tablaRepresentantes').cloneNode(true);
    
    // Quitar la columna de "Acciones" (última columna)
    const ths = tablaOriginal.querySelectorAll('thead th');
    const tds = tablaOriginal.querySelectorAll('tbody tr');
    
    if (ths.length > 0) ths[ths.length - 1].remove();
    
    tds.forEach(tr => {
        const celdas = tr.querySelectorAll('td');
        if (celdas.length > 0) {
            // Eliminar botón de acciones
            celdas[celdas.length - 1].remove();
            
            // Quitar avatar de Nombres (índice 1)
            const divAvatar = celdas[1].querySelector('div');
            if (divAvatar) divAvatar.remove();
            
            // Convertir badges en texto separado por comas en Estudiantes Asociados (índice 4)
            const badges = celdas[4].querySelectorAll('.badge');
            if (badges.length > 0) {
                const nombres = Array.from(badges).map(b => b.textContent.trim());
                celdas[4].innerHTML = nombres.join(', ');
            }
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
            <title>Directorio de Representantes</title>
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
                    <p class="subtitle">Directorio Oficial de Representantes Registrados</p>
                </div>
            </div>
            
            <div class="info-grid">
                <div>Fecha de Emisión: <span style="font-weight:normal;">${fechaActual}</span></div>
                <div>Total Representantes Registrados: <span style="font-weight:normal;">${tds.length}</span></div>
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
async function verDetallesRepresentante(id) {
    const overlay = document.getElementById('panelOverlay');
    const panel = document.getElementById('studentSidePanel');
    const content = document.getElementById('panelContent');
    
    overlay.style.display = 'block';
    panel.style.right = '0';
    content.innerHTML = '<div style="text-align:center; padding: 2rem;"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando detalles...</div>';

    try {
        const formData = new FormData();
        formData.append('action', 'obtener_detalles');
        formData.append('id', id);
        
        const res = await fetch('api/representantes.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.status === 'success') {
            const rep = data.data.representante;
            const estudiantes = data.data.estudiantes;
            
            let estHTML = '';
            if (estudiantes.length > 0) {
                estHTML = '<ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.5rem;">';
                estudiantes.forEach(est => {
                    estHTML += `
                    <li style="padding:0.75rem; background:#F8FAFC; border-radius:0.5rem; border:1px solid #E2E8F0; display:flex; align-items:center; gap:0.75rem; transition: background 0.2s;">
                        <div style="width:36px; height:36px; background:#EFF6FF; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#3B82F6; font-weight:bold; font-size:0.9rem;">
                            ${est.nombres.charAt(0)}${est.apellidos.charAt(0)}
                        </div>
                        <div>
                            <div style="font-weight:bold; font-size:0.95rem; color:#1E293B;">${est.nombres} ${est.apellidos}</div>
                            <div style="font-size:0.8rem; color:#64748B;"><i class="fas fa-id-badge"></i> C.I: ${est.cedula_escolar || 'No posee'}</div>
                        </div>
                    </li>`;
                });
                estHTML += '</ul>';
            } else {
                estHTML = '<div style="padding:0.75rem; background:#F8FAFC; border-radius:0.5rem; color:#64748B; font-size:0.9rem; text-align:center;">No hay estudiantes asociados en el periodo actual.</div>';
            }

            content.innerHTML = `
                <div style="padding-bottom: 1.5rem; border-bottom: 1px solid #E2E8F0; margin-bottom: 1.5rem; display:flex; align-items:center; gap:1rem;">
                    <div style="width:65px; height:65px; background:linear-gradient(135deg, #10B981, #059669); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:1.6rem; font-weight:bold; box-shadow:0 4px 6px -1px rgba(16, 185, 129, 0.4);">
                        ${rep.nombres.charAt(0)}${rep.apellidos.charAt(0)}
                    </div>
                    <div>
                        <h2 style="margin:0; font-size:1.5rem; color:#1E293B;">${rep.nombres} ${rep.apellidos}</h2>
                        <span style="display:inline-block; margin-top:0.35rem; padding: 0.2rem 0.6rem; background:#F1F5F9; color:#475569; border-radius:1rem; font-size:0.75rem; font-weight:bold;">
                            <i class="fas fa-id-card"></i> C.I. ${rep.cedula}
                        </span>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <div>
                        <h3 style="margin:0 0 1rem 0; font-size:1rem; color:#334155; border-left: 4px solid #3B82F6; padding-left:0.5rem;">Datos de Contacto</h3>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; background:#F8FAFC; padding:1.25rem; border-radius:0.5rem; border:1px solid #E2E8F0;">
                            <div>
                                <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase; font-weight:bold; margin-bottom:0.2rem;"><i class="fas fa-phone"></i> Teléfono</div>
                                <div style="font-size:0.95rem; color:#0F172A; font-weight:500;">${rep.telefono || 'No registrado'}</div>
                            </div>
                            <div>
                                <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase; font-weight:bold; margin-bottom:0.2rem;"><i class="fas fa-envelope"></i> Correo Electrónico</div>
                                <div style="font-size:0.95rem; color:#0F172A; font-weight:500; word-break: break-all;">${rep.correo || 'No registrado'}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 style="margin:0 0 1rem 0; font-size:1rem; color:#334155; border-left: 4px solid #8B5CF6; padding-left:0.5rem;">Perfil Adicional</h3>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; background:#F8FAFC; padding:1.25rem; border-radius:0.5rem; border:1px solid #E2E8F0;">
                            <div>
                                <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase; font-weight:bold; margin-bottom:0.2rem;"><i class="fas fa-users"></i> Parentesco</div>
                                <div style="font-size:0.95rem; color:#0F172A; font-weight:500;">${rep.parentesco || 'No especificado'}</div>
                            </div>
                            <div>
                                <div style="font-size:0.75rem; color:#64748B; text-transform:uppercase; font-weight:bold; margin-bottom:0.2rem;"><i class="fas fa-briefcase"></i> Profesión u Oficio</div>
                                <div style="font-size:0.95rem; color:#0F172A; font-weight:500;">${rep.profesion || 'No especificado'}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 style="margin:0 0 1rem 0; font-size:1rem; color:#334155; border-left: 4px solid #10B981; padding-left:0.5rem;">Estudiantes Asociados (${estudiantes.length})</h3>
                        ${estHTML}
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `<div style="text-align:center; color:#EF4444; padding:2rem;"><i class="fas fa-exclamation-triangle fa-2x"></i><br>Error: ${data.message}</div>`;
        }
    } catch (err) {
        content.innerHTML = `<div style="text-align:center; color:#EF4444; padding:2rem;"><i class="fas fa-exclamation-triangle fa-2x"></i><br>Error de conexión al cargar la ficha.</div>`;
    }
}