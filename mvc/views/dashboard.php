<?php
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Luxury - Dashboard</title>
    <link rel="stylesheet" href="views/css/style.css">
    <style>
        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }

        .modal {
            background: #fff;
            border-radius: 12px;
            padding: 28px 32px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            position: relative;
        }
        .modal h3 {
            margin: 0 0 18px;
            font-size: 1.15rem;
            color: #1e3a5f;
        }
        .modal-close {
            position: absolute;
            top: 14px; right: 18px;
            background: none; border: none;
            font-size: 1.4rem; cursor: pointer;
            color: #888;
            line-height: 1;
        }
        .modal-close:hover { color: #333; }

        /* Grilla de habitaciones dentro del modal */
        #editHabitaciones { margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">

    <!-- Encabezado -->
    <div class="header-bar">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombre']) ?></h2>
        <a href="index.php?action=logout" class="btn-logout">Cerrar sesión</a>
    </div>

    <!-- ── FORMULARIO DE RESERVA ── -->
    <div class="card">
        <h3>Nueva Reserva</h3>

        <form id="formReserva">

            <div class="form-group">
                <label>Clase de habitación</label>
                <select id="clase" name="clase" required>
                    <option value="">-- Selecciona clase --</option>
                    <option value="estandar">Estándar</option>
                    <option value="suite">Suite</option>
                    <option value="deluxe">Deluxe</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio"
                       min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Fecha de fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>

            <button type="button" id="btnBuscar" class="btn-secondary">
                Consultar disponibilidad
            </button>

            <!-- Grid de habitaciones -->
            <div id="habitaciones" class="habitaciones-grid"></div>

            <input type="hidden" name="habitacion" id="habitacion">

            <button type="submit" id="btnReservar" class="btn-primary" disabled>
                Confirmar Reserva
            </button>

        </form>
    </div>

    <!-- ── MIS RESERVAS ── -->
    <div class="card">
        <div class="card-header-row">
            <h3>Mis Reservas</h3>
            <a href="index.php?action=excelReservas" class="btn-excel">
                ⬇ Descargar Excel
            </a>
        </div>

        <div id="tablaReservas">
            <p class="loading-text">Cargando reservas...</p>
        </div>
    </div>

</div>

<!-- ── MODAL EDITAR RESERVA ── -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <button class="modal-close" id="btnCerrarModal" title="Cerrar">&times;</button>
        <h3>✏️ Editar Reserva <span id="editReservaIdLabel"></span></h3>

        <form id="formEditar">
            <input type="hidden" id="editId" name="id">

            <div class="form-group">
                <label>Clase de habitación</label>
                <select id="editClase" name="clase" required>
                    <option value="">-- Selecciona clase --</option>
                    <option value="estandar">Estándar</option>
                    <option value="suite">Suite</option>
                    <option value="deluxe">Deluxe</option>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de inicio</label>
                <input type="date" name="fecha_inicio" id="editFechaInicio"
                       min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Fecha de fin</label>
                <input type="date" name="fecha_fin" id="editFechaFin"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>

            <button type="button" id="btnEditBuscar" class="btn-secondary">
                Consultar disponibilidad
            </button>

            <!-- Grid de habitaciones en modal -->
            <div id="editHabitaciones" class="habitaciones-grid"></div>

            <input type="hidden" name="habitacion" id="editHabitacion">

            <button type="submit" id="btnGuardarEdicion" class="btn-primary" disabled>
                Guardar cambios
            </button>
        </form>
    </div>
</div>

<!-- Toast -->
<div id="toast"></div>

<script>
// ════════════════════════════════════════════════════════════
//  UTILIDADES
// ════════════════════════════════════════════════════════════

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show ' + type;
    setTimeout(() => { t.className = ''; }, 3500);
}

function calcularNoches(ini, fin) {
    const d1 = new Date(ini + 'T00:00:00');
    const d2 = new Date(fin + 'T00:00:00');
    return Math.round((d2 - d1) / 86400000);
}

function formatFecha(f) {
    const [y, m, d] = f.split('-');
    return `${d}/${m}/${y}`;
}

// ════════════════════════════════════════════════════════════
//  FORMULARIO NUEVA RESERVA
// ════════════════════════════════════════════════════════════

const inputInicio = document.getElementById('fecha_inicio');
const inputFin    = document.getElementById('fecha_fin');

inputInicio.addEventListener('change', () => {
    if (inputInicio.value) {
        const minFin = new Date(inputInicio.value);
        minFin.setDate(minFin.getDate() + 1);
        inputFin.min = minFin.toISOString().split('T')[0];
        if (inputFin.value && inputFin.value <= inputInicio.value) {
            inputFin.value = '';
        }
    }
});

document.getElementById('btnBuscar').addEventListener('click', () => {
    const clase  = document.getElementById('clase').value;
    const inicio = inputInicio.value;
    const fin    = inputFin.value;

    if (!clase)  return showToast('Selecciona una clase de habitación', 'error');
    if (!inicio) return showToast('Selecciona la fecha de inicio', 'error');
    if (!fin)    return showToast('Selecciona la fecha de fin', 'error');
    if (inicio >= fin) return showToast('La fecha de fin debe ser posterior a la de inicio', 'error');

    const grid = document.getElementById('habitaciones');
    grid.innerHTML = '<p class="loading-text">Buscando habitaciones...</p>';
    document.getElementById('habitacion').value = '';
    document.getElementById('btnReservar').disabled = true;

    fetch(`index.php?action=habitaciones&clase=${clase}&inicio=${inicio}&fin=${fin}`)
        .then(r => r.json())
        .then(data => renderGrid(data, grid, 'habitacion', 'btnReservar'))
        .catch(() => {
            grid.innerHTML = '';
            showToast('Error al consultar habitaciones', 'error');
        });
});

document.getElementById('formReserva').addEventListener('submit', e => {
    e.preventDefault();

    const habitacion = document.getElementById('habitacion').value;
    if (!habitacion) return showToast('Selecciona una habitación', 'error');

    const btn = document.getElementById('btnReservar');
    btn.disabled    = true;
    btn.textContent = 'Guardando...';

    fetch('index.php?action=guardarReserva', {
        method: 'POST',
        body: new FormData(e.target)
    })
    .then(r => r.json())
    .then(res => {
        showToast(res.msg, res.status);
        if (res.status === 'ok') {
            e.target.reset();
            document.getElementById('habitaciones').innerHTML = '';
            document.getElementById('habitacion').value = '';
            cargarReservas();
        }
    })
    .catch(() => showToast('Error del servidor', 'error'))
    .finally(() => {
        btn.disabled    = false;
        btn.textContent = 'Confirmar Reserva';
    });
});

// ════════════════════════════════════════════════════════════
//  TABLA DE RESERVAS
// ════════════════════════════════════════════════════════════

function cargarReservas() {
    const contenedor = document.getElementById('tablaReservas');
    contenedor.innerHTML = '<p class="loading-text">Cargando...</p>';

    fetch('index.php?action=listarReservas')
        .then(r => r.json())
        .then(res => {
            const data = res.data;

            if (!data || !data.length) {
                contenedor.innerHTML = '<p class="empty-text">No tienes reservas aún.</p>';
                return;
            }

            let html = `
                <table class="tabla-reservas">
                    <thead>
                        <tr>
                            <th># Reserva</th>
                            <th>Habitación</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Noches</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.forEach(r => {
                const noches = calcularNoches(r.fecha_inicio, r.fecha_fin);
                const idFmt  = String(r.id).padStart(5, '0');
                html += `
                    <tr>
                        <td>#${idFmt}</td>
                        <td>${r.habitacion}</td>
                        <td>${formatFecha(r.fecha_inicio)}</td>
                        <td>${formatFecha(r.fecha_fin)}</td>
                        <td>${noches} noche(s)</td>
                        <td class="td-acciones">
                            <a href="index.php?action=pdfReserva&id=${r.id}"
                               target="_blank" class="btn-pdf" title="Ver PDF">
                               📄 PDF
                            </a>
                            <button class="btn-edit"
                                    onclick="abrirModalEditar(${r.id}, '${r.habitacion}', '${r.fecha_inicio}', '${r.fecha_fin}')"
                                    title="Editar reserva">
                                ✏️ Editar
                            </button>
                            <button class="btn-cancelar"
                                    onclick="cancelarReserva(${r.id}, this)"
                                    title="Cancelar reserva">
                                🗑 Cancelar
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            contenedor.innerHTML = html;
        })
        .catch(() => {
            contenedor.innerHTML = '<p class="empty-text">Error al cargar reservas.</p>';
        });
}

// ════════════════════════════════════════════════════════════
//  MODAL EDITAR
// ════════════════════════════════════════════════════════════

function abrirModalEditar(id, habitacion, fechaInicio, fechaFin) {
    // Rellenar campos con los datos actuales
    document.getElementById('editId').value             = id;
    document.getElementById('editReservaIdLabel').textContent = '#' + String(id).padStart(5,'0');
    document.getElementById('editFechaInicio').value    = fechaInicio;
    document.getElementById('editFechaFin').value       = fechaFin;

    // Detectar clase a partir del número de habitación si sigue el patrón habitual,
    // o simplemente dejar vacío para que el usuario la seleccione y consulte.
    document.getElementById('editClase').value          = '';
    document.getElementById('editHabitaciones').innerHTML = '';
    document.getElementById('editHabitacion').value     = habitacion; // preseleccionar la actual
    document.getElementById('btnGuardarEdicion').disabled = false;    // ya hay habitación preseleccionada

    // Mostrar la habitación actual como referencia
    const grid = document.getElementById('editHabitaciones');
    grid.innerHTML = `<p class="loading-text" style="margin:6px 0">Habitación actual: <strong>${habitacion}</strong>. Selecciona clase y consulta para cambiarla.</p>`;

    document.getElementById('modalEditar').classList.add('active');
}

// Cerrar modal
document.getElementById('btnCerrarModal').addEventListener('click', cerrarModal);
document.getElementById('modalEditar').addEventListener('click', e => {
    if (e.target === document.getElementById('modalEditar')) cerrarModal();
});
function cerrarModal() {
    document.getElementById('modalEditar').classList.remove('active');
}

// Validación fecha fin en modal
document.getElementById('editFechaInicio').addEventListener('change', () => {
    const ini = document.getElementById('editFechaInicio').value;
    if (ini) {
        const minFin = new Date(ini);
        minFin.setDate(minFin.getDate() + 1);
        document.getElementById('editFechaFin').min = minFin.toISOString().split('T')[0];
        const finVal = document.getElementById('editFechaFin').value;
        if (finVal && finVal <= ini) {
            document.getElementById('editFechaFin').value = '';
        }
    }
});

// Consultar disponibilidad en modal (pasa excluir=id para que la habitación actual aparezca libre)
document.getElementById('btnEditBuscar').addEventListener('click', () => {
    const clase  = document.getElementById('editClase').value;
    const inicio = document.getElementById('editFechaInicio').value;
    const fin    = document.getElementById('editFechaFin').value;
    const id     = document.getElementById('editId').value;

    if (!clase)  return showToast('Selecciona una clase de habitación', 'error');
    if (!inicio) return showToast('Selecciona la fecha de inicio', 'error');
    if (!fin)    return showToast('Selecciona la fecha de fin', 'error');
    if (inicio >= fin) return showToast('La fecha de fin debe ser posterior a la de inicio', 'error');

    const grid = document.getElementById('editHabitaciones');
    grid.innerHTML = '<p class="loading-text">Buscando habitaciones...</p>';
    document.getElementById('editHabitacion').value = '';
    document.getElementById('btnGuardarEdicion').disabled = true;

    fetch(`index.php?action=habitaciones&clase=${clase}&inicio=${inicio}&fin=${fin}&excluir=${id}`)
        .then(r => r.json())
        .then(data => renderGrid(data, grid, 'editHabitacion', 'btnGuardarEdicion'))
        .catch(() => {
            grid.innerHTML = '';
            showToast('Error al consultar habitaciones', 'error');
        });
});

// Guardar edición
document.getElementById('formEditar').addEventListener('submit', e => {
    e.preventDefault();

    const habitacion = document.getElementById('editHabitacion').value;
    if (!habitacion) return showToast('Selecciona una habitación', 'error');

    const btn = document.getElementById('btnGuardarEdicion');
    btn.disabled    = true;
    btn.textContent = 'Guardando...';

    fetch('index.php?action=editarReserva', {
        method: 'POST',
        body: new FormData(e.target)
    })
    .then(r => r.json())
    .then(res => {
        showToast(res.msg, res.status);
        if (res.status === 'ok') {
            cerrarModal();
            cargarReservas();
        }
    })
    .catch(() => showToast('Error del servidor', 'error'))
    .finally(() => {
        btn.disabled    = false;
        btn.textContent = 'Guardar cambios';
    });
});

// ════════════════════════════════════════════════════════════
//  CANCELAR RESERVA
// ════════════════════════════════════════════════════════════

function cancelarReserva(id, btn) {
    if (!confirm('¿Seguro que deseas cancelar esta reserva?')) return;

    btn.disabled     = true;
    btn.textContent  = 'Cancelando...';

    const fd = new FormData();
    fd.append('id', id);

    fetch('index.php?action=cancelarReserva', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            showToast(res.msg, res.status);
            if (res.status === 'ok') cargarReservas();
        })
        .catch(() => showToast('Error del servidor', 'error'))
        .finally(() => {
            btn.disabled    = false;
            btn.textContent = '🗑 Cancelar';
        });
}

// ════════════════════════════════════════════════════════════
//  AUXILIAR: renderiza grilla de habitaciones (reutilizable)
// ════════════════════════════════════════════════════════════

function renderGrid(data, grid, hiddenInputId, btnId) {
    if (!data.length) {
        grid.innerHTML = '<p class="empty-text">No hay habitaciones en esta categoría.</p>';
        return;
    }

    grid.innerHTML = '';
    data.forEach(h => {
        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'hab-btn ' + h.estado;
        btn.textContent = '🛏 ' + h.numero;

        if (h.estado === 'disponible') {
            btn.addEventListener('click', () => {
                grid.querySelectorAll('.hab-btn.seleccionada')
                    .forEach(b => b.classList.remove('seleccionada'));
                btn.classList.add('seleccionada');
                document.getElementById(hiddenInputId).value = h.numero;
                document.getElementById(btnId).disabled = false;
            });
        } else {
            btn.disabled = true;
            btn.title    = 'Ocupada en esas fechas';
        }

        grid.appendChild(btn);
    });
}

// ════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════
cargarReservas();
</script>

</body>
</html>