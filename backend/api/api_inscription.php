<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nom']) || !isset($data['prenom']) || !isset($data['email']) || !isset($data['mot_de_passe'])) {
    echo json_encode(['succes' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

try {
    $db = obtenirConnexionBD();
    
    $stmt = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email_utilisateur = :email");
    $stmt->execute(['email' => $data['email']]);
    
    if ($stmt->fetch()) {
        echo json_encode(['succes' => false, 'message' => 'Cet email est déjà utilisé']);
        exit;
    }
    
    $hashed_password = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO utilisateurs (nom_utilisateur, prenom_utilisateur, email_utilisateur, mot_de_passe_hash, role_utilisateur) 
        VALUES (:nom, :prenom, :email, :password, 'utilisateur')
    ");
    
    $stmt->execute([
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'email' => $data['email'],
        'password' => $hashed_password
    ]);
    
    echo json_encode([
        'succes' => true,
        'message' => 'Inscription réussie'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
