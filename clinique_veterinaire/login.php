<?php
session_start();
require_once 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

    if (!empty($email) && !empty($mot_de_passe)) {
        try {
            // Recherche par email (sans tenir compte des majuscules/espaces)
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE LOWER(TRIM(email)) = LOWER(:email) LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Si l'email n'existe pas du tout en base
                $erreur = "Aucun utilisateur trouvé avec l'email : '" . htmlspecialchars($email) . "'";
            } else {
                // Comparaison directe ou hashée
                $pass_ok = ($mot_de_passe === $user['mot_de_passe']) || password_verify($mot_de_passe, $user['mot_de_passe']);
                
                if ($pass_ok) {
                    session_regenerate_id(true);
                    $_SESSION['utilisateur_id'] = $user['id'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    header('Location: dashboard.php');
                    exit;
                } else {
                    $erreur = "Mot de passe incorrect pour " . htmlspecialchars($email) . ". (Enregistré en BD : " . htmlspecialchars($user['mot_de_passe']) . ")";
                }
            }
        } catch (PDOException $e) {
            $erreur = "Erreur SQL : " . $e->getMessage();
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Clinique Vétérinaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1e3c72; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card-login { max-width: 420px; width: 100%; border-radius: 12px; }
    </style>
</head>
<body>
<div class="card card-login bg-white p-4">
    <h3 class="text-center font-weight-bold mb-3">Clinique Vétérinaire</h3>
    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger text-center mb-3" role="alert">
            <?= $erreur ?>
        </div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Adresse Email</label>
            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" class="form-control" name="mot_de_passe" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
</div>
</body>
</html>
