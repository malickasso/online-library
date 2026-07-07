<?php
/**
 * public/partials/header.php
 * Variables attendues (optionnelles) avant l'include :
 *   $pageTitle  (string) — affiché dans <title>
 *   $activeNav  (string) — 'accueil' | 'wishlist' | 'admin' pour surligner le lien actif
 */
$pageTitle = $pageTitle ?? '';
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(APP_NAME) ?><?= $pageTitle !== '' ? ' - ' . h($pageTitle) : '' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>

<body>

    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= BASE_URL ?>/index.php" class="logo"><?= h(APP_NAME) ?></a>

            <nav class="main-nav">
                <a href="<?= BASE_URL ?>/index.php"
                    class="nav-link <?= $activeNav === 'accueil' ? 'active' : '' ?>">Accueil</a>
                <?php if (estConnecte()): ?>
                <a href="<?= BASE_URL ?>/wishlist.php"
                    class="nav-link <?= $activeNav === 'wishlist' ? 'active' : '' ?>">Ma liste de lecture</a>
                <?php endif; ?>
                <?php if (estAdmin()): ?>
                <a href="<?= BASE_URL ?>/admin/index.php"
                    class="nav-link <?= $activeNav === 'admin' ? 'active' : '' ?>">Administration</a>
                <?php endif; ?>
            </nav>

            <div class="auth-nav">
                <div class="user-menu">
                    <button class="user-menu-toggle" type="button" aria-expanded="false" aria-label="Menu utilisateur">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7">
                            <circle cx="12" cy="8" r="4.2" />
                            <path d="M5 19c1.2-3.1 3.7-4.7 7-4.7s5.8 1.6 7 4.7" />
                        </svg>
                    </button>

                    <div class="user-menu-dropdown">
                        <?php if (estConnecte()): ?>
                        <?php
                            $prenom = $_SESSION['lecteur_prenom'] ?? '';
                            $nom = $_SESSION['lecteur_nom'] ?? '';
                            $nomComplet = trim($prenom . ' ' . $nom);
                        ?>
                        <div class="user-menu-user">
                            <strong><?= $nomComplet !== '' ? h($nomComplet) : 'Bonjour' ?></strong>
                            <span>Compte lecteur</span>
                        </div>
                        <a href="<?= BASE_URL ?>/logout.php" class="user-menu-link">Déconnexion</a>
                        <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="user-menu-link">Connexion</a>
                        <a href="<?= BASE_URL ?>/register.php" class="user-menu-link">Inscription</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>