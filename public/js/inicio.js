document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('api/dashboard_stats.php');
        const resData = await res.json();
        
        if (resData.status === 'success') {
            const data = resData.data;
            
            // Actualizar KPIs superiores
            document.getElementById('stat-estudiantes').textContent = data.estudiantes;
            document.getElementById('stat-docentes').textContent = data.docentes;
            document.getElementById('stat-periodo').textContent = data.periodo;

            // Renderizar Gráfico Circular (Niveles)
            renderGraficoNiveles(data.distribucion_niveles);

            // Renderizar Actividad Reciente
            renderActividadReciente(data.actividad_reciente);
        }
    } catch (e) {
        console.error("Error cargando estadísticas de inicio:", e);
    }
});

function renderGraficoNiveles(niveles) {
    const canvas = document.getElementById('graficoNiveles');
    const emptyMsg = document.getElementById('graficoNivelesEmpty');

    if (!niveles || niveles.length === 0) {
        canvas.style.display = 'none';
        emptyMsg.style.display = 'block';
        return;
    }

    const labels = niveles.map(n => n.nivel);
    const dataVals = niveles.map(n => n.total);
    
    // Paleta de colores vibrantes
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: dataVals,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 13
                        }
                    }
                }
            },
            cutout: '65%' // Hace el hueco más grande, más moderno
        }
    });
}

function renderActividadReciente(actividades) {
    const contenedor = document.getElementById('actividadLista');
    
    if (!actividades || actividades.length === 0) {
        contenedor.innerHTML = '<div style="text-align:center; color:var(--text-muted); font-size:0.9rem; padding:2rem 0;">No hay actividad reciente en el sistema.</div>';
        return;
    }

    let html = '';
    actividades.forEach((act, index) => {
        // Determinar un color e ícono según la acción (simplificado)
        let color = '#3b82f6';
        let icon = 'fa-info-circle';
        
        const accionStr = act.accion.toLowerCase();
        if (accionStr.includes('inscripci') || accionStr.includes('crear')) {
            color = '#10b981'; // Verde
            icon = 'fa-plus-circle';
        } else if (accionStr.includes('eliminar') || accionStr.includes('borrar')) {
            color = '#ef4444'; // Rojo
            icon = 'fa-trash-alt';
        } else if (accionStr.includes('login') || accionStr.includes('sesi')) {
            color = '#8b5cf6'; // Morado
            icon = 'fa-sign-in-alt';
        }

        // Formatear fecha a algo más legible
        const dateObj = new Date(act.fecha_accion);
        const fechaFormat = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        html += `
        <div style="display:flex; gap:1rem; align-items:flex-start; padding-bottom:1rem; ${index !== actividades.length - 1 ? 'border-bottom:1px solid var(--border);' : ''}">
            <div style="background-color: ${color}20; color: ${color}; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas ${icon}"></i>
            </div>
            <div>
                <h5 style="margin-bottom:0.25rem; font-size:0.95rem; font-weight:600; color:var(--text-dark);">${act.accion}</h5>
                <p style="font-size:0.85rem; color:var(--text-main); margin-bottom:0.35rem;">${act.detalle || ''}</p>
                <div style="font-size:0.75rem; color:var(--text-muted); display:flex; gap:1rem;">
                    <span><i class="fas fa-clock"></i> ${fechaFormat}</span>
                    <span><i class="fas fa-user"></i> ${act.usuario || 'Sistema'}</span>
                </div>
            </div>
        </div>`;
    });

    contenedor.innerHTML = html;
}
