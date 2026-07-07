<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Récupère et sécurise les paramètres venant du formulaire de recherche
$critere = critereValide($_GET['critere'] ?? null);
$q       = trim($_GET['q'] ?? '');

$resultats = rechercherLivres($critere, $q);
$nbResultats = count($resultats);
$pageTitle = 'Résultats de recherche';
$activeNav = 'accueil';
require __DIR__ . '/partials/header.php';
?>

<!-- EN-TETE DE PAGE + RECHERCHE -->
<section class="page-hero">
    <div class="container">
        <a href="index.php" class="back-link">&larr; Retour à l'accueil</a>

        <?php if ($q !== ''): ?>
        <h1 class="page-title">Résultats pour « <?= h($q) ?> »</h1>
        <?php else: ?>
        <h1 class="page-title">Toutes les archives</h1>
        <?php endif; ?>

        <p class="results-count"><strong><?= $nbResultats ?></strong> livre<?= $nbResultats > 1 ? 's' : '' ?>
            trouvé<?= $nbResultats > 1 ? 's' : '' ?></p>

        <div class="search-panel">
            <form class="search-bar" action="results.php" method="get">
                <div class="search-select-wrap">
                    <select name="critere" class="search-select">
                        <option value="titre" <?= $critere === 'titre' ? 'selected' : '' ?>>Titre</option>
                        <option value="auteur" <?= $critere === 'auteur' ? 'selected' : '' ?>>Auteur</option>
                    </select>
                </div>
                <div class="search-input-wrap">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" name="q" class="search-input" placeholder="Rechercher dans les archives..."
                        value="<?= h($q) ?>">
                </div>
                <button type="submit" class="search-btn">RECHERCHER</button>
            </form>
        </div>
    </div>
</section>

<!-- RESULTATS -->
<section class="results-section">
    <div class="container">
        <div class="results-grid">
            <?php if ($nbResultats === 0): ?>
            <div class="no-results">
                Aucun livre ne correspond à votre recherche. Essayez un autre titre ou un autre auteur.
            </div>
            <?php else: ?>
            <?php foreach ($resultats as $livre): ?>
            <article class="book-card">
                <img src="<?= couvertureUrl($livre['image'] ?? null) ?>" alt="<?= h($livre['titre']) ?>" class="book-cover">
                <h3 class="book-title"><?= h($livre['titre']) ?></h3>
                <p class="book-author"><?= h($livre['auteur']) ?></p>
                <a href="details.php?id=<?= (int) $livre['id'] ?>" class="details-link">Voir les détails</a>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>