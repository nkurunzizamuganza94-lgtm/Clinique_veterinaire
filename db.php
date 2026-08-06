<?php
// 1. Détection du reverse-proxy HTTPS Render
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 2. Configuration et démarrage propre de la session
if (session_status() === PHP_SESSION_NONE) {
    session_save_path(sys_get_temp_dir());
    
    session_set_cookie_params([
        'lifetime' => 86400, // 24 heures
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// 3. Connexion PDO à Aiven
$host     = getenv('DB_HOST')     ?: 'mysql-24df985f-nkurunzizamuganza94-7dfc.k.aivencloud.com';
$port     = getenv('DB_PORT')     ?: '12921';
$dbname   = getenv('DB_NAME')     ?: 'defaultdb';
$username = getenv('DB_USER')     ?: 'avnadmin';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA             => '/etc/ssl/certs/ca-certificates.crt',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>