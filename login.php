<?php
require_once 'db.php';

// Votre email Administrateur unique
define('ADMIN_EMAIL_EXCLUSIF', 'nkurunzizamuganza94@gmail.com');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verification du mot de passe (compatible password_hash ou texte brut)
        $password_valide = false;
        if ($user) {
            if (password_verify($password, $user['mot_de_passe'])) {
                $password_valide = true;
            } elseif ($password === $user['mot_de_passe']) {
                $password_valide = true;
            }
        }

        if ($user && $password_valide) {
            // Définition synchrone pour éviter tout conflit de nom dans le projet
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['utilisateur_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_email'] = $user['email'];

            // SECURITE STRICTE DU ROLE
            if (mb_strtolower($user['email']) === mb_strtolower(ADMIN_EMAIL_EXCLUSIF)) {
                $_SESSION['user_role'] = 'admin';

                // Met à jour le rôle en BDD si pas fait
                $pdo->prepare("UPDATE utilisateurs SET role = 'admin' WHERE id = ?")->execute([$user['id']]);
            } else {
                $_SESSION['user_role'] = !empty($user['role']) ? $user['role'] : 'veterinaire';
            }

            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
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
    <title>Connexion - Clinique Vétérinaire</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 35px 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .login-title {
            color: #0d6efd;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 25px;
        }
        .form-control {
            background-color: #eef4ff;
            border: 1px solid #d0e1fd;
            padding: 12px 15px;
            font-size: 0.95rem;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-login {
            background-color: #0d6efd;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            font-size: 1rem;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
        }
        .input-group-text {
            background-color: #eef4ff;
            border: 1px solid #d0e1fd;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <h2 class="login-title">Connexion</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size: 0.9rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="text-start mb-3">
            <label class="form-label text-secondary small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" value="nkurunzizamuganza94@gmail.com" required>
        </div>

        <div class="text-start mb-4">
            <label class="form-label text-secondary small fw-bold">Mot de passe</label>
            <div class="input-group">
                <input type="password" name="password" id="passwordInput" class="form-control" value="1234" required>
                <span class="input-group-text" id="togglePassword">
                    <i class="fa-solid fa-eye-slash text-secondary" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login w-100 mb-3">Se connecter</button>

        <div>
            <a href="register.php" class="text-decoration-none text-primary" style="font-size: 0.95rem;">
                Pas encore de compte ? S'inscrire
            </a>
        </div>
    </form>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        } else {
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        }
    });
</script>

</body>
</html>