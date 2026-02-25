<?php
echo "=== Test de vérification des mots de passe ===\n\n";

require_once __DIR__ . '/database.php';

// Mots de passe en clair depuis accounts.MD
$test_passwords = [
    'admin@lendshare.fr' => 'Admin123!',
    'test@test.fr' => 'test123'
];

try {
    $db = obtenirConnexionBD();
    
    foreach ($test_passwords as $email => $password) {
        echo "Test pour: $email\n";
        echo "Mot de passe testé: $password\n";
        
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email_utilisateur = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "Utilisateur trouvé dans la base\n";
            echo "Hash stocké: " . substr($user['mot_de_passe_utilisateur'], 0, 60) . "\n";
            
            // Test password_verify
            if (password_verify($password, $user['mot_de_passe_utilisateur'])) {
                echo "✓ PASSWORD_VERIFY RÉUSSIT - Le mot de passe correspond!\n";
            } else {
                echo "✗ PASSWORD_VERIFY ÉCHOUE - Le mot de passe ne correspond pas!\n";
                
                // Tenter de créer le bon hash
                $correct_hash = password_hash($password, PASSWORD_DEFAULT);
                echo "Hash correct devrait être du type: " . substr($correct_hash, 0, 60) . "\n";
            }
        } else {
            echo "✗ Utilisateur non trouvé dans la base!\n";
        }
        echo "\n" . str_repeat("-", 50) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
