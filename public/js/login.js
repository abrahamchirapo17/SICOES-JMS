document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('loginBtnText');
    const loader = document.getElementById('loginLoader');
    const loginAlert = document.getElementById('loginAlert');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Estado de carga
        loginBtn.disabled = true;
        btnText.textContent = 'Verificando...';
        loader.style.display = 'inline-block';
        loginAlert.style.display = 'none';
        
        const formData = new FormData(loginForm);
        formData.append('action', 'login');

        try {
            const response = await fetch('api/usuarios.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
                showAlert('success', 'Sesión iniciada. Redirigiendo...');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                showAlert('error', data.message);
                resetButton();
            }
        } catch (error) {
            showAlert('error', 'Error de conexión con el servidor. Revise su red.');
            resetButton();
        }
    });

    function showAlert(type, message) {
        if (type === 'success') {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', message, 'error');
        }
    }

    function resetButton() {
        loginBtn.disabled = false;
        btnText.textContent = 'Ingresar al Sistema';
        loader.style.display = 'none';
    }
});

// Toggle Funciones
function mostrarRecuperacion(e) {
    if(e) e.preventDefault();
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('formRecoveryStep1').style.display = 'block';
    document.getElementById('formRecoveryStep2').style.display = 'none';
    document.querySelector('.login-subtitle').textContent = 'Recuperación de Contraseña (Solo Admin)';
}

function mostrarLogin(e) {
    if(e) e.preventDefault();
    document.getElementById('formRecoveryStep1').style.display = 'none';
    document.getElementById('formRecoveryStep2').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('formRecoveryStep1').reset();
    document.getElementById('formRecoveryStep2').reset();
    document.querySelector('.login-subtitle').textContent = 'Ingresa tus credenciales para continuar';
}

// Recovery Submit Step 1
const formRecoveryStep1 = document.getElementById('formRecoveryStep1');
if(formRecoveryStep1) {
    formRecoveryStep1.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnRecovery1');
        const loader = document.getElementById('loaderRecovery1');
        const text = document.getElementById('textRecovery1');
        
        btn.disabled = true;
        loader.style.display = 'inline-block';
        text.textContent = 'Verificando...';

        const formData = new FormData(formRecoveryStep1);
        formData.append('action', 'verify_security');

        try {
            const res = await fetch('api/usuarios.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                document.getElementById('formRecoveryStep1').style.display = 'none';
                document.getElementById('formRecoveryStep2').style.display = 'block';
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch(err) {
            Swal.fire('Error', 'Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            loader.style.display = 'none';
            text.textContent = 'Verificar Respuesta';
        }
    });
}

// Recovery Submit Step 2
const formRecoveryStep2 = document.getElementById('formRecoveryStep2');
if(formRecoveryStep2) {
    formRecoveryStep2.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnRecovery2');
        const loader = document.getElementById('loaderRecovery2');
        const text = document.getElementById('textRecovery2');
        
        const password = document.getElementById('rec_password').value;
        const confirm_password = document.getElementById('rec_password_confirm').value;

        if (password !== confirm_password) {
            Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
            return;
        }

        // Validación local (Regex)
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[*,\.+\-\/]).{8,12}$/;
        if (!regex.test(password)) {
            Swal.fire('Error', 'La contraseña no cumple con los requisitos de seguridad.', 'error');
            return;
        }

        btn.disabled = true;
        loader.style.display = 'inline-block';
        text.textContent = 'Procesando...';

        const formData = new FormData(formRecoveryStep2);
        formData.append('action', 'recovery_reset');

        try {
            const res = await fetch('api/usuarios.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    mostrarLogin();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch(err) {
            Swal.fire('Error', 'Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            loader.style.display = 'none';
            text.textContent = 'Actualizar Contraseña';
        }
    });
}
