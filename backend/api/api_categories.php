<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/logger.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = obtenirConnexionBD();
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                logInfo("Récupération d'une catégorie", ['id' => $_GET['id']]);
                $stmt = $db->prepare("SELECT * FROM categories WHERE id_categorie = :id");
                $stmt->execute(['id' => $_GET['id']]);
                $categorie = $stmt->fetch();
                echo json_encode(['succes' => true, 'donnees' => $categorie]);
            } else {
                logInfo("Récupération de toutes les catégories");
                $stmt = $db->query("SELECT * FROM categories ORDER BY nom_categorie");
                $categories = $stmt->fetchAll();
                logInfo("Catégories récupérées", ['nombre' => count($categories)]);
                echo json_encode(['succes' => true, 'donnees' => $categories]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("
                INSERT INTO categories (nom_categorie, description_categorie, image_url_categorie)
                VALUES (:nom, :description, :image)
            ");
            $stmt->execute([
                'nom' => $data['nom_categorie'],
                'description' => $data['description_categorie'] ?? '',
                'image' => $data['image_url_categorie'] ?? ''
            ]);

            echo json_encode(['succes' => true, 'message' => 'Catégorie créée', 'id' => $db->lastInsertId()]);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("
                UPDATE categories
                SET nom_categorie = :nom, description_categorie = :description, image_url_categorie = :image
                WHERE id_categorie = :id
            ");
            $result = $stmt->execute([
                'id' => $data['id_categorie'],
                'nom' => $data['nom_categorie'],
                'description' => $data['description_categorie'] ?? '',
                'image' => $data['image_url_categorie'] ?? ''
            ]);

            echo json_encode(['succes' => $result, 'message' => 'Catégorie modifiée']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("DELETE FROM categories WHERE id_categorie = :id");
            $result = $stmt->execute(['id' => $data['id_categorie']]);

            echo json_encode(['succes' => $result, 'message' => 'Catégorie supprimée']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    logException($e, "Erreur dans l'API des catégories");
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
