<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/middleware_authentification.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = obtenirConnexionBD();

    // GET - Lire les produits (accessible à tous)
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Récupérer un produit spécifique
            $stmt = $db->prepare("
                SELECT p.*, c.nom_categorie
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.id_produit = :id
            ");
            $stmt->execute(['id' => $_GET['id']]);
            $produit = $stmt->fetch();

            echo json_encode([
                'success' => true,
                'produit' => $produit
            ]);
        } elseif (isset($_GET['ids'])) {
            // Charger plusieurs produits par leurs IDs
            $ids = explode(',', $_GET['ids']);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $db->prepare("
                SELECT p.*, c.nom_categorie
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.id_produit IN ($placeholders)
            ");
            $stmt->execute($ids);
            $produits = $stmt->fetchAll();

            // Réorganiser les produits dans l'ordre des IDs fournis
            $produitsOrdonnes = [];
            foreach ($ids as $id) {
                foreach ($produits as $produit) {
                    if ($produit['id_produit'] == $id) {
                        $produitsOrdonnes[] = $produit;
                        break;
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'produits' => $produitsOrdonnes
            ]);
        } elseif (isset($_GET['vedettes'])) {
            // Récupérer les produits en vedette
            $stmt = $db->query("
                SELECT p.*, c.nom_categorie
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.est_vedette = 1
                ORDER BY p.date_ajout_produit DESC
            ");
            $produits = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'produits' => $produits
            ]);
        } elseif (isset($_GET['categorie'])) {
            // Récupérer les produits par catégorie
            $stmt = $db->prepare("
                SELECT p.*, c.nom_categorie
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                WHERE p.id_categorie = :categorie
                ORDER BY p.nom_produit
            ");
            $stmt->execute(['categorie' => $_GET['categorie']]);
            $produits = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'produits' => $produits
            ]);
        } else {
            // Récupérer tous les produits
            $stmt = $db->query("
                SELECT p.*, c.nom_categorie
                FROM produits p
                LEFT JOIN categories c ON p.id_categorie = c.id_categorie
                ORDER BY p.nom_produit
            ");
            $produits = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'produits' => $produits
            ]);
        }
    }

    // POST - Créer un nouveau produit (admin uniquement)
    elseif ($method === 'POST') {
        verifierAuthentificationAdmin();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des données
        if (!isset($data['nom_produit']) || empty(trim($data['nom_produit']))) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Le nom du produit est requis'
            ]);
            exit;
        }

        if (!isset($data['id_categorie']) || empty($data['id_categorie'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La catégorie est requise'
            ]);
            exit;
        }

        // Préparer les données
        $nom_produit = trim($data['nom_produit']);
        $description_produit = isset($data['description_produit']) ? trim($data['description_produit']) : '';
        $id_categorie = intval($data['id_categorie']);
        $image_url_produit = isset($data['image_url_produit']) ? trim($data['image_url_produit']) : null;
        $est_vedette = isset($data['est_vedette']) ? intval($data['est_vedette']) : 0;
        $quantite_totale = isset($data['quantite_totale']) ? intval($data['quantite_totale']) : 1;
        $quantite_disponible = isset($data['quantite_disponible']) ? intval($data['quantite_disponible']) : $quantite_totale;

        // Validation : la quantité disponible ne peut pas dépasser la quantité totale
        if ($quantite_disponible > $quantite_totale) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La quantité disponible ne peut pas dépasser la quantité totale'
            ]);
            exit;
        }

        // Validation : les quantités ne peuvent pas être négatives
        if ($quantite_disponible < 0 || $quantite_totale < 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Les quantités ne peuvent pas être négatives'
            ]);
            exit;
        }

        // Vérifier que la catégorie existe
        $stmt = $db->prepare("SELECT id_categorie FROM categories WHERE id_categorie = :id");
        $stmt->execute(['id' => $id_categorie]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La catégorie spécifiée n\'existe pas'
            ]);
            exit;
        }

        // Insérer le produit
        $stmt = $db->prepare("
            INSERT INTO produits (
                nom_produit,
                description_produit,
                id_categorie,
                image_url_produit,
                est_vedette,
                quantite_totale,
                quantite_disponible
            ) VALUES (
                :nom_produit,
                :description_produit,
                :id_categorie,
                :image_url_produit,
                :est_vedette,
                :quantite_totale,
                :quantite_disponible
            )
        ");

        $stmt->execute([
            'nom_produit' => $nom_produit,
            'description_produit' => $description_produit,
            'id_categorie' => $id_categorie,
            'image_url_produit' => $image_url_produit,
            'est_vedette' => $est_vedette,
            'quantite_totale' => $quantite_totale,
            'quantite_disponible' => $quantite_disponible
        ]);

        $id_produit = $db->lastInsertId();

        // Récupérer le produit créé avec les informations de catégorie
        $stmt = $db->prepare("
            SELECT p.*, c.nom_categorie
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id_categorie
            WHERE p.id_produit = :id
        ");
        $stmt->execute(['id' => $id_produit]);
        $produit = $stmt->fetch();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Produit créé avec succès',
            'produit' => $produit
        ]);
    }

    // PUT - Modifier un produit existant (admin uniquement)
    elseif ($method === 'PUT') {
        verifierAuthentificationAdmin();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des données
        if (!isset($data['id_produit']) || empty($data['id_produit'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'L\'ID du produit est requis'
            ]);
            exit;
        }

        $id_produit = intval($data['id_produit']);

        // Vérifier que le produit existe
        $stmt = $db->prepare("SELECT * FROM produits WHERE id_produit = :id");
        $stmt->execute(['id' => $id_produit]);
        $produitExistant = $stmt->fetch();

        if (!$produitExistant) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Produit non trouvé'
            ]);
            exit;
        }

        // Préparer les données à mettre à jour
        $nom_produit = isset($data['nom_produit']) ? trim($data['nom_produit']) : $produitExistant['nom_produit'];
        $description_produit = isset($data['description_produit']) ? trim($data['description_produit']) : $produitExistant['description_produit'];
        $id_categorie = isset($data['id_categorie']) ? intval($data['id_categorie']) : $produitExistant['id_categorie'];
        $image_url_produit = isset($data['image_url_produit']) ? trim($data['image_url_produit']) : $produitExistant['image_url_produit'];
        $est_vedette = isset($data['est_vedette']) ? intval($data['est_vedette']) : $produitExistant['est_vedette'];
        $quantite_totale = isset($data['quantite_totale']) ? intval($data['quantite_totale']) : $produitExistant['quantite_totale'];
        $quantite_disponible = isset($data['quantite_disponible']) ? intval($data['quantite_disponible']) : $produitExistant['quantite_disponible'];

        // Si la quantité totale est réduite, ajuster automatiquement la quantité disponible
        if ($quantite_totale < $produitExistant['quantite_totale'] && $quantite_disponible > $quantite_totale) {
            $quantite_disponible = $quantite_totale;
        }

        // Validation : la quantité disponible ne peut pas dépasser la quantité totale
        if ($quantite_disponible > $quantite_totale) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La quantité disponible ne peut pas dépasser la quantité totale'
            ]);
            exit;
        }

        // Validation : les quantités ne peuvent pas être négatives
        if ($quantite_disponible < 0 || $quantite_totale < 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Les quantités ne peuvent pas être négatives'
            ]);
            exit;
        }

        // Si la catégorie a changé, vérifier qu'elle existe
        if ($id_categorie !== $produitExistant['id_categorie']) {
            $stmt = $db->prepare("SELECT id_categorie FROM categories WHERE id_categorie = :id");
            $stmt->execute(['id' => $id_categorie]);
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'La catégorie spécifiée n\'existe pas'
                ]);
                exit;
            }
        }

        // Mettre à jour le produit
        $stmt = $db->prepare("
            UPDATE produits SET
                nom_produit = :nom_produit,
                description_produit = :description_produit,
                id_categorie = :id_categorie,
                image_url_produit = :image_url_produit,
                est_vedette = :est_vedette,
                quantite_totale = :quantite_totale,
                quantite_disponible = :quantite_disponible
            WHERE id_produit = :id_produit
        ");

        $stmt->execute([
            'nom_produit' => $nom_produit,
            'description_produit' => $description_produit,
            'id_categorie' => $id_categorie,
            'image_url_produit' => $image_url_produit,
            'est_vedette' => $est_vedette,
            'quantite_totale' => $quantite_totale,
            'quantite_disponible' => $quantite_disponible,
            'id_produit' => $id_produit
        ]);

        // Récupérer le produit mis à jour
        $stmt = $db->prepare("
            SELECT p.*, c.nom_categorie
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id_categorie
            WHERE p.id_produit = :id
        ");
        $stmt->execute(['id' => $id_produit]);
        $produit = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Produit mis à jour avec succès',
            'produit' => $produit
        ]);
    }

    // DELETE - Supprimer un produit (admin uniquement)
    elseif ($method === 'DELETE') {
        verifierAuthentificationAdmin();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation des données
        if (!isset($data['id_produit']) || empty($data['id_produit'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'L\'ID du produit est requis'
            ]);
            exit;
        }

        $id_produit = intval($data['id_produit']);

        // Vérifier que le produit existe
        $stmt = $db->prepare("SELECT * FROM produits WHERE id_produit = :id");
        $stmt->execute(['id' => $id_produit]);
        $produit = $stmt->fetch();

        if (!$produit) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Produit non trouvé'
            ]);
            exit;
        }

        // Vérifier s'il y a des réservations actives pour ce produit
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM reservations
            WHERE id_produit = :id_produit
            AND statut_reservation IN ('en_attente', 'accepte')
        ");
        $stmt->execute(['id_produit' => $id_produit]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de supprimer ce produit car il a des réservations actives'
            ]);
            exit;
        }

        // Supprimer l'image si elle existe
        if (!empty($produit['image_url_produit'])) {
            $imagePath = __DIR__ . '/../../' . $produit['image_url_produit'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Supprimer le produit
        $stmt = $db->prepare("DELETE FROM produits WHERE id_produit = :id");
        $stmt->execute(['id' => $id_produit]);

        echo json_encode([
            'success' => true,
            'message' => 'Produit supprimé avec succès'
        ]);
    }

    else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>
