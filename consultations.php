<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

// Ajout d'une consultation et du traitement associé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $animal_id = (int)$_POST['animal_id'];
    $veterinaire_id = $_SESSION['user_id'];
    $diagnostic = trim($_POST['diagnostic']);
    $remarques = trim($_POST['remarques']);
    
    // Soins/Vaccins
    $type_soin = $_POST['type_soin'];
    $nom_produit = trim($_POST['nom_produit']);
    $dosage = trim($_POST['dosage']);

    if ($animal_id > 0 && !empty($diagnostic)) {
        $stmt = $pdo->prepare("INSERT INTO consultations (animal_id, veterinaire_id, diagnostic, remarques) VALUES (?, ?, ?, ?)");
        $stmt->execute([$animal_id, $veterinaire_id, $diagnostic, $remarques]);
        $consultation_id = $pdo->lastInsertId();

        if (!empty($nom_produit)) {
            $stmt2 = $pdo->prepare("INSERT INTO traitements_vaccinations (consultation_id, type, nom_produit, dosage) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$consultation_id, $type_soin, $nom_produit, $dosage]);
        }

        header('Location: consultations.php');
        exit;
    }
}

$animaux = $pdo->query("SELECT id, nom FROM animaux ORDER BY nom ASC")->fetchAll();

$consultations = $pdo->query("
    SELECT c.*, a.nom AS animal_nom, u.nom AS veterinaire_nom, t.type AS soin_type, t.nom_produit, t.dosage
    FROM consultations c
    JOIN animaux a ON c.animal_id = a.id
    JOIN utilisateurs u ON c.veterinaire_id = u.id
    LEFT JOIN traitements_vaccinations t ON t.consultation_id = c.id
    ORDER BY c.date_consultation DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Clinique Vétérinaire</a>
        <div class="navbar-nav me-auto">
            <a class="nav-link text-white" href="proprietaires.php">Propriétaires</a>
            <a class="nav-link text-white" href="animaux.php">Animaux</a>
            <a class="nav-link text-white fw-bold" href="consultations.php">Consultations</a>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Consultations & Traitements</h2>

    <!-- Formulaire -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light"><strong>Nouvelle Consultation</strong></div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="ajouter">
                <div class="col-md-6">
                    <label class="form-label">Animal *</label>
                    <select name="animal_id" class="form-select" required>
                        <option value="">Sélectionner un animal...</option>
                        <?php foreach ($animaux as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Diagnostic *</label>
                    <input type="text" name="diagnostic" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Remarques / Observations</label>
                    <textarea name="remarques" class="form-control" rows="2"></textarea>
                </div>
                <hr>
                <h5>Traitement / Vaccination associé (Optionnel)</h5>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type_soin" class="form-select">
                        <option value="traitement">Traitement</option>
                        <option value="vaccin">Vaccin</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nom du produit / vaccin</label>
                    <input type="text" name="nom_produit" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dosage</label>
                    <input type="text" name="dosage" class="form-control" placeholder="ex: 1 comprimé/jour">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">Enregistrer la consultation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Animal</th>
                <th>Vétérinaire</th>
                <th>Diagnostic</th>
                <th>Traitement / Vaccin</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($consultations as $c): ?>
            <tr>
                <td><?= $c['date_consultation'] ?></td>
                <td><strong><?= htmlspecialchars($c['animal_nom']) ?></strong></td>
                <td><?= htmlspecialchars($c['veterinaire_nom']) ?></td>
                <td><?= htmlspecialchars($c['diagnostic']) ?></td>
                <td>
                    <?php if ($c['nom_produit']): ?>
                        <span class="badge bg-<?= $c['soin_type'] === 'vaccin' ? 'danger' : 'info' ?>">
                            <?= strtoupper($c['soin_type']) ?>
                        </span>
                        <?= htmlspecialchars($c['nom_produit']) ?> (<?= htmlspecialchars($c['dosage']) ?>)
                    <?php else: ?>
                        <span class="text-muted">Aucun</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>