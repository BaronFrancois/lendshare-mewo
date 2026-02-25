<?php
echo "=== Migration de la base de données ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    echo "Vérification de la structure de la table produits...\n";
    $columns_produits = $db->query("PRAGMA table_info(produits)")->fetchAll();
    $column_names = array_column($columns_produits, 'name');
    
    if (!in_array('quantite_disponible', $column_names)) {
        echo "  Ajout de la colonne 'quantite_disponible'...\n";
        $db->exec("ALTER TABLE produits ADD COLUMN quantite_disponible INTEGER DEFAULT 1");
        echo "  ✓ Colonne ajoutée\n";
    } else {
        echo "  ✓ Colonne 'quantite_disponible' déjà présente\n";
    }
    
    if (!in_array('quantite_totale', $column_names)) {
        echo "  Ajout de la colonne 'quantite_totale'...\n";
        $db->exec("ALTER TABLE produits ADD COLUMN quantite_totale INTEGER DEFAULT 1");
        echo "  ✓ Colonne ajoutée\n";
    } else {
        echo "  ✓ Colonne 'quantite_totale' déjà présente\n";
    }
    
    echo "\nVérification de la structure de la table reservations...\n";
    $columns_reservations = $db->query("PRAGMA table_info(reservations)")->fetchAll();
    $column_names_res = array_column($columns_reservations, 'name');
    
    if (!in_array('statut_reservation', $column_names_res)) {
        echo "  Ajout de la colonne 'statut_reservation'...\n";
        $db->exec("ALTER TABLE reservations ADD COLUMN statut_reservation TEXT DEFAULT 'en_attente'");
        echo "  ✓ Colonne ajoutée\n";
    } else {
        echo "  ✓ Colonne 'statut_reservation' déjà présente\n";
    }
    
    if (!in_array('date_modification_statut', $column_names_res)) {
        echo "  Ajout de la colonne 'date_modification_statut'...\n";
        $db->exec("ALTER TABLE reservations ADD COLUMN date_modification_statut DATETIME");
        echo "  ✓ Colonne ajoutée\n";
    } else {
        echo "  ✓ Colonne 'date_modification_statut' déjà présente\n";
    }
    
    echo "\nInitialisation des quantités pour les produits existants...\n";
    $db->exec("UPDATE produits SET quantite_disponible = 1 WHERE quantite_disponible IS NULL OR quantite_disponible = 0");
    $db->exec("UPDATE produits SET quantite_totale = 1 WHERE quantite_totale IS NULL OR quantite_totale = 0");
    echo "✓ Quantités initialisées\n";
    
    echo "\n✓✓✓ Migration terminée avec succès ! ✓✓✓\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
