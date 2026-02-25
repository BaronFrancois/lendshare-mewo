<?php
echo "=== Recréation de la base de données ===\n\n";

require_once __DIR__ . '/config.php';

$db_path = CHEMIN_BASE_DONNEES;

if (file_exists($db_path)) {
    echo "Suppression de l'ancienne base de données...\n";
    unlink($db_path);
    echo "✓ Base de données supprimée\n\n";
} else {
    echo "ℹ Aucune base de données existante\n\n";
}

echo "Exécutez maintenant: php backend/config/create_database.php\n";
?>
