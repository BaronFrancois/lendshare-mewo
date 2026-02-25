<?php
echo "=== Test API Produits ===\n\n";

// Simuler l'appel API
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require __DIR__ . '/../api/api_produits.php';
$json_output = ob_get_clean();

echo "Réponse de l'API:\n";
echo "----------------\n\n";

$data = json_decode($json_output, true);

if (isset($data['success']) && $data['success']) {
    $count = count($data['produits']);
    echo "✓ L'API a retourné {$count} produit(s)\n\n";
    
    foreach ($data['produits'] as $index => $produit) {
        $vedette = $produit['est_vedette'] ? '⭐' : '';
        echo ($index + 1) . ". {$produit['nom_produit']} {$vedette}\n";
        echo "   Catégorie: {$produit['nom_categorie']}\n";
        echo "   Image: {$produit['image_url_produit']}\n\n";
    }
} else {
    echo "✗ Erreur: " . ($data['message'] ?? 'Erreur inconnue') . "\n";
}
?>
