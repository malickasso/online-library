<?php
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/helpers.php';

// Accès réservé aux administrateurs
exigerAdmin();

// Traitement de la suppression (Post/Redirect/Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $idASupprimer = (int) ($_POST['id'] ?? 0);
    if ($idASupprimer > 0) {
        supprimerLivre($idASupprimer);
    }
    header('Location: index.php');
    exit;
}

$livres = getTousLesLivresAdmin();

$pageTitle = 'Administration — Livres';
$activeNav = 'admin';
require __DIR__ . '/../partials/header.php';
?>

<section class="admin-section">
    <div class="container">

        <div class="admin-toolbar">
            <div>
                <h1 class="page-title" style="font-size:1.7rem;">Gestion des livres</h1>
                <p class="results-count"><strong><?= count($livres) ?></strong>
                    livre<?= count($livres) > 1 ? 's' : '' ?> dans la bibliothèque</p>
            </div>
            <a href="ajouter.php" class="btn-primary" style="text-decoration:none;">+ Ajouter un livre</a>
        </div>

        <div class="admin-card">
            <?php if (empty($livres)): ?>
            <p class="admin-empty">Aucun livre pour le moment. Commencez par en ajouter un.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Maison d'édition</th>
                        <th>Exemplaires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livres as $livre): ?>
                    <tr>
                        <td><?= h($livre['titre']) ?></td>
                        <td><?= h($livre['auteur']) ?></td>
                        <td><?= h($livre['maison_edition'] ?: '—') ?></td>
                        <td><?= (int) $livre['nombre_exemplaire'] ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="modifier.php?id=<?= (int) $livre['id'] ?>" class="btn-small">Modifier</a>
                                <form method="post" action="index.php"
                                    onsubmit="return confirm('Supprimer définitivement « <?= h(addslashes($livre['titre'])) ?> » ?');">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id" value="<?= (int) $livre['id'] ?>">
                                    <button type="submit" class="btn-small btn-danger">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>