<?php
require_once __DIR__ . "/../config/db.php";

class Reserva {

    // ── GUARDAR ───────────────────────────────────────────────────────────────
    public function guardar($usuario_id, $habitacion, $fecha_inicio, $fecha_fin) {
        global $pdo;
        $s = $pdo->prepare(
            "INSERT INTO reservas (usuario_id, habitacion, fecha_inicio, fecha_fin)
             VALUES (?, ?, ?, ?)"
        );
        return $s->execute([$usuario_id, $habitacion, $fecha_inicio, $fecha_fin]);
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────────────
    public function actualizar($id, $usuario_id, $habitacion, $fecha_inicio, $fecha_fin) {
        global $pdo;
        $s = $pdo->prepare(
            "UPDATE reservas
             SET habitacion = ?, fecha_inicio = ?, fecha_fin = ?
             WHERE id = ? AND usuario_id = ?"
        );
        return $s->execute([$habitacion, $fecha_inicio, $fecha_fin, $id, $usuario_id]);
    }

    // ── LISTAR por usuario ────────────────────────────────────────────────────
    public function listar($usuario_id) {
        global $pdo;
        $s = $pdo->prepare(
            "SELECT id, habitacion, fecha_inicio, fecha_fin
             FROM reservas
             WHERE usuario_id = ?
             ORDER BY fecha_inicio DESC"
        );
        $s->execute([$usuario_id]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── OBTENER por ID (validando dueño) ──────────────────────────────────────
    public function obtenerPorId($id, $usuario_id) {
        global $pdo;
        $s = $pdo->prepare(
            "SELECT * FROM reservas WHERE id = ? AND usuario_id = ?"
        );
        $s->execute([$id, $usuario_id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }

    // ── CANCELAR (solo el dueño) ──────────────────────────────────────────────
    public function cancelar($id, $usuario_id) {
        global $pdo;
        $s = $pdo->prepare(
            "DELETE FROM reservas WHERE id = ? AND usuario_id = ?"
        );
        return $s->execute([$id, $usuario_id]);
    }

    // ── VERIFICAR DISPONIBILIDAD ──────────────────────────────────────────────
    // $excluirId permite ignorar la reserva actual al editar
    public function estaDisponible($habitacion, $fecha_inicio, $fecha_fin, $excluirId = null) {
        global $pdo;

        $sql = "SELECT COUNT(*) FROM reservas
                WHERE habitacion = ?
                  AND fecha_inicio < ?
                  AND fecha_fin   > ?";

        $params = [$habitacion, $fecha_fin, $fecha_inicio];

        if ($excluirId !== null) {
            $sql     .= " AND id != ?";
            $params[] = $excluirId;
        }

        $s = $pdo->prepare($sql);
        $s->execute($params);
        return $s->fetchColumn() == 0;
    }

    // ── VERIFICAR QUE LA HABITACIÓN EXISTE ───────────────────────────────────
    public function existeHabitacion($numero) {
        global $pdo;
        $s = $pdo->prepare("SELECT id FROM habitaciones WHERE numero = ?");
        $s->execute([$numero]);
        return $s->fetch() !== false;
    }

    // ── ESTADO DE HABITACIONES ────────────────────────────────────────────────
    public function estadoHabitaciones($clase, $ini, $fin, $excluirId = null) {
        global $pdo;

        $sql = "
            SELECT h.numero,
                   CASE WHEN r.id IS NOT NULL THEN 'ocupado' ELSE 'disponible' END AS estado
            FROM habitaciones h
            LEFT JOIN reservas r
                ON h.numero = r.habitacion
               AND r.fecha_inicio < ?
               AND r.fecha_fin   > ?
               " . ($excluirId !== null ? "AND r.id != ?" : "") . "
            WHERE h.clase = ?
            ORDER BY h.numero
        ";

        $params = [$fin, $ini];
        if ($excluirId !== null) $params[] = $excluirId;
        $params[] = $clase;

        $s = $pdo->prepare($sql);
        $s->execute($params);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
}