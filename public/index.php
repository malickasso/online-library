<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Readly');
}

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('getDernieresNouveautes')) {
    require_once __DIR__ . '/../app/db.php';

    function getDernieresNouveautes(int $limite = 4): array
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, titre, auteur, maison_edition FROM livres ORDER BY id DESC LIMIT :limite');
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

// Récupère les 4 derniers livres ajoutés pour la section "Nouveautés"
$nouveautes = getDernieresNouveautes(4);
$pageTitle = 'Accueil';
$activeNav = 'accueil';
require __DIR__ . '/partials/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Vos archives numériques</h1>
        <p class="hero-subtitle">
            Découvrez des milliers d'ouvrages dans notre bibliothèque numérique respectueuse
            de l'environnement. Un accès facile, zéro gaspillage de papier.
        </p>

        <!-- ETAPES -->
        <div class="steps">
            <div class="step-card">
                <span class="step-number">01</span>
                <span class="step-label">Première étape</span>
                <p class="step-text">Recherchez un livre dans notre vaste base de données.</p>
            </div>
            <div class="step-card">
                <span class="step-number">02</span>
                <span class="step-label">Deuxième étape</span>
                <p class="step-text">Ajoutez-les à votre liste et organisez vos lectures.</p>
            </div>
            <div class="step-card">
                <span class="step-number">03</span>
                <span class="step-label">Troisième étape</span>
                <p class="step-text">Commencez à lire immédiatement sur n'importe quel appareil.</p>
            </div>
        </div>

        <!-- RECHERCHE -->
        <div class="search-panel">
            <form class="search-bar" action="results.php" method="get">
                <div class="search-select-wrap">
                    <select name="critere" class="search-select">
                        <option value="titre">Titre</option>
                        <option value="auteur">Auteur</option>
                    </select>
                </div>
                <div class="search-input-wrap">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" name="q" class="search-input" placeholder="Rechercher dans les archives...">
                </div>
                <button type="submit" class="search-btn">RECHERCHER</button>
            </form>
        </div>
    </div>
</section>

<!-- NOUVEAUTES -->
<section class="new-arrivals">
    <div class="container">
        <div class="section-heading">
            <div>
                <h2>Nouveautés dans les archives</h2>
                <p class="section-subtitle">Les dernières nouveautés de notre collection.</p>
            </div>
            <a href="results.php" class="see-all">Voir les collections</a>
        </div>

        <div class="book-grid" id="book-grid">
            <?php if (empty($nouveautes)): ?>
            <p>Aucun livre n'a encore été ajouté à la bibliothèque.</p>
            <?php else: ?>
            <?php foreach ($nouveautes as $livre): ?>
            <article class="book-card">
                <a href="details.php?id=<?= (int) $livre['id'] ?>">
                    <div class="book-cover"></div>
                    <h3 class="book-title"><?= h($livre['titre']) ?></h3>
                    <p class="book-author"><?= h($livre['auteur']) ?></p>
                </a>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>