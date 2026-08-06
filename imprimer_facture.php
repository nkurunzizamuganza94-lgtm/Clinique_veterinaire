<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

if (!isset($_GET['id'])) {
    die("ID Facture non spécifié.");
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
$stmt->execute([$id]);
$facture = $stmt->fetch();

if (!$facture) {
    die("Facture introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture_#<?= $facture['id'] ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .invoice-card {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 30px auto;
        }
        .invoice-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-logo {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #0d6efd;
        }
        .table-invoice th {
            background-color: #f1f5f9;
        }
        @media print {
            body { background-color: #fff; }
            .invoice-card { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Boutons d'action non imprimables -->
    <div class="text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 fw-bold me-2">
            <i class="fa-solid fa-print me-2"></i>Imprimer la facture
        </button>
        <a href="factures.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Retour aux factures
        </a>
    </div>

    <!-- Modèle de Facture Officielle -->
    <div class="invoice-card">
        <!-- En-tête avec Photo / Logo -->
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <img src="ALEXIS.jpg" alt="Logo Clinique" class="invoice-logo">
                <div>
                    <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-clinic-medical me-2"></i>CLINIQUE VÉTÉRINAIRE</h3>
                    <p class="text-muted small mb-0">Services de soins, consultations & traitements</p>
                    <p class="text-muted small mb-0">Téléphone: +243 000 000 000 | Email: contact@clinique.com</p>
                </div>
            </div>
            <div class="text-end">
                <h2 class="fw-bold text-uppercase text-secondary mb-0">FACTURE</h2>
                <h5 class="fw-bold text-primary">N° #<?= sprintf('%04d', $facture['id']) ?></h5>
                <small class="text-muted">Date: <?= date('d/m/Y à H:i', strtotime($facture['date_facture'])) ?></small>
            </div>
        </div>

        <!-- Informations Client / Statut -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="text-uppercase fw-bold text-muted small">Facturé à :</h6>
                <h5 class="fw-bold text-dark"><?= htmlspecialchars($facture['proprietaire_nom']) ?></h5>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-uppercase fw-bold text-muted small">Statut du paiement :</h6>
                <?php if ($facture['statut'] === 'payee'): ?>
                    <span class="badge bg-success fs-6 px-3 py-2">PAYÉE</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">EN ATTENTE</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tableau des détails -->
        <table class="table table-bordered table-invoice mb-4">
            <thead>
                <tr>
                    <th>Désignation / Description du service</th>
                    <th class="text-end" style="width: 200px;">Montant Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= !empty($facture['description']) ? htmlspecialchars($facture['description']) : "Prestations de soins vétérinaires" ?></strong>
                    </td>
                    <td class="text-end fw-bold fs-5"><?= number_format($facture['montant'], 2) ?> $</td>
                </tr>
            </tbody>
        </table>

        <!-- Résumé Financier -->
        <div class="row justify-content-end mb-5">
            <div class="col-md-5">
                <div class="d-flex justify-content-between p-2 bg-light rounded fw-bold fs-5 border">
                    <span>Total à payer :</span>
                    <span class="text-primary"><?= number_format($facture['montant'], 2) ?> $</span>
                </div>
            </div>
        </div>

        <!-- Bas de page & Signature -->
        <div class="row pt-4 border-top mt-5">
            <div class="col-6">
                <p class="small text-muted mb-0">Merci pour votre confiance !</p>
                <p class="small text-muted">Clinique Vétérinaire - Santé & Bien-être animal</p>
            </div>
            <div class="col-6 text-end">
                <p class="fw-bold mb-5">Cachet & Signature de la Clinique</p>
                <p class="text-muted">_______________________</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>