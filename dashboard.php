<?php
require_once 'conexion.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$view = $_GET['view'] ?? 'inicio';
$allowed_views = ['inicio', 'configuracion', 'estudiantes', 'docentes', 'matricula', 'seguridad'];

if (!in_array($view, $allowed_views)) {
    $view = 'inicio';
}



$view_titles = [
    'inicio' => 'SICOES JMS (Sistema de Control de Estudios "Jesús María Sifontes")',
    'configuracion' => 'Configuración del Sistema',
    'estudiantes' => 'Gestión de Estudiantes',
    'docentes' => 'Gestión de Docentes',
    'matricula' => 'Matrícula y Reportes',
    'seguridad' => 'Seguridad y Usuarios'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $view_titles[$view] ?> - SICOES JMS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="public/img/favicon.webp">
    <link rel="stylesheet" href="public/css/index.css?v=5">
    <link rel="stylesheet" href="public/css/dashboard.css?v=6">
    <!-- FontAwesome para Íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="public/js/csrf_interceptor.js?v=1"></script>
    <script>
        const USER_ROL_ID = <?= $_SESSION['rol_id'] ?? 0 ?>;
    </script>
</head>
<body>
<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
</script>

<div class="dashboard-container">
    <!-- Barra Lateral -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="public/img/logo sifontes webp2.webp" alt="SICOES JMS Logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="?view=inicio" class="<?= $view == 'inicio' ? 'active' : '' ?>"><i class="fas fa-home"></i> Inicio</a></li>
              <?php if($_SESSION['rol_id'] == 1): ?>
              <li><a href="?view=configuracion" class="<?= $view == 'configuracion' ? 'active' : '' ?>"><i class="fas fa-cogs"></i> Configuración de Año Escolar</a></li>
              <?php endif; ?>
            <li><a href="?view=estudiantes" class="<?= $view == 'estudiantes' ? 'active' : '' ?>"><i class="fas fa-user-graduate"></i> Estudiantes</a></li>
            <li><a href="?view=docentes" class="<?= $view == 'docentes' ? 'active' : '' ?>"><i class="fas fa-chalkboard-teacher"></i> Docentes</a></li>
            <li><a href="?view=matricula" class="<?= $view == 'matricula' ? 'active' : '' ?>"><i class="fas fa-list-alt"></i> Matrícula</a></li>
              <?php if($_SESSION['rol_id'] == 1): ?>
              <li><a href="?view=seguridad" class="<?= $view == 'seguridad' ? 'active' : '' ?>"><i class="fas fa-shield-alt"></i> Seguridad y Usuarios</a></li>
              <?php endif; ?>
        </ul>
    </aside>

    <!-- Contenido Principal -->
    <div class="main-wrapper">
        <header class="topbar">
            <div>
                <h3><?= $view_titles[$view] ?></h3>
            </div>
            <div class="user-info" style="display:flex; align-items:center;">
                <button id="themeToggleBtn" class="btn" style="display:flex; align-items:center; gap:0.5rem; background:var(--surface); color:var(--text-main); border:1px solid var(--border); padding:0.5rem 1rem; margin-right: 1rem; border-radius:20px; font-weight:bold;" onclick="toggleTheme()" title="Cambiar tema">
                    <i class="fas fa-moon" id="themeIcon"></i> <span id="themeText">Modo Oscuro</span>
                </button>
                <div style="display:flex; align-items:center; gap:0.75rem; margin-right: 1rem;">
                    <div style="width:38px; height:38px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.1rem; box-shadow:var(--shadow-sm);">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="user-details" style="margin-right: 0;">
                        <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>
                        <span><?= htmlspecialchars($_SESSION['rol_nombre']); ?></span>
                    </div>
                </div>
                <button onclick="logout()" class="btn" style="background:var(--danger); color:white; padding:0.5rem 1rem;">Cerrar Sesión</button>
            </div>
        </header>

        <main id="dynamic-content">
            <?php 
                $file = "views/{$view}.php";
                if(file_exists($file)){
                    include $file;
                } else {
                    echo "<div class='card'><p>Vista en construcción...</p></div>";
                }
            ?>
        </main>
    </div>
</div>

<script>
async function logout() {
    const formData = new FormData();
    formData.append('action', 'logout');
    await fetch('api/usuarios.php', { method: 'POST', body: formData });
    window.location.href = 'index.php';
}

function toggleTheme() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');
    if (icon && text) {
        if (isDark) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            text.textContent = 'Modo Claro';
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            text.textContent = 'Modo Oscuro';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.body.classList.contains('dark-mode')) {
        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');
        if (icon && text) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            text.textContent = 'Modo Claro';
        }
    }
});
</script>
</body>
</html>
