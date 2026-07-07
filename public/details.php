<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$livre = $id > 0 ? getLivreParId($id) : null;

$vientDetreAjoute = false;

// Traitement du formulaire "Ajouter / Retirer de la liste de lecture"
// (le lecteur doit être connecté : la page a déjà redirigé vers login.php sinon)
if ($livre && $_SERVER['REQUEST_METHOD'] === 'POST' && estConnecte()) {
    $action = $_POST['action'] ?? '';
    $idLecteur = (int) $_SESSION['lecteur_id'];

    if ($action === 'ajouter') {
        ajouterALaListe($livre['id'], $idLecteur);
        $vientDetreAjoute = true;
    } elseif ($action === 'retirer') {
        retirerDeLaListe($livre['id'], $idLecteur);
    }
}

$dejaDansLaListe = ($livre && estConnecte()) ? estDansLaListe($livre['id'], (int) $_SESSION['lecteur_id']) : false;

$pageTitle = $livre ? $livre['titre'] : 'Livre introuvable';
$activeNav = '';
require __DIR__ . '/partials/header.php';
?>

<?php if (!$livre): ?>

<!-- LIVRE INTROUVABLE -->
<section class="details-section">
    <div class="container not-found">
        <h1 class="page-title">Livre introuvable</h1>
        <p class="results-count">Ce livre n'existe pas ou a été retiré de la bibliothèque.</p>
        <a href="results.php" class="details-link" style="display:inline-block;width:auto;padding:12px 28px;">Voir les
            archives</a>
    </div>
</section>

<?php else: ?>

<!-- DETAILS DU LIVRE -->
<section class="details-section">
    <div class="container">
        <a href="results.php" class="back-link">&larr; Retour aux résultats</a>

        <div class="details-layout" style="margin-top:24px;">
            <img src="<?= couvertureUrl($livre['image'] ?? null) ?>" alt="<?= h($livre['titre']) ?>" class="details-cover">

            <div class="details-info">
                <h1 class="page-title"><?= h($livre['titre']) ?></h1>
                <p class="details-author">par <?= h($livre['auteur']) ?></p>

                <div class="details-meta">
                    <div class="meta-item">
                        <span class="meta-label">Maison d'édition</span>
                        <span class="meta-value"><?= h($livre['maison_edition'] ?: 'Non renseignée') ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Disponibilité</span>
                        <?php if ((int) $livre['nombre_exemplaire'] > 0): ?>
                        <span class="availability disponible"><?= (int) $livre['nombre_exemplaire'] ?> exemplaire(s)
                            disponible(s)</span>
                        <?php else: ?>
                        <span class="availability indisponible">Aucun exemplaire disponible</span>
                        <?php endif; ?>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Date d'ajout</span>
                        <span class="meta-value">
                            <?= isset($livre['created_at']) && $livre['created_at'] !== '' ? date('d/m/Y', strtotime($livre['created_at'])) : 'Non renseignée' ?>
                        </span>
                    </div>
                </div>

                <p class="details-description">
                    <?= $livre['description'] ? nl2br(h($livre['description'])) : 'Aucune description disponible pour cet ouvrage.' ?>
                </p>

                <?php if (estConnecte()): ?>
                <form method="post" action="details.php?id=<?= (int) $livre['id'] ?>"
                    style="display:flex; align-items:center;">
                    <?php if ($dejaDansLaListe): ?>
                    <input type="hidden" name="action" value="retirer">
                    <button type="submit" class="btn-primary is-active">Retirer de la liste de lecture</button>
                    <?php else: ?>
                    <input type="hidden" name="action" value="ajouter">
                    <button type="submit" class="btn-primary">Ajouter à la liste de lecture</button>
                    <?php endif; ?>

                    <?php if ($vientDetreAjoute): ?>
                    <span class="confirm-message">✓ Ajouté à votre liste de lecture</span>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                <a href="login.php?redirect=<?= urlencode('details.php?id=' . $livre['id']) ?>" class="btn-primary"
                    style="display:inline-block; text-decoration:none;">
                    Se connecter pour ajouter à la liste
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>