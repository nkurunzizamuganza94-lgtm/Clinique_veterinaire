<?php
// Configuration et démarrage de la session avec support HTTPS pour Render
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

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