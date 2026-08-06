<?php
session_start();
require_once 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

    if (!empty($email) && !empty($mot_de_passe)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Régénération de la session pour la sécurité
                session_regenerate_id(true);
                
                $_SESSION['utilisateur_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: dashboard.php');
                exit;
            } else {
                $erreur = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $erreur = "Erreur de base de données : " . $e->getMessage();
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
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login {
            max-width: 400px;
            width: 100%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background-color: transparent;
            border-bottom: none;
            text-align: center;
            padding-top: 30px;
        }
        .btn-primary {
            background-color: #2a5298;
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1e3c72;
        }
    </style>
</head>
<body>

<div class="card card-login bg-white p-4">
    <div class="card-header">
        <h3 class="fw-bold text-dark">Clinique Vétérinaire</h3>
        <p class="text-muted">Connectez-vous à votre espace</p>
    </div>
    
    <div class="card-body">
        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger text-center mb-3" role="alert">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label font-weight-bold">Adresse Email</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="nom@exemple.com">
            </div>

            <div class="mb-4">
                <label for="mot_de_passe" class="form-label font-weight-bold">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
</div>

</body>
</html>
