<?php
/**
 * Middleware d'authentification pour vérifier les permissions administrateur
 */

function verifierAuthentificationAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['utilisateur_connecte']) || !isset($_SESSION['role_utilisateur'])) {
        http_response_code(401);
        echo json_encode([
            'succes' => false,
            'message' => 'Non authentifié. Veuillez vous connecter.'
        ]);
        exit;
    }

    // Vérifier si l'utilisateur a le rôle administrateur
    if ($_SESSION['role_utilisateur'] !== 'administrateur') {
        http_response_code(403);
        echo json_encode([
            'succes' => false,
            'message' => 'Accès refusé. Permissions administrateur requises.'
        ]);
        exit;
    }

    return true;
}

function verifierAuthentificationUtilisateur() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['utilisateur_connecte'])) {
        http_response_code(401);
        echo json_encode([
            'succes' => false,
            'message' => 'Non authentifié. Veuillez vous connecter.'
        ]);
        exit;
    }

    return $_SESSION['utilisateur_connecte'];
}

function obtenirUtilisateurConnecte() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['utilisateur_connecte']) ? $_SESSION['utilisateur_connecte'] : null;
}
?>
