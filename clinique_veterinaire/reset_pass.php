<?php
require_once 'db.php';

$new_password = '1234';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :hash WHERE email = :email");
    $stmt->execute([
        'hash' => $hash,
        'email' => 'nkurunzizamuganza94@gmail.com'
    ]);
    echo "Le mot de passe de nkurunzizamuganza94@gmail.com a été réinitialisé à '1234' avec succès !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
