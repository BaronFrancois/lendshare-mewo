<?php
echo "=== Test d'indisponibilité des produits ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    // Mettre les produits 1, 2 et 3 en quantité 0 (indisponibles)
    echo "Mise à jour des quantités...\n";
    $db->exec("UPDATE produits SET quantite_disponible = 0 WHERE id_produit IN (1, 2, 3)");
    echo "✓ Produits 1, 2 et 3 mis en quantité 0 (indisponibles)\n\n";
    
    // Mettre les autres produits en quantité 1 (disponibles)
    $db->exec("UPDATE produits SET quantite_disponible = 1 WHERE id_produit NOT IN (1, 2, 3)");
    echo "✓ Autres produits mis en quantité 1 (disponibles)\n\n";
    
    // Afficher l'état actuel
    echo "État actuel des produits:\n";
    echo "-------------------------\n";
    $produits = $db->query("SELECT id_produit, nom_produit, quantite_disponible, quantite_totale FROM produits ORDER BY id_produit")->fetchAll();
    
    foreach ($produits as $prod) {
        $statut = $prod['quantite_disponible'] > 0 ? '✓ Disponible' : '✗ Indisponible';
        echo sprintf(
            "[%d] %s - %s (Dispo: %d / Total: %d)\n",
            $prod['id_produit'],
            $prod['nom_produit'],
            $statut,
            $prod['quantite_disponible'],
            $prod['quantite_totale']
        );
    }
    
    echo "\n✓✓✓ Test terminé ! ✓✓✓\n";
    echo "Rechargez votre page pour voir les produits indisponibles en rouge.\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
