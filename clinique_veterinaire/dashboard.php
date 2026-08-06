<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification stricte de la connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: login.php');
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

require_once 'db.php';

// Récupération des statistiques globales
try {
    $total_animaux = $pdo->query("SELECT COUNT(*) FROM animaux")->fetchColumn();
    $total_proprietaires = $pdo->query("SELECT COUNT(*) FROM proprietaires")->fetchColumn();
    $total_consultations = $pdo->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
    $total_factures = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn();
} catch (PDOException $e) {
    $total_animaux = $total_proprietaires = $total_consultations = $total_factures = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Clinique Vétérinaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #1e3c72; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #2a5298; color: white; }
        .card-stat { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Barre de navigation latérale -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
            <div class="p-3 text-center border-bottom border-secondary">
                <h4 class="fw-bold m-0">Clinique Vétérinaire</h4>
            </div>
            <div class="py-3">
                <a href="dashboard.php" class="active"><i class="fa-solid fa-chart-line me-2"></i> Tableau de bord</a>
                <a href="animaux.php"><i class="fa-solid fa-paw me-2"></i> Animaux</a>
                <a href="consultations.php"><i class="fa-solid fa-stethoscope me-2"></i> Consultations</a>
                <a href="factures.php"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Factures</a>
                <a href="admin_utilisateurs.php"><i class="fa-solid fa-users me-2"></i> Utilisateurs</a>
                <hr class="text-light">
                <a href="logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Déconnexion</a>
            </div>
        </nav>

        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Bienvenue, <?= htmlspecialchars($_SESSION['nom'] ?? 'Administrateur') ?> !</h2>
                <span class="badge bg-primary fs-6"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></span>
            </div>

            <!-- Cartes de statistiques -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-stat bg-primary text-white p-3">
                        <h5>Animaux enregistrés</h5>
                        <h2 class="fw-bold"><?= $total_animaux ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat bg-success text-white p-3">
                        <h5>Propriétaires</h5>
                        <h2 class="fw-bold"><?= $total_proprietaires ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat bg-warning text-dark p-3">
                        <h5>Consultations</h5>
                        <h2 class="fw-bold"><?= $total_consultations ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat bg-info text-white p-3">
                        <h5>Factures</h5>
                        <h2 class="fw-bold"><?= $total_factures ?></h2>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
