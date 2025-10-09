<?php
/**
 * Configuration générale de l'application VALRES2
 */

// Configuration de l'application
define('APP_NAME', 'VALRES2 - Réservation de salles M2L');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/VALRES2');

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'valres2');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuration de sécurité
define('SECURITY_SALT', 'votre_salt_unique_ici'); // À changer en production
define('SESSION_TIMEOUT', 3600); // 1 heure

// Configuration des chemins
define('BASE_PATH', __DIR__ . '/..');
define('VIEWS_PATH', BASE_PATH . '/views');
define('EXPORTS_PATH', BASE_PATH . '/exports');

// Configuration des rôles utilisateurs
define('ROLE_ADMIN', 'administrateur');
define('ROLE_SECRETARIAT', 'secretariat');
define('ROLE_RESPONSABLE', 'responsable');
define('ROLE_USER', 'utilisateur');

// États des réservations
define('ETAT_PROVISOIRE', 'provisoire');
define('ETAT_CONFIRME', 'confirme');
define('ETAT_ANNULE', 'annule');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
?>