<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = obtenirConnexionBD();
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id_utilisateur'])) {
                $stmt = $db->prepare("
                    SELECT r.*, p.nom_produit, p.image_url_produit,
                           u.nom_utilisateur, u.prenom_utilisateur, u.email_utilisateur
                    FROM reservations r
                    LEFT JOIN produits p ON r.id_produit = p.id_produit
                    LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
                    WHERE r.id_utilisateur = :id_user
                    ORDER BY r.date_reservation DESC
                ");
                $stmt->execute(['id_user' => $_GET['id_utilisateur']]);
            } else {
                $stmt = $db->query("
                    SELECT r.*, p.nom_produit, p.image_url_produit,
                           u.nom_utilisateur, u.prenom_utilisateur, u.email_utilisateur
                    FROM reservations r
                    LEFT JOIN produits p ON r.id_produit = p.id_produit
                    LEFT JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
                    ORDER BY r.date_reservation DESC
                ");
            }
            
            $reservations = $stmt->fetchAll();
            echo json_encode(['success' => true, 'reservations' => $reservations]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("
                INSERT INTO reservations (id_utilisateur, id_produit, statut_reservation) 
                VALUES (:id_user, :id_product, 'en_attente')
            ");
            $stmt->execute([
                'id_user' => $data['id_utilisateur'],
                'id_product' => $data['id_produit']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Réservation créée']);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt_get = $db->prepare("SELECT id_produit, statut_reservation FROM reservations WHERE id_reservation = :id");
            $stmt_get->execute(['id' => $data['id_reservation']]);
            $reservation = $stmt_get->fetch();
            
            if (!$reservation) {
                echo json_encode(['success' => false, 'message' => 'Réservation non trouvée']);
                break;
            }
            
            $stmt = $db->prepare("
                UPDATE reservations 
                SET statut_reservation = :statut, date_modification_statut = CURRENT_TIMESTAMP 
                WHERE id_reservation = :id
            ");
            $result = $stmt->execute([
                'statut' => $data['statut_reservation'],
                'id' => $data['id_reservation']
            ]);
            
            if ($reservation['statut_reservation'] !== 'accepte' && $data['statut_reservation'] === 'accepte') {
                $db->prepare("UPDATE produits SET quantite_disponible = quantite_disponible - 1 WHERE id_produit = :id AND quantite_disponible > 0")
                   ->execute(['id' => $reservation['id_produit']]);
            } elseif ($reservation['statut_reservation'] === 'accepte' && $data['statut_reservation'] !== 'accepte') {
                $db->prepare("UPDATE produits SET quantite_disponible = quantite_disponible + 1 WHERE id_produit = :id AND quantite_disponible < quantite_totale")
                   ->execute(['id' => $reservation['id_produit']]);
            }
            
            echo json_encode(['success' => $result, 'message' => 'Statut mis à jour']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
