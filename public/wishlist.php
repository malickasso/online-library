<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Cette page nécessite d'être connecté
exigerConnexion('wishlist.php');

$idLecteur = (int) $_SESSION['lecteur_id'];

// Traitement du retrait d'un livre (Post/Redirect/Get pour éviter la re-soumission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'retirer') {
    $idARetirer = (int) ($_POST['id'] ?? 0);
    if ($idARetirer > 0) {
        retirerDeLaListe($idARetirer, $idLecteur);
    }
    header('Location: wishlist.php');
    exit;
}

$livres = getLivresDeLaListe($idLecteur);
$nbLivres = count($livres);

$pageTitle = 'Ma liste de lecture';
$activeNav = 'wishlist';
require __DIR__ . '/partials/header.php';
?>

<!-- EN-TETE DE PAGE -->
<section class="page-hero">
    <div class="container">
        <a href="index.php" class="back-link">&larr; Retour à l'accueil</a>
        <h1 class="page-title">Ma liste de lecture</h1>
        <p class="results-count">
            <?php if ($nbLivres > 0): ?>
            <strong><?= $nbLivres ?></strong> livre<?= $nbLivres > 1 ? 's' : '' ?> dans votre liste
            <?php else: ?>
            Votre liste de lecture est vide pour le moment
            <?php endif; ?>
        </p>
    </div>
</section>

<!-- LISTE DE LECTURE -->
<section class="wishlist-section">
    <div class="container">

        <?php if ($nbLivres === 0): ?>

        <div class="empty-state">
            <h2 class="section-heading" style="justify-content:center; margin-bottom:0;">Aucun livre pour l'instant</h2>
            <p>Parcourez les archives et ajoutez des livres à votre liste de lecture pour les retrouver ici.</p>
            <a href="results.php" class="btn-primary">Découvrir les archives</a>
        </div>

        <?php else: ?>

        <div class="results-grid">
            <?php foreach ($livres as $livre): ?>
            <article class="book-card">
                <div class="book-cover"></div>
                <h3 class="book-title"><?= h($livre['titre']) ?></h3>
                <p class="book-author"><?= h($livre['auteur']) ?></p>
                <p class="book-author">Emprunté le <?= h(date('d/m/Y', strtotime($livre['date_emprunt']))) ?></p>

                <div class="card-actions">
                    <a href="details.php?id=<?= (int) $livre['id'] ?>" class="details-link">Voir les détails</a>
                    <form method="post" action="wishlist.php" class="remove-form">
                        <input type="hidden" name="action" value="retirer">
                        <input type="hidden" name="id" value="<?= (int) $livre['id'] ?>">
                        <button type="submit" class="remove-btn">Retirer de la liste</button>
                    </form>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>