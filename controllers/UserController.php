<?php
require_once "models/User.php";
require_once "config/mailer.php";   // ← AGREGADO

class UserController {

    private function res($status, $msg) {
        header('Content-Type: application/json');
        echo json_encode(["status" => $status, "msg" => $msg]);
        exit;
    }

    // ── REGISTRO ──────────────────────────────────────────────────────────────
    public function register() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->res("error", "Acceso inválido");
        }

        if (!isset($_POST['nombre'], $_POST['email'], $_POST['password'], $_POST['cedula'])) {
            return $this->res("error", "Faltan datos");
        }

        $n = trim($_POST['nombre']);
        $e = trim($_POST['email']);
        $p = $_POST['password'];
        $c = trim($_POST['cedula']);

        if ($n === "" || !preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $n)) {
            return $this->res("error", "Nombre inválido (solo letras y espacios)");
        }

        if (!filter_var($e, FILTER_VALIDATE_EMAIL)) {
            return $this->res("error", "Correo inválido");
        }

        if (!preg_match("/^[0-9]{6,15}$/", $c)) {
            return $this->res("error", "Cédula inválida (solo números, 6 a 15 dígitos)");
        }

        if (
            strlen($p) < 6              ||
            !preg_match("/[A-Z]/", $p)  ||
            !preg_match("/[a-z]/", $p)  ||
            !preg_match("/[0-9]/", $p)  ||
            !preg_match("/[\W_]/", $p)
        ) {
            return $this->res("error", "La contraseña debe tener al menos 6 caracteres, mayúscula, minúscula, número y carácter especial");
        }

        $user = new User();

        if ($user->existeEmail($e)) {
            return $this->res("error", "El correo ya está registrado");
        }

        if ($user->existeCedula($c)) {
            return $this->res("error", "La cédula ya está registrada");
        }

        $user->crear($n, $e, password_hash($p, PASSWORD_DEFAULT), $c);

        return $this->res("ok", "Registro exitoso");
    }

    // ── LOGIN ─────────────────────────────────────────────────────────────────
    public function login() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->res("error", "Acceso inválido");
        }

        if (!isset($_POST['email'], $_POST['password'])) {
            return $this->res("error", "Faltan datos");
        }

        $e = trim($_POST['email']);
        $p = $_POST['password'];

        if (!filter_var($e, FILTER_VALIDATE_EMAIL)) {
            return $this->res("error", "Correo inválido");
        }

        $user = (new User())->login($e);

        if (!$user || !password_verify($p, $user['password'])) {
            return $this->res("error", "Credenciales incorrectas");
        }

        $_SESSION['user'] = [
            'id'     => $user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
            'cedula' => $user['cedula'],
        ];

        // ── NOTIFICACIÓN DE LOGIN ─────────────────────────────────────────────
        enviarCorreoLogin($_SESSION['user']);   // ← AGREGADO
        // ─────────────────────────────────────────────────────────────────────

        return $this->res("ok", "Bienvenido");
    }

    // ── LOGOUT ────────────────────────────────────────────────────────────────
    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}