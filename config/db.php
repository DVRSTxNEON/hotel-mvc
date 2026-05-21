<?php
// ── Configuración de la base de datos ────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'hotel');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ── Conexión PDO ──────────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lanza excepciones en errores SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // fetch devuelve arrays asociativos por defecto
            PDO::ATTR_EMULATE_PREPARES   => false,                    // usa prepared statements reales
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        "status" => "error",
        "msg"    => "Error de conexión a la base de datos: " . $e->getMessage()
    ]));
}