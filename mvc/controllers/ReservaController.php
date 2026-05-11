<?php
require_once "models/Reserva.php";

class ReservaController {

    private function res($status, $msg, $data = null) {
        header('Content-Type: application/json');
        $r = ["status" => $status, "msg" => $msg];
        if ($data !== null) $r["data"] = $data;
        echo json_encode($r);
        exit;
    }

    private function requireSession() {
        if (!isset($_SESSION['user'])) {
            $this->res("error", "Sesión no iniciada");
        }
    }

    // ── GUARDAR RESERVA ──────────────────────────────────────────────────────
    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->res("error", "Acceso inválido");
        }

        $this->requireSession();

        if (!isset($_POST['habitacion'], $_POST['fecha_inicio'], $_POST['fecha_fin'])) {
            return $this->res("error", "Faltan datos");
        }

        $habitacion   = trim($_POST['habitacion']);
        $fecha_inicio = trim($_POST['fecha_inicio']);
        $fecha_fin    = trim($_POST['fecha_fin']);

        if (empty($habitacion)) {
            return $this->res("error", "Selecciona una habitación");
        }

        // Validar formato de fechas
        if (!$this->validarFecha($fecha_inicio) || !$this->validarFecha($fecha_fin)) {
            return $this->res("error", "Formato de fecha inválido");
        }

        // fecha_inicio debe ser hoy o futura
        if ($fecha_inicio < date('Y-m-d')) {
            return $this->res("error", "La fecha de inicio no puede ser en el pasado");
        }

        if ($fecha_inicio >= $fecha_fin) {
            return $this->res("error", "La fecha de fin debe ser posterior a la de inicio");
        }

        $model = new Reserva();

        // Verificar que la habitación existe
        if (!$model->existeHabitacion($habitacion)) {
            return $this->res("error", "Habitación no encontrada");
        }

        // Verificar disponibilidad
        if (!$model->estaDisponible($habitacion, $fecha_inicio, $fecha_fin)) {
            return $this->res("error", "La habitación no está disponible en esas fechas");
        }

        $model->guardar(
            $_SESSION['user']['id'],
            $habitacion,
            $fecha_inicio,
            $fecha_fin
        );

        return $this->res("ok", "Reserva creada exitosamente");
    }

    // ── LISTAR RESERVAS DEL USUARIO ──────────────────────────────────────────
    public function listar() {
        $this->requireSession();
        $data = (new Reserva())->listar($_SESSION['user']['id']);
        return $this->res("ok", "ok", $data);
    }

    // ── CANCELAR RESERVA ─────────────────────────────────────────────────────
    public function cancelar() {
        $this->requireSession();

        if (!isset($_POST['id'])) {
            return $this->res("error", "Falta el ID de la reserva");
        }

        $id = (int) $_POST['id'];
        $model = new Reserva();

        // Verificar que la reserva pertenece al usuario
        $reserva = $model->obtenerPorId($id, $_SESSION['user']['id']);
        if (!$reserva) {
            return $this->res("error", "Reserva no encontrada");
        }

        $model->cancelar($id, $_SESSION['user']['id']);
        return $this->res("ok", "Reserva cancelada");
    }

    // ── ESTADO DE HABITACIONES ───────────────────────────────────────────────
    public function habitaciones() {

        if (!isset($_GET['clase'], $_GET['inicio'], $_GET['fin'])) {
            return $this->res("error", "Faltan parámetros");
        }

        $clase = trim($_GET['clase']);
        $ini   = trim($_GET['inicio']);
        $fin   = trim($_GET['fin']);

        $clasesValidas = ['estandar', 'suite', 'deluxe'];
        if (!in_array($clase, $clasesValidas)) {
            return $this->res("error", "Clase de habitación inválida");
        }

        if (!$this->validarFecha($ini) || !$this->validarFecha($fin)) {
            return $this->res("error", "Fechas inválidas");
        }

        if ($ini >= $fin) {
            return $this->res("error", "La fecha de fin debe ser posterior a la de inicio");
        }

        $data = (new Reserva())->estadoHabitaciones($clase, $ini, $fin);
        echo json_encode($data);
        exit;
    }

    // ── PDF INDIVIDUAL ────────────────────────────────────────────────────────
    public function pdfReserva() {

        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            echo "Acceso denegado";
            exit;
        }

        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo "Falta el ID";
            exit;
        }

        $id = (int) $_GET['id'];
        $model = new Reserva();

        // Solo puede ver sus propias reservas
        $r = $model->obtenerPorId($id, $_SESSION['user']['id']);

        if (!$r) {
            http_response_code(404);
            echo "Reserva no encontrada";
            exit;
        }

        require_once "libs/fpdf/fpdf.php";

        // Calcular noches
        $inicio  = new DateTime($r['fecha_inicio']);
        $fin     = new DateTime($r['fecha_fin']);
        $noches  = $inicio->diff($fin)->days;

        $pdf = new FPDF();
        $pdf->AddPage();

        // ── Encabezado ──────────────────────────────
        $pdf->SetFillColor(30, 58, 95);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 18, 'Hotel Luxury', 0, 1, 'C', true);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, 'Comprobante de Reserva', 0, 1, 'C', true);
        $pdf->Ln(6);

        // ── Datos de la reserva ──────────────────────
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(240, 244, 250);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Detalle de la Reserva', 0, 1, 'L', true);
        $pdf->Ln(2);

        $campos = [
            ['N\xba de Reserva', '#' . str_pad($r['id'], 5, '0', STR_PAD_LEFT)],
            ['Habitaci\xf3n',    $r['habitacion']],
            ['Fecha Inicio',     $r['fecha_inicio']],
            ['Fecha Fin',        $r['fecha_fin']],
            ['Noches',           $noches . ' noche(s)'],
            ['Hu\xe9sped',       $_SESSION['user']['nombre']],
            ['C\xe9dula',        $_SESSION['user']['cedula']],
        ];

        $pdf->SetFont('Arial', '', 11);
        foreach ($campos as $i => $campo) {
            $fill = ($i % 2 === 0);
            $pdf->SetFillColor($fill ? 248 : 255, $fill ? 249 : 255, $fill ? 252 : 255);
            $pdf->Cell(60,  9, $campo[0] . ':', 0, 0, 'L', true);
            $pdf->Cell(120, 9, $campo[1],        0, 1, 'L', true);
        }

        $pdf->Ln(8);

        // ── Pie ──────────────────────────────────────
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 8, 'Generado el ' . date('d/m/Y H:i') . ' - Hotel Luxury', 0, 1, 'C');

        $pdf->Output('I', 'Reserva_' . str_pad($r['id'], 5, '0', STR_PAD_LEFT) . '.pdf');
        exit;
    }

    // ── EXCEL GENERAL ─────────────────────────────────────────────────────────
    public function excelReservas() {

        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            echo "Acceso denegado";
            exit;
        }

        $data = (new Reserva())->listar($_SESSION['user']['id']);

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reservas_" . date('Ymd_His') . ".xls");
        header("Cache-Control: max-age=0");

        // BOM para UTF-8
        echo "\xEF\xBB\xBF";

        // Encabezado del reporte
        echo "Hotel Luxury - Reporte de Reservas\t\t\t\n";
        echo "Huésped:\t" . $_SESSION['user']['nombre'] . "\t\t\n";
        echo "Cédula:\t"  . $_SESSION['user']['cedula']  . "\t\t\n";
        echo "Generado:\t" . date('d/m/Y H:i') . "\t\t\n";
        echo "\n";

        // Cabecera de tabla
        echo "# Reserva\tHabitación\tFecha Inicio\tFecha Fin\tNoches\n";

        if (empty($data)) {
            echo "Sin reservas registradas\t\t\t\t\n";
        } else {
            foreach ($data as $r) {
                $noches = (new DateTime($r['fecha_inicio']))->diff(new DateTime($r['fecha_fin']))->days;
                echo str_pad($r['id'], 5, '0', STR_PAD_LEFT) . "\t"
                   . $r['habitacion']    . "\t"
                   . $r['fecha_inicio']  . "\t"
                   . $r['fecha_fin']     . "\t"
                   . $noches            . "\n";
            }
        }
        exit;
    }

    // ── UTILIDADES ────────────────────────────────────────────────────────────
    private function validarFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}