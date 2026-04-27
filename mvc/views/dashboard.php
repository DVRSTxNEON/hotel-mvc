<link rel="stylesheet" href="/hotel-mvc-main/mvc/views/css/style.css?v=2">

<?php
require_once "controllers/ReservaController.php";
$reservas = (new ReservaController())->listar() ?? [];
?>

<div class="container">

    <header class="topbar">
        <h2>JP Urban Hotel</h2>
        <span>Bienvenido, <?= $_SESSION['user']['nombre'] ?></span>
    </header>

    <div class="grid">

        <!-- FORM -->
        <div class="card">

            <h3>Nueva Reserva</h3>

            <form method="POST" action="index.php?action=<?= isset($reserva) ? 'actualizarReserva' : 'guardarReserva' ?>">

                <?php if(isset($reserva)): ?>
                <input type="hidden" name="id" value="<?= $reserva['id'] ?>">
                <?php endif; ?>

                <label>Clase</label>
                <select id="clase" required>
                    <option value="">Selecciona</option>
                    <option value="estandar">Estándar</option>
                    <option value="suite">Suite</option>
                    <option value="deluxe">Deluxe</option>
                </select>

                <label>Habitación</label>
                <select name="habitacion" id="habitacion" required>
                    <option value="">Selecciona</option>
                </select>

                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" required>

                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" required>

                <button>Reservar</button>

            </form>

        </div>

        <!-- TABLA -->
        <div class="card">

            <h3>Mis Reservas</h3>
<table>
    <tr>
        <th>Habitación</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th></th>
    </tr>

    <?php if(!empty($reservas)): ?>
        <?php foreach($reservas as $r): ?>
        <tr>
            <td><?= $r['habitacion'] ?></td>
            <td><?= $r['fecha_inicio'] ?></td>
            <td><?= $r['fecha_fin'] ?></td>
            <td>
                <a href="index.php?action=editarReserva&id=<?= $r['id'] ?>">✏️</a>
                <a href="index.php?action=eliminarReserva&id=<?= $r['id'] ?>">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">No tienes reservas aún</td>
        </tr>
    <?php endif; ?>
</table>

        </div>

    </div>

    <a class="logout" href="index.php?action=logout">Cerrar sesión</a>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const claseSelect = document.getElementById("clase");
    const habitacionSelect = document.getElementById("habitacion");

    claseSelect.addEventListener("change", function(){

        let clase = this.value;

        fetch("index.php?action=habitaciones&clase=" + clase)
.then(res => {
    if(!res.ok) throw new Error("Error servidor");
    return res.json();
})
.then(data => {

    habitacionSelect.innerHTML = '<option value="">Selecciona</option>';

    if(data.length === 0){
        habitacionSelect.innerHTML += '<option>No hay disponibles</option>';
        return;
    }

    data.forEach(r => {
        habitacionSelect.innerHTML += `<option value="${r.numero}">${r.numero}</option>`;
    });

})
.catch(err => {
    console.error(err);
    habitacionSelect.innerHTML = '<option>Error cargando habitaciones</option>';
});
        .then(data => {

            habitacionSelect.innerHTML = '<option value="">Selecciona</option>';

            data.forEach(r => {
                habitacionSelect.innerHTML += `<option value="${r.numero}">${r.numero}</option>`;
            });

        });

    });

});
</script>