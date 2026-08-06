<?php
// Démarrage obligatoire de la session tout en haut du fichier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

    if (!empty($email) && !empty($mot_de_passe)) {
        try {
            // Recherche de l'utilisateur sans sensibilité aux majuscules/espaces
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE LOWER(TRIM(email)) = LOWER(:email) LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $erreur = "Aucun compte trouvé avec l'adresse : " . htmlspecialchars($email);
            } else {
                // Accepte à la fois '1234' direct ou un mot de passe haché
                $pass_ok = ($mot_de_passe === $user['mot_de_passe']) || password_verify($mot_de_passe, $user['mot_de_passe']);
                
                if ($pass_ok) {
                    session_regenerate_id(true);
                    
                    $_SESSION['utilisateur_id'] = $user['id'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Double redirection PHP + JavaScript pour assurer le passage vers le dashboard
                    header('Location: dashboard.php');
                    echo "<script>window.location.href='dashboard.php';</script>";
                    exit;
                } else {
                    $erreur = "Mot de passe incorrect.";
                }
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
            margin: 0;
        }
        .card-login {
            max-width: 420px;
            width: 90%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
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
    <div class="card-body">
        <h3 class="text-center fw-bold text-dark mb-1">Clinique Vétérinaire</h3>
        <p class="text-center text-muted mb-4">Connectez-vous à votre espace</p>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger text-center mb-3" role="alert">
                <?= $erreur ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Adresse Email</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="nom@exemple.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-4">
                <label for="mot_de_passe" class="form-label fw-semibold">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>
</div>

</body>
</html>
