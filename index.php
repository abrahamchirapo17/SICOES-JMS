<?php
require_once 'conexion.php';
// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SICOES JMS</title>
    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="public/img/favicon.webp">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/index.css?v=10">
    <script src="public/js/csrf_interceptor.js?v=1"></script>
</head>
<body>
<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    }
</script>

<div class="login-wrapper">
    <!-- Left Side: Image with dark overlay -->
    <div class="login-split-image">
        <div class="login-image-overlay">
            <div class="login-benefits-container">
                <h2 class="benefits-title">Bienvenidos a SICOES JMS</h2>
                <p class="benefits-subtitle">(Sistema de Control de Estudios "Jesús María Sifontes")</p>
                
                <p class="benefits-intro">En este sistema podrás:</p>
                
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Configurar el período escolar</p>
                    </div>
                    <div class="benefit-card">
                        <i class="fas fa-users"></i>
                        <p>Registrar a los estudiantes y docentes de la institución</p>
                    </div>
                    <div class="benefit-card">
                        <i class="fas fa-sitemap"></i>
                        <p>Gestionar la estructura académica</p>
                    </div>
                    <div class="benefit-card">
                        <i class="fas fa-file-alt"></i>
                        <p>Obtener matrículas completas de estudiantes y docentes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form with glassmorphism -->
    <div class="login-split-form">
        <div class="login-card">
            <div class="login-header">
                <img src="public/img/logo sifontes webp2.webp" alt="Logo SICOES JMS" class="login-logo">
                <h1 class="login-title">SICOES JMS</h1>
                <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
            </div>

            <div id="loginAlert" class="alert"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">Usuario</label>
                    <div style="position:relative;">
                        <i class="fas fa-user input-icon" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Ej. lucero" required autofocus autocomplete="username" maxlength="12" style="padding-left: 2.75rem;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div style="position:relative;">
                        <i class="fas fa-lock input-icon" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
                        <input type="password" id="password" name="password" class="form-input" placeholder="•••••••••" required autocomplete="current-password" maxlength="15" style="padding-left: 2.75rem; padding-right: 2.75rem;">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()" style="position:absolute; right:1rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary btn--full" id="loginBtn" style="margin-top: 1.5rem; padding: 1rem; font-size: 1.1rem; border-radius: 12px;">
                    <span class="loader" id="loginLoader"></span>
                    <span class="btn-text" id="loginBtnText">Ingresar al Sistema <i class="fas fa-arrow-right" style="margin-left:0.5rem;"></i></span>
                </button>
                <div style="text-align:center; margin-top:1rem;">
                    <a href="#" onclick="mostrarRecuperacion(event)" style="color:var(--primary); font-size:0.9rem; text-decoration:none; font-weight:500;">¿Olvidaste tu contraseña? (Solo Admin)</a>
                </div>
            </form>

            <form id="formRecoveryStep1" style="display:none;">
                <h3 style="margin-bottom: 1rem; color:var(--text-color); font-size:1.1rem;">Paso 1: Verificación de Identidad</h3>
                <div class="form-group">
                    <label class="form-label">Usuario Administrador</label>
                    <input type="text" id="rec_username" name="username" class="form-input" required placeholder="Ej. lucero" maxlength="12">
                </div>
                <div class="form-group">
                    <label class="form-label">Pregunta de Seguridad</label>
                    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:0.5rem; font-style:italic;" id="lbl_pregunta">Responda a la pregunta de seguridad configurada para su cuenta.</p>
                    <input type="text" id="rec_respuesta" name="respuesta" class="form-input" required placeholder="Tu respuesta secreta">
                </div>
                <button type="submit" class="btn btn--primary btn--full" id="btnRecovery1">
                    <span class="loader" id="loaderRecovery1"></span>
                    <span id="textRecovery1">Verificar Respuesta</span>
                </button>
                <div style="text-align:center; margin-top:1rem;">
                    <a href="#" onclick="mostrarLogin(event)" style="color:var(--text-muted); font-size:0.9rem; text-decoration:none; font-weight:500;"><i class="fas fa-arrow-left"></i> Cancelar</a>
                </div>
            </form>

            <form id="formRecoveryStep2" style="display:none;">
                <h3 style="margin-bottom: 1rem; color:var(--text-color); font-size:1.1rem;">Paso 2: Nueva Contraseña</h3>
                <div class="form-group">
                    <label class="form-label">Nueva Contraseña</label>
                    <input type="password" id="rec_password" name="new_password" class="form-input" required placeholder="Al menos 8 caracteres" minlength="8" maxlength="15">
                    <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:0.3rem;">Debe contener: 1 Mayúscula, 1 minúscula, 1 número y 1 carácter especial (* , . + - /)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password" id="rec_password_confirm" name="confirm_password" class="form-input" required placeholder="Repita la nueva contraseña" minlength="8" maxlength="15">
                </div>
                <button type="submit" class="btn btn--primary btn--full" id="btnRecovery2">
                    <span class="loader" id="loaderRecovery2"></span>
                    <span id="textRecovery2">Actualizar Contraseña</span>
                </button>
                <div style="text-align:center; margin-top:1rem;">
                    <a href="#" onclick="mostrarLogin(event)" style="color:var(--text-muted); font-size:0.9rem; text-decoration:none; font-weight:500;"><i class="fas fa-arrow-left"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="public/js/login.js?v=4"></script>
</body>
</html>
