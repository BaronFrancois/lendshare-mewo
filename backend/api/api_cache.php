<?php
/**
 * API de cache pour améliorer les performances
 * Génère un fichier JSON des produits qui est mis à jour automatiquement
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$cacheFile = __DIR__ . '/../../cache/produits_cache.json';
$cacheDir = dirname($cacheFile);

// Créer le dossier cache s'il n'existe pas
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

try {
    $db = obtenirConnexionBD();
    
    // Récupérer tous les produits avec leurs catégories
    $stmt = $db->query("
        SELECT p.*, c.nom_categorie 
        FROM produits p 
        LEFT JOIN categories c ON p.id_categorie = c.id_categorie 
        ORDER BY p.date_ajout_produit DESC
    ");
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Créer le cache
    $cache = [
        'timestamp' => date('Y-m-d H:i:s'),
        'count' => count($produits),
        'produits' => $produits
    ];
    
    // Sauvegarder dans le fichier JSON
    file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo json_encode([
        'success' => true,
        'message' => 'Cache mis à jour',
        'timestamp' => $cache['timestamp'],
        'count' => $cache['count']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour du cache: ' . $e->getMessage()
    ]);
}
?>
