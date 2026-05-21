<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Luxury - Iniciar Sesión</title>
    <link rel="stylesheet" href="views/css/style.css">
</head>
<body>

<div class="container">

    <h2>Hotel Luxury</h2>

    <form id="formLogin">
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Contraseña" required>
        </div>

        <button type="submit" class="btn-primary">Ingresar</button>
    </form>

    <p class="form-link">
        ¿No tienes cuenta? <a href="index.php?action=registerView">Crear cuenta</a>
    </p>

</div>

<div id="toast"></div>

<script>
document.getElementById('formLogin').addEventListener('submit', e => {
    e.preventDefault();

    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled    = true;
    btn.textContent = 'Ingresando...';

    fetch('index.php?action=login', {
        method: 'POST',
        body: new FormData(e.target)
    })
    .then(r => r.json())
    .then(res => {
        showToast(res.msg, res.status);
        if (res.status === 'ok') {
            setTimeout(() => location.href = 'index.php?action=dashboard', 1500);
        }
    })
    .catch(() => showToast('Error del servidor', 'error'))
    .finally(() => {
        btn.disabled    = false;
        btn.textContent = 'Ingresar';
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