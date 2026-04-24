<link rel="stylesheet" href="/hotel/mvc/views/css/style.css">

<?php
require_once "controllers/ReservaController.php";
$reservas = (new ReservaController())->listar();
?>

<h2>Bienvenido <?= $_SESSION['user']['nombre'] ?></h2>

<h3><?= isset($reserva) ? "Editar Reserva" : "Nueva Reserva" ?></h3>

<form method="POST" action="index.php?action=<?= isset($reserva) ? 'actualizarReserva' : 'guardarReserva' ?>">

<?php if(isset($reserva)): ?>
<input type="hidden" name="id" value="<?= $reserva['id'] ?>">
<?php endif; ?>

<input name="habitacion" placeholder="Habitación"
value="<?= $reserva['habitacion'] ?? '' ?>" required>

<input type="date" name="fecha_inicio"
value="<?= $reserva['fecha_inicio'] ?? '' ?>"
min="<?= date('Y-m-d') ?>" required>

<input type="date" name="fecha_fin"
value="<?= $reserva['fecha_fin'] ?? '' ?>"
min="<?= date('Y-m-d') ?>" required>

<button>
<?= isset($reserva) ? "Actualizar" : "Reservar" ?>
</button>

</form>

<h3>Mis Reservas</h3>

<table border="1" width="100%">
<tr>
<th>Habitación</th>
<th>Inicio</th>
<th>Fin</th>
<th>Acciones</th>
</tr>

<?php foreach($reservas as $r): ?>
<tr>
<td><?= $r['habitacion'] ?></td>
<td><?= $r['fecha_inicio'] ?></td>
<td><?= $r['fecha_fin'] ?></td>
<td>
<a href="index.php?action=editarReserva&id=<?= $r['id'] ?>">✏️</a>
<a href="index.php?action=eliminarReserva&id=<?= $r['id'] ?>"
onclick="return confirm('¿Eliminar reserva?')">🗑️</a>
</td>
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="index.php?action=logout">Cerrar sesión</a>

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