<?php
session_start();
require_once 'db.php';

// VERROUILLAGE UNIQUE : Remplacez cet email par VOTRE adresse email exacte
$ADMIN_EMAIL_UNIQUE = 'votre_email_admin@exemple.com'; // Ex: 'admin@clinique.com'

// Vérification stricte : Seul VOTRE compte (connecté ET avec votre email) a l'accès
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérification en base de données pour s'assurer que c'est bien votre compte
$stmtCheck = $pdo->prepare("SELECT email, role FROM utilisateurs WHERE id = ?");
$stmtCheck->execute([$_SESSION['user_id']]);
$userConnecte = $stmtCheck->fetch();

if (!$userConnecte || $userConnecte['email'] !== $ADMIN_EMAIL_UNIQUE || $userConnecte['role'] !== 'admin') {
    // Si ce n'est pas VOUS, redirection immédiate vers le tableau de bord
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

// Traitement de la suppression d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_a_supprimer = (int)$_GET['id'];

    if ($id_a_supprimer === (int)$_SESSION['user_id']) {
        $error = "Vous ne pouvez pas supprimer votre propre compte principal.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        if ($stmt->execute([$id_a_supprimer])) {
            $message = "L'utilisateur a été supprimé avec succès.";
        } else {
            $error = "Une erreur est survenue lors de la suppression.";
        }
    }
}

// Récupération de tous les utilisateurs
$stmt = $pdo->query("SELECT id, nom, email, role FROM utilisateurs ORDER BY id DESC");
$utilisateurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs - Super Admin Seul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Gestion d'une clinique vétérinaire</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link text-white" href="dashboard.php">Tableau de bord</a>
            <a class="nav-link text-white fw-bold active" href="admin_utilisateurs.php">Espace Administrateur Unique</a>
        </div>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 text-white">
                <?= htmlspecialchars($_SESSION['user_nom']) ?> 
                <span class="badge bg-danger ms-1">Admin Unique</span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="fa-solid fa-user-shield me-2"></i>Gestion Utilisateurs (Accès Exclusif)</h3>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Adresse Email</th>
                            <th>Rôle</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $user): ?>
                            <tr class="<?= ((int)$user['id'] === (int)$_SESSION['user_id']) ? 'table-warning' : '' ?>">
                                <td><?= $user['id'] ?></td>
                                <td class="fw-bold">
                                    <?= htmlspecialchars($user['nom']) ?>
                                    <?php if ((int)$user['id'] === (int)$_SESSION['user_id']): ?>
                                        <span class="badge bg-danger ms-1">Propriétaire du Système</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Administrateur</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Vétérinaire</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                        <a href="admin_utilisateurs.php?action=supprimer&id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                            <i class="fa-solid fa-trash me-1"></i> Supprimer
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Votre Compte</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>