<?php
/**
 * Fichier de configuration générale de l'application Lend&Share
 * Contient toutes les constantes utilisées dans l'application
 */

// Chemin absolu vers le dossier racine du projet
define('CHEMIN_RACINE_PROJET', dirname(__DIR__, 2));

// Configuration de la base de données
define('CHEMIN_BASE_DONNEES', CHEMIN_RACINE_PROJET . '/database/lendshare.db');

// Configuration des uploads
define('CHEMIN_UPLOAD_PRODUITS', CHEMIN_RACINE_PROJET . '/uploads/products/');
define('CHEMIN_UPLOAD_CATEGORIES', CHEMIN_RACINE_PROJET . '/uploads/categories/');
define('TAILLE_MAXIMALE_UPLOAD_OCTETS', 5 * 1024 * 1024); // 5 Mo en octets

// Configuration du cache
define('CHEMIN_DOSSIER_CACHE', CHEMIN_RACINE_PROJET . '/cache/');
define('DUREE_CACHE_SECONDES', 300); // 5 minutes

// Configuration des backups
define('CHEMIN_DOSSIER_BACKUPS', CHEMIN_RACINE_PROJET . '/backups/');
define('NOMBRE_JOURS_CONSERVATION_BACKUPS', 30);

// Configuration des logs
define('CHEMIN_DOSSIER_LOGS', CHEMIN_RACINE_PROJET . '/logs/');

// Configuration de sécurité
define('NOMBRE_MAXIMUM_TENTATIVES_CONNEXION', 5);
define('DUREE_BLOCAGE_CONNEXION_MINUTES', 15);

// Configuration des sessions
define('DUREE_SESSION_SECONDES', 3600); // 1 heure

// Types d'images autorisés
define('TYPES_IMAGES_AUTORISES', ['image/jpeg', 'image/png', 'image/webp']);
define('EXTENSIONS_IMAGES_AUTORISEES', ['jpg', 'jpeg', 'png', 'webp']);

// Dimensions maximales des images
define('LARGEUR_MAXIMALE_IMAGE_PIXELS', 2000);
define('HAUTEUR_MAXIMALE_IMAGE_PIXELS', 2000);

// Rôles utilisateurs
define('ROLE_UTILISATEUR', 'utilisateur');
define('ROLE_ADMINISTRATEUR', 'administrateur');

// Statuts des réservations
define('STATUT_RESERVATION_EN_ATTENTE', 'en_attente');
define('STATUT_RESERVATION_CONFIRMEE', 'confirmee');
define('STATUT_RESERVATION_RECUPEREE', 'recuperee');
define('STATUT_RESERVATION_RENDUE', 'rendue');
define('STATUT_RESERVATION_ANNULEE', 'annulee');

// Configuration du fuseau horaire
date_default_timezone_set('Europe/Paris');

// Configuration des erreurs (production)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
