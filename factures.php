<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$message = '';
$error = '';

// Ajout d'une nouvelle facture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom = trim($_POST['proprietaire_nom']);
    $montant = (float)$_POST['montant'];
    $statut = $_POST['statut'] ?? 'payee';
    $description = trim($_POST['description']);

    if (!empty($nom) && $montant > 0) {
        $stmt = $pdo->prepare("INSERT INTO factures (proprietaire_nom, montant, statut, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nom, $montant, $statut, $description])) {
            $message = "Facture enregistrée avec succès !";
        } else {
            $error = "Erreur lors de l'enregistrement.";
        }
    } else {
        $error = "Veuillez remplir correctement les champs.";
    }
}

// Suppression d'une facture (réservé Admin)
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id']) && ($_SESSION['user_role'] ?? '') === 'admin') {
    $stmt = $pdo->prepare("DELETE FROM factures WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    header('Location: factures.php');
    exit;
}

// Récupération des factures
$stmt = $pdo->query("SELECT * FROM factures ORDER BY id DESC");
$factures = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Factures - Clinique Vétérinaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; min-height: 100vh; }
        .wrapper { display: flex; width: 100%; }
        #sidebar { min-width: 250px; max-width: 250px; background: #0d6efd; color: #fff; min-height: 100vh; }
        #sidebar .sidebar-header { padding: 20px; background: #0b5ed7; }
        #sidebar ul.components { padding: 20px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 1.05em; display: block; color: rgba(255,255,255,0.85); text-decoration: none; }
        #sidebar ul li a:hover, #sidebar ul li.active > a { color: #fff; background: rgba(255,255,255,0.15); border-left: 4px solid #fff; }
        #content { width: 100%; padding: 20px 30px; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-clinic-medical me-2"></i>Clinique Véto</h5>
        </div>
        <ul class="list-unstyled components">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-line me-2"></i> Tableau de bord</a></li>
            <li><a href="proprietaires.php"><i class="fa-solid fa-users me-2"></i> Propriétaires</a></li>
            <li><a href="animaux.php"><i class="fa-solid fa-paw me-2"></i> Animaux</a></li>
            <li><a href="consultations.php"><i class="fa-solid fa-stethoscope me-2"></i> Consultations</a></li>
            <li class="active"><a href="factures.php"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Factures</a></li>
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <hr class="my-2 bg-light opacity-25">
                <li><a href="admin_utilisateurs.php" class="text-warning fw-bold"><i class="fa-solid fa-users-gear me-2"></i> Utilisateurs / Admins</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
            <h4 class="fw-bold text-primary mb-0"><i class="fa-solid fa-receipt me-2"></i>Gestion des Factures</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFacture">
                <i class="fa-solid fa-plus me-1"></i> Créer une Facture
            </button>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># ID</th>
                                <th>Client / Propriétaire</th>
                                <th>Montant ($)</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($factures) > 0): ?>
                                <?php foreach ($factures as $f): ?>
                                    <tr>
                                        <td><strong>#<?= $f['id'] ?></strong></td>
                                        <td class="fw-bold"><?= htmlspecialchars($f['proprietaire_nom']) ?></td>
                                        <td class="text-success fw-bold"><?= number_format($f['montant'], 2) ?> $</td>
                                        <td>
                                            <?php if ($f['statut'] === 'payee'): ?>
                                                <span class="badge bg-success">Payée</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">En attente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($f['date_facture'])) ?></td>
                                        <td><?= htmlspecialchars($f['description'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <!-- Bouton d'impression ouvrant le modèle officiel -->
                                            <a href="imprimer_facture.php?id=<?= $f['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-print me-1"></i> Facture Pro
                                            </a>
                                            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                                <a href="factures.php?action=supprimer&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette facture ?');">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">Aucune facture enregistrée.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal formulaire d'ajout -->
<div class="modal fade" id="modalFacture" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="ajouter">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Nouvelle Facture</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du Propriétaire / Client *</label>
                        <input type="text" name="proprietaire_nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant Total ($) *</label>
                        <input type="number" step="0.01" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut du Paiement</label>
                        <select name="statut" class="form-select">
                            <option value="payee">Payée</option>
                            <option value="en_attente">En attente</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description / Soins effectués</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Ex: Consultation + Traitement vaccin"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer la Facture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>