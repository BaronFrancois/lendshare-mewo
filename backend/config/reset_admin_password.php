<?php
echo "=== Réinitialisation du mot de passe administrateur ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    // Nouveau mot de passe : Admin123!
    $nouveauMotDePasse = 'Admin123!';
    $hashMotDePasse = password_hash($nouveauMotDePasse, PASSWORD_BCRYPT);
    
    // Mettre à jour le mot de passe de l'admin
    $stmt = $db->prepare("
        UPDATE utilisateurs 
        SET mot_de_passe_hash = :hash 
        WHERE email_utilisateur = 'admin@lendshare.fr'
    ");
    
    $stmt->execute(['hash' => $hashMotDePasse]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Mot de passe administrateur mis à jour avec succès !\n\n";
        echo "Identifiants de connexion :\n";
        echo "==========================\n";
        echo "Email        : admin@lendshare.fr\n";
        echo "Mot de passe : Admin123!\n";
        echo "==========================\n\n";
        echo "⚠️  Changez ce mot de passe après la première connexion pour plus de sécurité.\n";
    } else {
        echo "✗ Aucun utilisateur admin trouvé avec cet email.\n";
        
        // Créer un nouvel admin si il n'existe pas
        echo "\nCréation d'un nouveau compte administrateur...\n";
        $stmt = $db->prepare("
            INSERT INTO utilisateurs (
                nom_utilisateur, 
                prenom_utilisateur, 
                email_utilisateur, 
                mot_de_passe_hash, 
                role_utilisateur
            ) VALUES (
                'Admin',
                'Lend&Share',
                'admin@lendshare.fr',
                :hash,
                'administrateur'
            )
        ");
        
        $stmt->execute(['hash' => $hashMotDePasse]);
        
        echo "✓ Compte administrateur créé !\n\n";
        echo "Identifiants de connexion :\n";
        echo "==========================\n";
        echo "Email        : admin@lendshare.fr\n";
        echo "Mot de passe : Admin123!\n";
        echo "==========================\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
