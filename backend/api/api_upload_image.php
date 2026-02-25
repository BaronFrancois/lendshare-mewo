<?php
/**
 * API pour uploader des images de produits ou catégories
 * Accessible uniquement aux administrateurs
 */

// Headers pour l'API
header('Content-Type: application/json; charset=utf-8');

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/middleware_authentification.php';
require_once __DIR__ . '/../utils/logger.php';

// Fonction pour envoyer une réponse JSON
function envoyerReponseJSON($code_http, $succes, $message, $donnees = null) {
    http_response_code($code_http);
    
    $reponse = [
        'succes' => $succes,
        'message' => $message
    ];
    
    if ($donnees !== null) {
        $reponse['donnees'] = $donnees;
    }
    
    echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérifier que l'utilisateur est administrateur
verifierAuthentificationAdmin();

logInfo("Tentative d'upload d'image par l'utilisateur " . $_SESSION['utilisateur_connecte']);

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logWarning("Tentative d'upload avec méthode non-POST: " . $_SERVER['REQUEST_METHOD']);
    envoyerReponseJSON(405, false, "Méthode non autorisée");
}

// Vérifier qu'un fichier a été envoyé
if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    logWarning("Aucune image fournie dans la requête");
    envoyerReponseJSON(400, false, "Aucune image fournie");
}

$fichier = $_FILES['image'];

// Vérifier les erreurs d'upload
if ($fichier['error'] !== UPLOAD_ERR_OK) {
    logError("Erreur d'upload de fichier", ['code_erreur' => $fichier['error']]);
    envoyerReponseJSON(400, false, "Erreur lors de l'upload : " . $fichier['error']);
}

// Récupérer le type (produit ou categorie)
$type = isset($_POST['type']) ? $_POST['type'] : 'produit';

if (!in_array($type, ['produit', 'categorie'])) {
    envoyerReponseJSON(400, false, "Type invalide");
}

// Vérifier la taille du fichier (5 Mo max)
$taille_max = 5 * 1024 * 1024; // 5 Mo
if ($fichier['size'] > $taille_max) {
    envoyerReponseJSON(400, false, "Fichier trop volumineux (5 Mo maximum)");
}

// Vérifier le type MIME
$types_autorises = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$type_mime = finfo_file($finfo, $fichier['tmp_name']);
finfo_close($finfo);

if (!in_array($type_mime, $types_autorises)) {
    envoyerReponseJSON(400, false, "Type de fichier non autorisé (JPEG, PNG, GIF, WEBP uniquement)");
}

// Définir l'extension
$extensions = [
    'image/jpeg' => 'jpg',
    'image/jpg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];
$extension = $extensions[$type_mime];

// Générer un nom unique
$nom_fichier = uniqid('img_' . $type . '_', true) . '.' . $extension;

// Déterminer le dossier de destination
if ($type === 'produit') {
    $dossier_destination = CHEMIN_RACINE_PROJET . '/uploads/products/';
    $url_relative = '../uploads/products/' . $nom_fichier;
} else {
    $dossier_destination = CHEMIN_RACINE_PROJET . '/uploads/categories/';
    $url_relative = '../uploads/categories/' . $nom_fichier;
}

// Créer le dossier s'il n'existe pas
if (!file_exists($dossier_destination)) {
    mkdir($dossier_destination, 0755, true);
}

// Déplacer le fichier
$chemin_complet = $dossier_destination . $nom_fichier;

if (move_uploaded_file($fichier['tmp_name'], $chemin_complet)) {
    // Succès
    logInfo("Image uploadée avec succès", [
        'nom_fichier' => $nom_fichier,
        'type' => $type,
        'chemin' => $chemin_complet
    ]);
    envoyerReponseJSON(200, true, "Image uploadée avec succès", [
        'nom_fichier' => $nom_fichier,
        'url' => $url_relative,
        'type' => $type
    ]);
} else {
    logError("Échec de l'enregistrement de l'image", [
        'chemin_destination' => $chemin_complet,
        'tmp_name' => $fichier['tmp_name']
    ]);
    envoyerReponseJSON(500, false, "Erreur lors de l'enregistrement de l'image");
}
