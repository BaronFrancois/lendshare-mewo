<?php
echo "=== Vérification de la base de données ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    echo "1. UTILISATEURS:\n";
    echo "----------------\n";
    $users = $db->query("SELECT id_utilisateur, nom_utilisateur, prenom_utilisateur, email_utilisateur, role_utilisateur FROM utilisateurs")->fetchAll();
    foreach ($users as $user) {
        echo "  - [{$user['id_utilisateur']}] {$user['prenom_utilisateur']} {$user['nom_utilisateur']} ({$user['email_utilisateur']}) - {$user['role_utilisateur']}\n";
    }
    echo "\nTotal: " . count($users) . " utilisateur(s)\n\n";
    
    echo "2. CATÉGORIES:\n";
    echo "----------------\n";
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
    foreach ($categories as $cat) {
        echo "  - [{$cat['id_categorie']}] {$cat['nom_categorie']}\n";
    }
    echo "\nTotal: " . count($categories) . " catégorie(s)\n\n";
    
    echo "3. PRODUITS:\n";
    echo "----------------\n";
    $produits = $db->query("SELECT p.*, c.nom_categorie FROM produits p LEFT JOIN categories c ON p.id_categorie = c.id_categorie")->fetchAll();
    foreach ($produits as $prod) {
        $vedette = $prod['est_vedette'] ? '⭐' : '';
        echo "  - [{$prod['id_produit']}] {$prod['nom_produit']} {$vedette}\n";
        echo "    Catégorie: {$prod['nom_categorie']}\n";
        echo "    Quantité: {$prod['quantite_disponible']}/{$prod['quantite_totale']}\n";
        echo "    Image: {$prod['image_url_produit']}\n";
    }
    echo "\nTotal: " . count($produits) . " produit(s)\n\n";
    
    echo "4. RÉSERVATIONS:\n";
    echo "----------------\n";
    $reservations = $db->query("SELECT r.*, p.nom_produit, u.email_utilisateur FROM reservations r LEFT JOIN produits p ON r.id_produit = p.id_produit LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur")->fetchAll();
    if (count($reservations) > 0) {
        foreach ($reservations as $res) {
            echo "  - [{$res['id_reservation']}] {$res['nom_produit']} par {$res['email_utilisateur']}\n";
            echo "    Statut: {$res['statut_reservation']}\n";
        }
    } else {
        echo "  Aucune réservation\n";
    }
    echo "\nTotal: " . count($reservations) . " réservation(s)\n\n";
    
    echo "✓ Vérification terminée\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
