<?php
/**
 * app/db.php
 * Ouvre une connexion PDO unique vers la base de données.
 * À inclure après config.php dans chaque page qui a besoin de la BDD.
 */

require_once __DIR__ . '/config.php';

if (!function_exists('getPDO')) {
    function getPDO(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // On ne montre jamais le message brut de PDO à l'utilisateur final
                die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
            }
        }

        return $pdo;
    }
}