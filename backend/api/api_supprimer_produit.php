<?php
/**
 * API pour supprimer un produit
 * Accessible uniquement aux administrateurs
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/logger.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['utilisateur_connecte']) || $_SESSION['role_utilisateur'] !== 'administrateur') {
    http_response_code(401);
    echo json_encode(['succes' => false, 'message' => 'Non authentifié ou permissions insuffisantes']);
    exit;
}

// Vérifier que c'est une requête DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Valider les données
if (!isset($data['id_produit'])) {
    logWarning("Tentative de suppression sans ID produit");
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'ID produit manquant']);
    exit;
}

$id_produit = intval($data['id_produit']);

try {
    $db = obtenirConnexionBD();

    // Vérifier que le produit existe
    $stmt = $db->prepare("SELECT nom_produit FROM produits WHERE id_produit = :id");
    $stmt->execute(['id' => $id_produit]);
    $produit = $stmt->fetch();

    if (!$produit) {
        logWarning("Tentative de suppression d'un produit inexistant", ['id_produit' => $id_produit]);
        http_response_code(404);
        echo json_encode(['succes' => false, 'message' => 'Produit non trouvé']);
        exit;
    }

    // Supprimer le produit
    $stmt = $db->prepare("DELETE FROM produits WHERE id_produit = :id");
    $result = $stmt->execute(['id' => $id_produit]);

    if ($result) {
        logInfo("Produit supprimé", [
            'id_produit' => $id_produit,
            'nom_produit' => $produit['nom_produit'],
            'utilisateur_id' => $_SESSION['utilisateur_connecte']
        ]);

        http_response_code(200);
        echo json_encode([
            'succes' => true,
            'message' => 'Produit supprimé avec succès',
            'donnees' => [
                'id_produit' => $id_produit,
                'nom_produit' => $produit['nom_produit']
            ]
        ]);
    } else {
        logError("Erreur lors de la suppression du produit", ['id_produit' => $id_produit]);
        http_response_code(500);
        echo json_encode(['succes' => false, 'message' => 'Erreur lors de la suppression']);
    }

} catch (Exception $e) {
    logException($e, "Erreur dans api_supprimer_produit");
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
