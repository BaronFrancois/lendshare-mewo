<?php

require_once __DIR__ . '/config.php';

function obtenirConnexionBD() {
    try {
        $db_path = CHEMIN_BASE_DONNEES;
        $db_connection = new PDO('sqlite:' . $db_path);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $db_connection;
    } catch (PDOException $e) {
        throw new Exception("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}
?>
