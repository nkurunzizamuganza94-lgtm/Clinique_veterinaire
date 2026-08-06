<?php
// Fix pour le reverse-proxy Render (HTTPS)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Configuration stricte et durable des cookies de session PHP sur Render
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 24 heures
        'path' => '/',
        'domain' => '',
        'secure' => true,     // Exigé par HTTPS
        'httponly' => true,   // Empêche le vol de session via JS
        'samesite' => 'Lax'   // Permet au cookie de persister lors de la navigation
    ]);
    session_start();
}

// Connexion à la base de données Aiven
$host = getenv('DB_HOST') ?: 'mysql-24df985f-nkurunzizamuganza94-7dfc.k.aivencloud.com';
$port = getenv('DB_PORT') ?: '12921';
$dbname = getenv('DB_NAME') ?: 'defaultdb';
$username = getenv('DB_USER') ?: 'avnadmin';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>