<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="/hotel-mvc-main/mvc/views/css/style.css">
</head>
<body>

<div class="auth-container">
<div class="auth-box">

<h1>JP Urban Hotel</h1>

<form method="POST" action="index.php?action=loginUser">
<input name="email" type="email" placeholder="Correo" required>
<input name="password" type="password" placeholder="Contraseña" required>
<button>Ingresar</button>
</form>

<p><a href="index.php?action=register">Crear cuenta</a></p>

</div>
</div>

<?php if(isset($_SESSION['toast'])): ?>
<div id="toast" class="toast <?= $_SESSION['toast']['type'] ?>">
    <?= $_SESSION['toast']['msg'] ?>
</div>

<script>
const t = document.getElementById("toast");

if(t){
    setTimeout(()=>t.classList.add("show"),100);

    setTimeout(()=>{
        t.classList.remove("show");
        setTimeout(()=>t.remove(),400);
    },3000);
}
</script>

<?php unset($_SESSION['toast']); endif; ?>

</body>
</html>