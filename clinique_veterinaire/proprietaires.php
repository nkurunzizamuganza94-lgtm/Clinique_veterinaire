<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

// Ajout d'un propriétaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if (!empty($nom) && !empty($prenom) && !empty($telephone)) {
        $stmt = $pdo->prepare("INSERT INTO proprietaires (nom, prenom, telephone, email, adresse) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $telephone, $email, $adresse]);
        header('Location: proprietaires.php');
        exit;
    }
}

// Suppression d'un propriétaire
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM proprietaires WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: proprietaires.php');
    exit;
}

// Recherche
$search = trim($_GET['recherche'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM proprietaires WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $proprietaires = $stmt->fetchAll();
} else {
    $proprietaires = $pdo->query("SELECT * FROM proprietaires ORDER BY id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Propriétaires</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Clinique Vétérinaire</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link text-white fw-bold" href="proprietaires.php">Propriétaires</a>
            <a class="nav-link text-white" href="animaux.php">Animaux</a>
            <a class="nav-link text-white" href="consultations.php">Consultations</a>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Gestion des Propriétaires</h2>

    <!-- Formulaire de recherche -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-8">
            <input type="text" name="recherche" class="form-control" placeholder="Rechercher par nom, prénom ou téléphone..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-secondary">Rechercher</button>
            <a href="proprietaires.php" class="btn btn-outline-secondary">Réinitialiser</a>
        </div>
    </form>

    <!-- Formulaire d'ajout -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light"><strong>Ajouter un propriétaire</strong></div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="ajouter">
                <div class="col-md-3">
                    <input type="text" name="nom" class="form-control" placeholder="Nom *" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="prenom" class="form-control" placeholder="Prénom *" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="telephone" class="form-control" placeholder="Téléphone *" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email">
                </div>
                <div class="col-md-9">
                    <input type="text" name="adresse" class="form-control" placeholder="Adresse">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom & Prénom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proprietaires as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></td>
                <td><?= htmlspecialchars($p['telephone']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['adresse']) ?></td>
                <td>
                    <a href="proprietaires.php?supprimer=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce propriétaire ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>