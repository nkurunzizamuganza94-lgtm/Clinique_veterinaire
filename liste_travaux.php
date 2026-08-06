<?php
require_once 'auth_check.php';
require_once 'db.php';

// Filtres de recherche
$filtre_statut = $_GET['statut'] ?? '';
$filtre_priorite = $_GET['priorite'] ?? '';
$recherche = trim($_GET['recherche'] ?? '');

// Construction dynamique de la requête SQL
$sql = "SELECT t.*, 
               a.nom_complet AS client_nom, a.code_client, a.adresse_physique,
               tech.nom AS tech_nom, tech.prenom AS tech_prenom, tech.matricule AS tech_mat,
               c.numero_serie AS num_compteur
        FROM travaux t
        LEFT JOIN abonnes a ON t.abonne_id = a.id
        LEFT JOIN techniciens tech ON t.technicien_id = tech.id
        LEFT JOIN compteurs c ON t.compteur_id = c.id
        WHERE 1=1";

$params = [];

if (!empty($filtre_statut)) {
    $sql .= " AND t.statut = :statut";
    $params[':statut'] = $filtre_statut;
}

if (!empty($filtre_priorite)) {
    $sql .= " AND t.priorite = :priorite";
    $params[':priorite'] = $filtre_priorite;
}

if (!empty($recherche)) {
    $sql .= " AND (t.reference_ticket LIKE :q OR a.nom_complet LIKE :q OR c.numero_serie LIKE :q)";
    $params[':q'] = "%$recherche%";
}

$sql .= " ORDER BY t.date_signalement DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$travaux = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Travaux — REGIDESO</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; color: #333; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background-color: #1a202c; color: #fff; position: fixed; height: 100vh; top: 0; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 0 20px 20px 20px; font-size: 20px; font-weight: bold; border-bottom: 1px solid #2d3748; color: #3182ce; }
        .sidebar-menu { list-style: none; margin-top: 20px; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 20px; color: #cbd5e0; text-decoration: none; font-size: 15px; transition: 0.2s; }
        .sidebar-menu li a:hover, .sidebar-menu li.active a { background-color: #2d3748; color: #fff; border-left: 4px solid #3182ce; }
        .sidebar-menu .icon { margin-right: 12px; font-weight: bold; width: 20px; text-align: center; }

        /* Contenu Principal */
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header h1 { color: #1a365d; font-size: 24px; }
        .btn-add { background-color: #38a169; color: white; padding: 10px 16px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; }
        .btn-add:hover { background-color: #2f855a; }

        /* Barre de filtres */
        .filter-card { background: white; padding: 18px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .filter-form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filter-form input, .filter-form select { padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 14px; }
        .filter-form input[type="text"] { flex: 1; min-width: 200px; }
        .btn-filter { background: #3182ce; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-reset { background: #718096; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; font-size: 14px; }

        /* Tableau */
        .table-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        th { background-color: #f7fafc; color: #4a5568; font-weight: bold; }
        tr:hover { background-color: #f8fafc; }

        /* Badges */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; display: inline-block; }
        .bg-signale { background-color: #718096; }
        .bg-encours { background-color: #dd6b20; }
        .bg-termine { background-color: #38a169; }
        .bg-annule { background-color: #e53e3e; }
        
        .bg-haute { background-color: #e53e3e; }
        .bg-moyenne { background-color: #3182ce; }
        .bg-basse { background-color: #a0aec0; }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">💧 REGIDESO Eau</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><span class="icon">📊</span> Tableau de bord</a></li>
            <li class="active"><a href="liste_travaux.php"><span class="icon">📋</span> Registre Travaux</a></li>
            <li><a href="ajouter_travail.php"><span class="icon">➕</span> Créer Ticket</a></li>
            <li><a href="gestion_abonnes.php"><span class="icon">👥</span> Abonnés & Clients</a></li>
            <li><a href="gestion_techniciens.php"><span class="icon">👷</span> Techniciens</a></li>
        </ul>

        <div style="margin-top: auto; padding: 15px 20px; border-top: 1px solid #2d3748; background: #141923;">
            <div style="font-size: 13px; font-weight: bold; color: #fff;"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></div>
            <div style="font-size: 11px; color: #a0aec0; margin-bottom: 10px;"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Agent') ?></div>
            <a href="logout.php" style="display: block; text-align: center; background: #e53e3e; color: white; padding: 6px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold;">🚪 Déconnexion</a>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="main-content">
        
        <div class="header">
            <h1>Registre des Travaux & Interventions</h1>
            <a href="ajouter_travail.php" class="btn-add">+ Nouveau Travail</a>
        </div>

        <!-- Section Filtres de Recherche -->
        <div class="filter-card">
            <form method="GET" class="filter-form">
                <input type="text" name="recherche" placeholder="Rechercher ticket, client ou compteur..." value="<?= htmlspecialchars($recherche) ?>">

                <select name="statut">
                    <option value="">-- Tous les statuts --</option>
                    <option value="Signalé" <?= $filtre_statut === 'Signalé' ? 'selected' : '' ?>>Signalé</option>
                    <option value="En cours" <?= $filtre_statut === 'En cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="Terminé" <?= $filtre_statut === 'Terminé' ? 'selected' : '' ?>>Terminé</option>
                    <option value="Annulé" <?= $filtre_statut === 'Annulé' ? 'selected' : '' ?>>Annulé</option>
                </select>

                <select name="priorite">
                    <option value="">-- Toutes les priorités --</option>
                    <option value="Basse" <?= $filtre_priorite === 'Basse' ? 'selected' : '' ?>>Basse</option>
                    <option value="Moyenne" <?= $filtre_priorite === 'Moyenne' ? 'selected' : '' ?>>Moyenne</option>
                    <option value="Haute" <?= $filtre_priorite === 'Haute' ? 'selected' : '' ?>>Haute</option>
                    <option value="Urgence Maximale" <?= $filtre_priorite === 'Urgence Maximale' ? 'selected' : '' ?>>Urgence Maximale</option>
                </select>

                <button type="submit" class="btn-filter">Filtrer</button>
                <a href="liste_travaux.php" class="btn-reset">Réinitialiser</a>
            </form>
        </div>

        <!-- Tableau des Travaux -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>N° Ticket</th>
                        <th>Type d'Intervention</th>
                        <th>Abonné / Client</th>
                        <th>Compteur</th>
                        <th>Priorité</th>
                        <th>Technicien Assigné</th>
                        <th>Statut</th>
                        <th>Date Signalement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($travaux) > 0): ?>
                        <?php foreach ($travaux as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['reference_ticket']) ?></strong></td>
                                <td><?= htmlspecialchars($row['type_travail']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['client_nom'] ?? 'Client non spécifié') ?></strong><br>
                                    <small style="color: #718096;"><?= htmlspecialchars($row['adresse_physique'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['num_compteur'] ?? 'N/A') ?></td>
                                <td>
                                    <?php 
                                        $prioClass = 'bg-moyenne';
                                        if ($row['priorite'] === 'Haute' || $row['priorite'] === 'Urgence Maximale') $prioClass = 'bg-haute';
                                        if ($row['priorite'] === 'Basse') $prioClass = 'bg-basse';
                                    ?>
                                    <span class="badge <?= $prioClass ?>"><?= htmlspecialchars($row['priorite']) ?></span>
                                </td>
                                <td>
                                    <?php if ($row['tech_nom']): ?>
                                        <?= htmlspecialchars($row['tech_nom'] . ' ' . $row['tech_prenom']) ?>
                                    <?php else: ?>
                                        <em style="color: #a0aec0;">Non assigné</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $statutClass = 'bg-signale';
                                        if ($row['statut'] === 'En cours') $statutClass = 'bg-encours';
                                        if ($row['statut'] === 'Terminé') $statutClass = 'bg-termine';
                                        if ($row['statut'] === 'Annulé') $statutClass = 'bg-annule';
                                    ?>
                                    <span class="badge <?= $statutClass ?>"><?= htmlspecialchars($row['statut']) ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['date_signalement'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #718096; padding: 25px;">
                                Aucun travail ne correspond à vos critères de recherche.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>