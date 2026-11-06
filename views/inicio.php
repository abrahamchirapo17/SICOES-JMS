<div style="display:flex; flex-direction:column; gap:2rem;">
    
    <!-- HEADER Y ACCESOS RÁPIDOS -->
    <div class="card" style="padding: 2rem; background: linear-gradient(135deg, var(--primary) 0%, #1e1b4b 100%); color: white; position: relative; overflow: hidden; border: none; box-shadow: 0 15px 30px -10px rgba(79, 70, 229, 0.4);">
        <!-- Decorative background circles -->
        <div style="position: absolute; top: -50%; right: -5%; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; blur(10px);"></div>
        <div style="position: absolute; bottom: -50%; right: 10%; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; position: relative; z-index: 2;">
            <div>
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 0.5rem; color: white;">¡Hola, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>! 👋</h2>
                <p style="color: rgba(255,255,255,0.8); font-size: 1rem; margin-bottom: 0;">Bienvenido al Panel de Control Integral. Aquí tienes un resumen del estado actual de la institución.</p>
            </div>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <button class="btn" onclick="window.location.href='?view=estudiantes'" style="background: white; color: var(--primary); font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"><i class="fas fa-user-plus"></i> Inscribir Estudiante</button>
                <button class="btn" onclick="window.location.href='?view=docentes'" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);"><i class="fas fa-chalkboard-teacher"></i> Nuevo Docente</button>
            </div>
        </div>
    </div>

    <!-- TARJETAS DE KPIs SUPERIORES -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        
        <!-- KPI Estudiantes -->
        <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color:white; padding:2rem; border-radius:16px; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4); position:relative; overflow:hidden;">
            <div style="position:relative; z-index:2;">
                <h4 style="font-weight:600; opacity:0.85; font-size:1rem; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:1px;">Estudiantes Activos</h4>
                <div id="stat-estudiantes" style="font-size:3.5rem; font-weight:900; line-height:1;">--</div>
            </div>
            <i class="fas fa-user-graduate" style="position:absolute; right:-20px; bottom:-20px; font-size:8rem; opacity:0.1; z-index:1; transform: rotate(-10deg);"></i>
        </div>
        
        <!-- KPI Docentes -->
        <div style="background: linear-gradient(135deg, #059669 0%, #064e3b 100%); color:white; padding:2rem; border-radius:16px; box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.4); position:relative; overflow:hidden;">
            <div style="position:relative; z-index:2;">
                <h4 style="font-weight:600; opacity:0.85; font-size:1rem; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:1px;">Docentes Registrados</h4>
                <div id="stat-docentes" style="font-size:3.5rem; font-weight:900; line-height:1;">--</div>
            </div>
            <i class="fas fa-chalkboard-teacher" style="position:absolute; right:-20px; bottom:-20px; font-size:8rem; opacity:0.1; z-index:1; transform: rotate(-10deg);"></i>
        </div>
        
        <!-- KPI Periodo -->
        <div style="background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%); color:white; padding:2rem; border-radius:16px; box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.4); position:relative; overflow:hidden;">
            <div style="position:relative; z-index:2;">
                <h4 style="font-weight:600; opacity:0.85; font-size:1rem; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:1px;">Año Escolar Actual</h4>
                <div id="stat-periodo" style="font-size:1.8rem; font-weight:800; line-height:1.2; margin-top:0.5rem;">Cargando...</div>
            </div>
            <i class="fas fa-calendar-alt" style="position:absolute; right:-20px; bottom:-20px; font-size:8rem; opacity:0.1; z-index:1; transform: rotate(-10deg);"></i>
        </div>
        
    </div>

    <!-- GRID INFERIOR: GRÁFICOS Y ACTIVIDAD -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- GRÁFICO CIRCULAR -->
        <div class="card" style="min-height:350px;">
            <h3 class="card-title" style="font-size:1.1rem; border-bottom:1px solid var(--border); padding-bottom:0.75rem;"><i class="fas fa-chart-pie" style="color:var(--primary);"></i> Distribución de Matrícula</h3>
            <div style="position:relative; height:280px; width:100%; display:flex; justify-content:center; align-items:center; margin-top:1rem;">
                <canvas id="graficoNiveles"></canvas>
                <div id="graficoNivelesEmpty" style="display:none; color:var(--text-muted); font-size:0.9rem;">No hay datos de matrícula aún.</div>
            </div>
        </div>

        <!-- ACTIVIDAD RECIENTE -->
        <div class="card" style="min-height:350px;">
            <h3 class="card-title" style="font-size:1.1rem; border-bottom:1px solid var(--border); padding-bottom:0.75rem;"><i class="fas fa-history" style="color:var(--primary);"></i> Actividad Reciente del Sistema</h3>
            <div id="actividadLista" style="margin-top:1rem; display:flex; flex-direction:column; gap:1rem; max-height:280px; overflow-y:auto; padding-right:0.5rem;">
                <div style="text-align:center; color:var(--text-muted); font-size:0.9rem; padding:2rem 0;">Cargando historial...</div>
            </div>
        </div>

    </div>

</div>

<!-- Cargar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Script específico de inicio -->
<script src="public/js/inicio.js?v=3"></script>
