<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'connecte' => false,
    'utilisateur' => null
];

if (isset($_SESSION['utilisateur_connecte'])) {
    require_once __DIR__ . '/../config/database.php';

    try {
        $db = obtenirConnexionBD();
        $stmt = $db->prepare("SELECT id_utilisateur, nom_utilisateur, prenom_utilisateur, email_utilisateur, role_utilisateur
                              FROM utilisateurs WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $_SESSION['utilisateur_connecte']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $response['connecte'] = true;
            $response['utilisateur'] = $user;
        }
    } catch (Exception $e) {
        error_log("Erreur vérification session: " . $e->getMessage());
    }
}

echo json_encode($response);
?>
