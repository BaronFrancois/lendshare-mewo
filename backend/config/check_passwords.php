<?php
echo "=== Vérification des mots de passe ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    $users = $db->query("SELECT id_utilisateur, email_utilisateur, mot_de_passe_utilisateur FROM utilisateurs")->fetchAll();
    
    foreach ($users as $user) {
        echo "Utilisateur: {$user['email_utilisateur']}\n";
        echo "Hash actuel: " . substr($user['mot_de_passe_utilisateur'], 0, 50) . "...\n";
        
        // Vérifier si c'est un hash bcrypt valide
        if (preg_match('/^\$2[ayb]\$.{56}$/', $user['mot_de_passe_utilisateur'])) {
            echo "  ✓ Hash bcrypt valide\n";
        } else {
            echo "  ✗ Pas un hash bcrypt valide - mot de passe probablement en clair!\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
