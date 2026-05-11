<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Luxury - Crear Cuenta</title>
    <link rel="stylesheet" href="views/css/style.css">
</head>
<body>

<div class="container">

    <h2>Crear Cuenta</h2>

    <form id="formRegister">
        <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="nombre" placeholder="Nombre completo" required>
        </div>
        <div class="form-group">
            <label>Cédula</label>
            <input type="text" name="cedula" placeholder="Número de cédula" required>
        </div>
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" id="password" placeholder="Contraseña" required>
            <small class="hint">Mínimo 6 caracteres, mayúscula, minúscula, número y carácter especial.</small>
        </div>

        <button type="submit" class="btn-primary">Registrarse</button>
    </form>

    <p class="form-link">
        ¿Ya tienes cuenta? <a href="index.php">Iniciar sesión</a>
    </p>

</div>

<div id="toast"></div>

<script>
document.getElementById('formRegister').addEventListener('submit', e => {
    e.preventDefault();

    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled    = true;
    btn.textContent = 'Registrando...';

    fetch('index.php?action=register', {
        method: 'POST',
        body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.msg, data.status);
        if (data.status === 'ok') {
            setTimeout(() => location.href = 'index.php', 1500);
        }
    })
    .catch(() => showToast('Error del servidor', 'error'))
    .finally(() => {
        btn.disabled    = false;
        btn.textContent = 'Registrarse';
    });
});

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className   = 'show ' + type;
    setTimeout(() => { t.className = ''; }, 3500);
}
</script>

</body>
</html>