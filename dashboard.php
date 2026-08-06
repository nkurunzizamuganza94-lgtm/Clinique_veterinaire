<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

// Statistiques des données
$total_proprietaires = $pdo->query("SELECT COUNT(*) FROM proprietaires")->fetchColumn();
$total_animaux = $pdo->query("SELECT COUNT(*) FROM animaux")->fetchColumn();
$total_consultations = $pdo->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
$total_veterinaires = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'veterinaire'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'admin'")->fetchColumn();

// Statistiques des factures
$total_factures = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn();
$total_revenus = $pdo->query("SELECT SUM(montant) FROM factures WHERE statut = 'payee'")->fetchColumn() ?? 0;

// Récupération des 5 derniers propriétaires enregistrés
$stmt = $pdo->query("SELECT * FROM proprietaires ORDER BY id DESC LIMIT 5");
$recents_proprietaires = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Clinique Vétérinaire</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { min-height: 100vh; background-color: #f8f9fa; }
        .wrapper { display: flex; width: 100%; }
        #sidebar { min-width: 250px; max-width: 250px; background: #0d6efd; color: #fff; min-height: 100vh; }
        #sidebar .sidebar-header { padding: 20px; background: #0b5ed7; }
        #sidebar ul.components { padding: 20px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 1.05em; display: block; color: rgba(255, 255, 255, 0.85); text-decoration: none; }
        #sidebar ul li a:hover, #sidebar ul li.active > a { color: #fff; background: rgba(255, 255, 255, 0.15); border-left: 4px solid #fff; }
        #content { width: 100%; padding: 20px 30px; min-height: 100vh; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Barre Latérale (Sidebar) -->
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-clinic-medical me-2"></i>Clinique Véterinaire</h5>
        </div>

        <ul class="list-unstyled components">
            <li class="active">
                <a href="dashboard.php"><i class="fa-solid fa-chart-line me-2"></i> Tableau de bord</a>
            </li>
            <li>
                <a href="proprietaires.php"><i class="fa-solid fa-users me-2"></i> Propriétaires</a>
            </li>
            <li>
                <a href="animaux.php"><i class="fa-solid fa-paw me-2"></i> Animaux</a>
            </li>
            <li>
                <a href="consultations.php"><i class="fa-solid fa-stethoscope me-2"></i> Consultations</a>
            </li>
            <li>
                <a href="factures.php"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Factures</a>
            </li>

            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <hr class="my-2 bg-light opacity-25">
                <li>
                    <a href="admin_utilisateurs.php" class="text-warning fw-bold">
                        <i class="fa-solid fa-users-gear me-2"></i> Utilisateurs / Admins
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Contenu Principal -->
    <div id="content">
        <!-- Barre Supérieure -->
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
            <h4 class="fw-bold text-primary mb-0">Gestion d'une clinique vétérinaire</h4>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-semibold text-secondary">
                    <i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_nom']) ?>
                    <span class="badge bg-primary ms-1"><?= ucfirst(htmlspecialchars($_SESSION['user_role'] ?? 'utilisateur')) ?></span>
                </span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Déconnexion</a>
            </div>
        </div>

        <!-- Bannière d'accueil -->
        <div class="card mb-4 shadow-sm border-0 overflow-hidden">
            <div class="row g-0 align-items-center">
                <div class="col-md-4">
                    <img src="ALEXIS.JPG" alt="Vache de la clinique" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
                </div>
                <div class="col-md-8">
                    <div class="card-body p-4">
                        <h3 class="card-title text-primary fw-bold mb-2">Gestion d'une clinique vétérinaire</h3>
                        <p class="card-text text-muted mb-3">
                            Bienvenue sur votre espace de travail. Gérez facilement les soins, les consultations, les factures et les comptes utilisateurs.
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="proprietaires.php" class="btn btn-info text-white btn-sm"><i class="fa-solid fa-user-plus me-1"></i> Nouveau propriétaire</a>
                            <a href="animaux.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-paw me-1"></i> Ajouter un animal</a>
                            <a href="factures.php" class="btn btn-success btn-sm"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Créer Facture</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-3 fw-bold">Statistiques Générales</h5>
        
        <!-- Cartes des Statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card bg-dark text-white shadow-sm border-0">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase fw-bold fs-7"><i class="fa-solid fa-user-shield me-1"></i>Admins</h6>
                        <h2 class="display-6 fw-bold mb-0"><?= $total_admins ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white shadow-sm border-0">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase fw-bold fs-7"><i class="fa-solid fa-user-doctor me-1"></i>Vétos</h6>
                        <h2 class="display-6 fw-bold mb-0"><?= $total_veterinaires ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white shadow-sm border-0">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase fw-bold fs-7"><i class="fa-solid fa-users me-1"></i>Clients</h6>
                        <h2 class="display-6 fw-bold mb-0"><?= $total_proprietaires ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white shadow-sm border-0">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase fw-bold fs-7"><i class="fa-solid fa-paw me-1"></i>Animaux</h6>
                        <h2 class="display-6 fw-bold mb-0"><?= $total_animaux ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm border-0">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase fw-bold fs-7"><i class="fa-solid fa-sack-dollar me-1"></i>Revenus Total Payés</h6>
                        <h2 class="display-6 fw-bold mb-0"><?= number_format($total_revenus, 2) ?> $</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section des derniers propriétaires -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-users me-2"></i>Derniers Propriétaires Enregistrés</h6>
                <a href="proprietaires.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom & Prénom</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>Adresse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recents_proprietaires) > 0): ?>
                                <?php foreach ($recents_proprietaires as $proprio): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($proprio['nom'] . ' ' . ($proprio['prenom'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($proprio['telephone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($proprio['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($proprio['adresse'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Aucun propriétaire enregistré.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>