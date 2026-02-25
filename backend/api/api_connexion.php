<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['mot_de_passe'])) {
    logWarning("Tentative de connexion sans email ou mot de passe");
    echo json_encode(['succes' => false, 'message' => 'Email et mot de passe requis']);
    exit;
}

logInfo("Tentative de connexion", ['email' => $data['email']]);

try {
    $db = obtenirConnexionBD();
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email_utilisateur = :email");
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($data['mot_de_passe'], $user['mot_de_passe_utilisateur'])) {
        $_SESSION['utilisateur_connecte'] = $user['id_utilisateur'];
        $_SESSION['role_utilisateur'] = $user['role_utilisateur'];

        logInfo("Connexion réussie", [
            'utilisateur_id' => $user['id_utilisateur'],
            'email' => $user['email_utilisateur'],
            'role' => $user['role_utilisateur']
        ]);

        echo json_encode([
            'succes' => true,
            'message' => 'Connexion réussie',
            'utilisateur' => [
                'id' => $user['id_utilisateur'],
                'email' => $user['email_utilisateur'],
                'role_utilisateur' => $user['role_utilisateur']
            ]
        ]);
    } else {
        logWarning("Échec de connexion - Identifiants invalides", ['email' => $data['email']]);
        echo json_encode(['succes' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
} catch (Exception $e) {
    logException($e, "Erreur lors de la connexion");
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur']);
}
?>
