<?php
/**
 * app/config.php
 * Configuration globale du projet Bibliothèque en Ligne.
 * Centralise les paramètres afin de ne jamais les dupliquer ailleurs.
 */

// Affiche les erreurs en développement (à mettre à false en production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Démarre la session (utile plus tard pour le lecteur connecté / liste de lecture)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Paramètres de connexion à la base de données ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'bibliotheque_online');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Paramètres généraux de l'application ---
define('APP_NAME', 'Readly');
define('BASE_URL', '/bibliotheque_enligne/public');