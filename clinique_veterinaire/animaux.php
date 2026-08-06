<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// Récupération de la liste des animaux avec le nom du propriétaire
try {
    $stmt = $pdo->query("
        SELECT animaux.*, proprietaires.nom AS prop_nom, proprietaires.prenom AS prop_prenom 
        FROM animaux 
        LEFT JOIN proprietaires ON animaux.proprietaire_id = proprietaires.id 
        ORDER BY animaux.id DESC
    ");
    $animaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $animaux = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Animaux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Votre code HTML ici -->
</body>
</html>
