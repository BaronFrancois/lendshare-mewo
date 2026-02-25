<?php
/**
 * API pour ajouter un nouveau produit
 * Accessible uniquement aux administrateurs
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/logger.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['utilisateur_connecte']) || $_SESSION['role_utilisateur'] !== 'administrateur') {
    http_response_code(401);
    echo json_encode(['succes' => false, 'message' => 'Non authentifié ou permissions insuffisantes']);
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données du formulaire
$nom_produit = isset($_POST['nom_produit']) ? trim($_POST['nom_produit']) : '';
$description_produit = isset($_POST['description_produit']) ? trim($_POST['description_produit']) : '';
$quantite_disponible = isset($_POST['quantite_disponible']) ? intval($_POST['quantite_disponible']) : 0;
$quantite_totale = isset($_POST['quantite_totale']) ? intval($_POST['quantite_totale']) : 0;
$id_categorie = isset($_POST['id_categorie']) ? intval($_POST['id_categorie']) : null;
$est_vedette = isset($_POST['est_vedette']) ? intval($_POST['est_vedette']) : 0;
$image_url_produit = isset($_POST['image_url_produit']) ? $_POST['image_url_produit'] : '';

// Valider les données obligatoires
if (empty($nom_produit)) {
    logWarning("Tentative d'ajout de produit sans nom", ['admin_id' => $_SESSION['utilisateur_connecte']]);
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Le nom du produit est obligatoire']);
    exit;
}

if ($quantite_disponible < 0 || $quantite_totale < 0) {
    logWarning("Tentative d'ajout de produit avec quantités négatives", ['nom' => $nom_produit]);
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Les quantités doivent être positives']);
    exit;
}

if ($quantite_disponible > $quantite_totale) {
    logWarning("Quantité disponible > total", ['nom' => $nom_produit]);
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Quantité disponible ne peut pas être supérieure à la quantité totale']);
    exit;
}

try {
    $db = obtenirConnexionBD();

    // Vérifier que la catégorie existe si fournie
    if ($id_categorie) {
        $stmt = $db->prepare("SELECT id_categorie FROM categories WHERE id_categorie = :id");
        $stmt->execute(['id' => $id_categorie]);
        if (!$stmt->fetch()) {
            logWarning("Tentative d'ajout avec catégorie inexistante", ['id_categorie' => $id_categorie]);
            http_response_code(404);
            echo json_encode(['succes' => false, 'message' => 'Catégorie non trouvée']);
            exit;
        }
    }

    // Insérer le produit
    $stmt = $db->prepare("
        INSERT INTO produits (
            nom_produit,
            description_produit,
            quantite_disponible,
            quantite_totale,
            id_categorie,
            image_url_produit,
            est_vedette,
            date_ajout_produit
        ) VALUES (
            :nom,
            :description,
            :dispo,
            :total,
            :categorie,
            :image,
            :vedette,
            CURRENT_TIMESTAMP
        )
    ");

    $result = $stmt->execute([
        'nom' => $nom_produit,
        'description' => $description_produit,
        'dispo' => $quantite_disponible,
        'total' => $quantite_totale,
        'categorie' => $id_categorie,
        'image' => $image_url_produit,
        'vedette' => $est_vedette
    ]);

    if ($result) {
        $id_produit = $db->lastInsertId();

        logInfo("Produit ajouté", [
            'id_produit' => $id_produit,
            'nom_produit' => $nom_produit,
            'utilisateur_id' => $_SESSION['utilisateur_connecte']
        ]);

        http_response_code(201);
        echo json_encode([
            'succes' => true,
            'message' => 'Produit créé avec succès',
            'donnees' => [
                'id_produit' => $id_produit,
                'nom_produit' => $nom_produit
            ]
        ]);
    } else {
        logError("Erreur lors de l'ajout du produit", ['nom_produit' => $nom_produit]);
        http_response_code(500);
        echo json_encode(['succes' => false, 'message' => 'Erreur lors de la création du produit']);
    }

} catch (Exception $e) {
    logException($e, "Erreur dans api_ajouter_produit");
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
