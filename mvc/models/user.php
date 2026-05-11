<?php
require_once __DIR__ . "/../config/db.php";

class User {

    public function crear($nombre, $email, $password, $cedula) {
        global $pdo;
        $s = $pdo->prepare(
            "INSERT INTO usuarios (nombre, email, password, cedula)
             VALUES (?, ?, ?, ?)"
        );
        return $s->execute([$nombre, $email, $password, $cedula]);
    }

    public function existeEmail($email) {
        global $pdo;
        $s = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $s->execute([$email]);
        return $s->fetch() !== false;
    }

    public function existeCedula($cedula) {
        global $pdo;
        $s = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ?");
        $s->execute([$cedula]);
        return $s->fetch() !== false;
    }

    public function login($email) {
        global $pdo;
        $s = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $s->execute([$email]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }
}