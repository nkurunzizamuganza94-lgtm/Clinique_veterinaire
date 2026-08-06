<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

// Ajout d'un animal avec gestion de photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $proprietaire_id = (int)$_POST['proprietaire_id'];
    $nom = trim($_POST['nom']);
    $espece = trim($_POST['espece']);
    $race = trim($_POST['race']);
    $date_naissance = $_POST['date_naissance'];
    $sexe = $_POST['sexe'];
    $nom_photo = 'default.png';

    // Upload d'image
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $extensions_autorisees)) {
            $nom_photo = uniqid('anim_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $nom_photo);
        }
    }

    if ($proprietaire_id > 0 && !empty($nom) && !empty($espece)) {
        $stmt = $pdo->prepare("INSERT INTO animaux (proprietaire_id, nom, espece, race, date_naissance, sexe, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$proprietaire_id, $nom, $espece, $race, $date_naissance, $sexe, $nom_photo]);
        header('Location: animaux.php');
        exit;
    }
}

// Récupération des propriétaires pour le sélecteur
$proprietaires = $pdo->query("SELECT id, nom, prenom FROM proprietaires ORDER BY nom ASC")->fetchAll();

// Filtre par espèce
$filtre_espece = trim($_GET['espece'] ?? '');
if (!empty($filtre_espece)) {
    $stmt = $pdo->prepare("SELECT a.*, CONCAT(p.nom, ' ', p.prenom) AS proprietaire_nom FROM animaux a JOIN proprietaires p ON a.proprietaire_id = p.id WHERE a.espece = ? ORDER BY a.id DESC");
    $stmt->execute([$filtre_espece]);
    $animaux = $stmt->fetchAll();
} else {
    $animaux = $pdo->query("SELECT a.*, CONCAT(p.nom, ' ', p.prenom) AS proprietaire_nom FROM animaux a JOIN proprietaires p ON a.proprietaire_id = p.id ORDER BY a.id DESC")->fetchAll();
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
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Clinique Vétérinaire</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link text-white" href="proprietaires.php">Propriétaires</a>
            <a class="nav-link text-white fw-bold" href="animaux.php">Animaux</a>
            <a class="nav-link text-white" href="consultations.php">Consultations</a>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Gestion des Animaux</h2>

    <!-- Filtre par espèce -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="espece" class="form-control" placeholder="Filtrer par espèce (ex: Chien, Chat...)" value="<?= htmlspecialchars($filtre_espece) ?>">
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            <a href="animaux.php" class="btn btn-outline-secondary">Réinitialiser</a>
        </div>
    </form>

    <!-- Formulaire d'ajout -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light"><strong>Ajouter un animal</strong></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="action" value="ajouter">
                <div class="col-md-4">
                    <label class="form-label">Propriétaire *</label>
                    <select name="proprietaire_id" class="form-select" required>
                        <option value="">Choisir...</option>
                        <?php foreach ($proprietaires as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom de l'animal *</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Espèce *</label>
                    <input type="text" name="espece" class="form-control" placeholder="Chien, Chat..." required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Race</label>
                    <input type="text" name="race" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date de Naissance</label>
                    <input type="date" name="date_naissance" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sexe *</label>
                    <select name="sexe" class="form-select" required>
                        <option value="M">Mâle</option>
                        <option value="F">Femelle</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Photo de l'animal</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">Enregistrer l'animal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste -->
    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Espèce / Race</th>
                <th>Sexe</th>
                <th>Propriétaire</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($animaux as $a): ?>
            <tr>
                <td>
                    <?php if (file_exists($a['photo']) && $a['photo'] !== 'default.png'): ?>
                        <img src="<?= htmlspecialchars($a['photo']) ?>" width="60" height="60" style="object-fit:cover;" class="rounded">
                    <?php else: ?>
                        <span class="badge bg-secondary">Pas de photo</span>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($a['nom']) ?></strong></td>
                <td><?= htmlspecialchars($a['espece']) ?> (<?= htmlspecialchars($a['race'] ?? 'N/A') ?>)</td>
                <td><?= $a['sexe'] === 'M' ? 'Mâle' : 'Femelle' ?></td>
                <td><?= htmlspecialchars($a['proprietaire_nom']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>