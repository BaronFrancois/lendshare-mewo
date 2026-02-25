<?php
echo "=== Correction du mot de passe test@test.fr ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    $email = 'test@test.fr';
    $nouveau_mot_de_passe = 'test123';
    
    // Générer le hash
    $hash = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
    
    echo "Email: $email\n";
    echo "Nouveau mot de passe: $nouveau_mot_de_passe\n";
    echo "Hash généré: $hash\n\n";
    
    // Mettre à jour le mot de passe
    $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe_utilisateur = :hash WHERE email_utilisateur = :email");
    $result = $stmt->execute([
        'hash' => $hash,
        'email' => $email
    ]);
    
    if ($result) {
        echo "✓ Mot de passe mis à jour avec succès!\n\n";
        
        // Vérifier que ça fonctionne
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email_utilisateur = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if (password_verify($nouveau_mot_de_passe, $user['mot_de_passe_utilisateur'])) {
            echo "✓ Vérification réussie - Le mot de passe fonctionne maintenant!\n";
        } else {
            echo "✗ Erreur - La vérification échoue toujours\n";
        }
    } else {
        echo "✗ Erreur lors de la mise à jour\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
