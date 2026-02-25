<?php
/**
 * API pour mettre à jour les quantités d'un produit
 * Accessible uniquement aux administrateurs
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
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

// Vérifier que c'est une requête PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Valider les données
if (!isset($data['id_produit']) || !isset($data['quantite_disponible']) || !isset($data['quantite_totale'])) {
    logWarning("Tentative de mise à jour quantités avec données manquantes", ['data' => $data]);
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Données manquantes (id_produit, quantite_disponible, quantite_totale)']);
    exit;
}

$id_produit = intval($data['id_produit']);
$quantite_disponible = intval($data['quantite_disponible']);
$quantite_totale = intval($data['quantite_totale']);

// Valider que les quantités sont positives
if ($quantite_disponible < 0 || $quantite_totale < 0) {
    logWarning("Quantités négatives", ['id_produit' => $id_produit, 'dispo' => $quantite_disponible, 'total' => $quantite_totale]);
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Les quantités doivent être positives']);
    exit;
}

// Valider que disponible <= total
if ($quantite_disponible > $quantite_totale) {
    logWarning("Quantité disponible > total", ['id_produit' => $id_produit, 'dispo' => $quantite_disponible, 'total' => $quantite_totale]);
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Quantité disponible ne peut pas être supérieure à la quantité totale']);
    exit;
}

try {
    $db = obtenirConnexionBD();

    // Vérifier que le produit existe
    $stmt = $db->prepare("SELECT id_produit FROM produits WHERE id_produit = :id");
    $stmt->execute(['id' => $id_produit]);
    if (!$stmt->fetch()) {
        logWarning("Tentative de modification d'un produit inexistant", ['id_produit' => $id_produit]);
        http_response_code(404);
        echo json_encode(['succes' => false, 'message' => 'Produit non trouvé']);
        exit;
    }

    // Mettre à jour les quantités
    $stmt = $db->prepare("
        UPDATE produits
        SET quantite_disponible = :dispo, quantite_totale = :total
        WHERE id_produit = :id
    ");

    $result = $stmt->execute([
        'id' => $id_produit,
        'dispo' => $quantite_disponible,
        'total' => $quantite_totale
    ]);

    if ($result) {
        logInfo("Quantités mises à jour", [
            'id_produit' => $id_produit,
            'utilisateur_id' => $_SESSION['utilisateur_connecte'],
            'quantite_disponible' => $quantite_disponible,
            'quantite_totale' => $quantite_totale
        ]);

        http_response_code(200);
        echo json_encode([
            'succes' => true,
            'message' => 'Quantités mises à jour avec succès',
            'donnees' => [
                'id_produit' => $id_produit,
                'quantite_disponible' => $quantite_disponible,
                'quantite_totale' => $quantite_totale
            ]
        ]);
    } else {
        logError("Erreur lors de la mise à jour des quantités", ['id_produit' => $id_produit]);
        http_response_code(500);
        echo json_encode(['succes' => false, 'message' => 'Erreur lors de la mise à jour']);
    }

} catch (Exception $e) {
    logException($e, "Erreur dans api_update_quantites");
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
