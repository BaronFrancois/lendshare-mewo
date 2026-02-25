<?php
/**
 * Système de logging simple pour faciliter le débogage
 */

// Niveaux de log
define('LOG_DEBUG', 'DEBUG');
define('LOG_INFO', 'INFO');
define('LOG_WARNING', 'WARNING');
define('LOG_ERROR', 'ERROR');

/**
 * Écrit un message dans le fichier de log
 *
 * @param string $message Le message à logger
 * @param string $niveau Le niveau du log (DEBUG, INFO, WARNING, ERROR)
 * @param array $contexte Données contextuelles supplémentaires
 */
function ecrireLog($message, $niveau = LOG_INFO, $contexte = []) {
    // Définir le chemin du fichier de log
    $chemin_log = __DIR__ . '/../../logs/app.log';

    // Créer le dossier logs s'il n'existe pas
    $dossier_logs = dirname($chemin_log);
    if (!file_exists($dossier_logs)) {
        mkdir($dossier_logs, 0755, true);
    }

    // Formater le message
    $timestamp = date('Y-m-d H:i:s');
    $message_formate = "[{$timestamp}] [{$niveau}] {$message}";

    // Ajouter le contexte si présent
    if (!empty($contexte)) {
        $message_formate .= ' | Contexte: ' . json_encode($contexte, JSON_UNESCAPED_UNICODE);
    }

    $message_formate .= PHP_EOL;

    // Écrire dans le fichier
    file_put_contents($chemin_log, $message_formate, FILE_APPEND);
}

/**
 * Fonctions raccourcies pour chaque niveau de log
 */
function logDebug($message, $contexte = []) {
    ecrireLog($message, LOG_DEBUG, $contexte);
}

function logInfo($message, $contexte = []) {
    ecrireLog($message, LOG_INFO, $contexte);
}

function logWarning($message, $contexte = []) {
    ecrireLog($message, LOG_WARNING, $contexte);
}

function logError($message, $contexte = []) {
    ecrireLog($message, LOG_ERROR, $contexte);
}

/**
 * Log une exception avec sa stack trace
 */
function logException($exception, $message_supplementaire = '') {
    $message = $message_supplementaire ? $message_supplementaire . ': ' : '';
    $message .= $exception->getMessage();

    $contexte = [
        'fichier' => $exception->getFile(),
        'ligne' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];

    ecrireLog($message, LOG_ERROR, $contexte);
}
