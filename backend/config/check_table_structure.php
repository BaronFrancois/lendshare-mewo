<?php
echo "=== Structure de la table utilisateurs ===\n\n";

require_once __DIR__ . '/database.php';

try {
    $db = obtenirConnexionBD();
    
    $columns = $db->query("PRAGMA table_info(utilisateurs)")->fetchAll();
    
    echo "Colonnes de la table 'utilisateurs':\n";
    echo "------------------------------------\n";
    foreach ($columns as $col) {
        echo "  - {$col['name']} ({$col['type']})";
        if ($col['dflt_value']) {
            echo " DEFAULT {$col['dflt_value']}";
        }
        if ($col['notnull']) {
            echo " NOT NULL";
        }
        echo "\n";
    }
    
    echo "\n";
    
    // Afficher les utilisateurs avec leurs colonnes
    $users = $db->query("SELECT * FROM utilisateurs LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($users) {
        echo "Colonnes présentes dans les données:\n";
        echo "------------------------------------\n";
        foreach (array_keys($users) as $key) {
            echo "  - $key\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
