<?php
session_start();
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? 'veterinaire';

    // Sécurité : Vérifier que le rôle choisi est uniquement 'veterinaire' ou 'secretaire'
    if (!in_array($role, ['veterinaire', 'secretaire'])) {
        $role = 'veterinaire';
    }

    if (!empty($nom) && !empty($email) && !empty($password)) {
        // Empêcher d'utiliser l'email de l'administrateur principal
        if (mb_strtolower($email) === 'nkurunzizamuganza94@gmail.com') {
            $error = "Cet email est réservé à l'administrateur du système.";
        } else {
            // Vérifier si l'email existe déjà
            $stmtCheck = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmtCheck->execute([$email]);
            
            if ($stmtCheck->rowCount() > 0) {
                $error = "Cet email est déjà utilisé par un autre compte.";
            } else {
                // Hachage du mot de passe
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$nom, $email, $password_hash, $role])) {
                    $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } else {
                    $error = "Une erreur est survenue lors de l'inscription.";
                }
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Clinique Vétérinaire</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        .register-card {
            width: 100%;
            max-width: 440px;
            padding: 35px 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .register-title {
            color: #0d6efd;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 25px;
        }
        .form-control, .form-select {
            background-color: #eef4ff;
            border: 1px solid #d0e1fd;
            padding: 12px 15px;
            font-size: 0.95rem;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-register {
            background-color: #0d6efd;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            font-size: 1rem;
        }
        .btn-register:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>

<div class="register-card text-center">
    <h2 class="register-title">Créer un compte</h2>

    <?php if ($message): ?>
        <div class="alert alert-success py-2 mb-3" style="font-size: 0.9rem;">
            <?= htmlspecialchars($message) ?>
            <div class="mt-2">
                <a href="login.php" class="btn btn-sm btn-outline-success fw-bold">Se connecter</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size: 0.9rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="text-start mb-3">
            <label class="form-label text-secondary small fw-bold">Nom complet</label>
            <input type="text" name="nom" class="form-control" placeholder="Ex: Jean Dupont" required>
        </div>

        <div class="text-start mb-3">
            <label class="form-label text-secondary small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" placeholder="nom@exemple.com" required>
        </div>

        <div class="text-start mb-3">
            <label class="form-label text-secondary small fw-bold">S'inscrire en tant que :</label>
            <select name="role" class="form-select" required>
                <option value="veterinaire">Vétérinaire</option>
                <option value="secretaire">Secrétaire</option>
            </select>
        </div>

        <div class="text-start mb-4">
            <label class="form-label text-secondary small fw-bold">Mot de passe</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary btn-register w-100 mb-3">S'inscrire</button>

        <div>
            <a href="login.php" class="text-decoration-none text-primary" style="font-size: 0.95rem;">
                Vous avez déjà un compte ? Se connecter
            </a>
        </div>
    </form>
</div>

</body>
</html>