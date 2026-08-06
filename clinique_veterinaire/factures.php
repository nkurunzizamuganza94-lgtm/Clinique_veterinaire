<?php
require_once 'db.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM factures ORDER BY id DESC");
    $factures = $stmt->fetchAll();
} catch (PDOException $e) {
    $factures = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Factures - Clinique Vétérinaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #1e3c72; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #2a5298; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar p-0">
            <div class="p-3 text-center border-bottom border-secondary">
                <h4 class="fw-bold m-0">Clinique Vétérinaire</h4>
            </div>
            <div class="py-3">
                <a href="dashboard.php"><i class="fa-solid fa-chart-line me-2"></i> Tableau de bord</a>
                <a href="animaux.php"><i class="fa-solid fa-paw me-2"></i> Animaux</a>
                <a href="consultations.php"><i class="fa-solid fa-stethoscope me-2"></i> Consultations</a>
                <a href="factures.php" class="active"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Factures</a>
                <a href="admin_utilisateurs.php"><i class="fa-solid fa-users me-2"></i> Utilisateurs</a>
                <hr class="text-light">
                <a href="logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Déconnexion</a>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Facturation</h2>
                <button class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Créer une facture</button>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Montant ($)</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($factures)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Aucune facture émise.</td></tr>
                            <?php else: ?>
                                <?php foreach ($factures as $f): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($f['id']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($f['proprietaire_nom']) ?></td>
                                        <td><?= number_format($f['montant'], 2) ?> $</td>
                                        <td>
                                            <span class="badge bg-<?= $f['statut'] === 'payee' ? 'success' : 'warning' ?>">
                                                <?= htmlspecialchars($f['statut']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($f['date_facture']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
