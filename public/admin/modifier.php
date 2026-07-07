<?php
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/helpers.php';

exigerAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$livre = $id > 0 ? getLivreParId($id) : null;

if (!$livre) {
    header('Location: index.php');
    exit;
}

$erreur = '';
$titre            = $livre['titre'];
$auteur           = $livre['auteur'];
$description      = $livre['description'];
$maisonEdition    = $livre['maison_edition'];
$nombreExemplaire = $livre['nombre_exemplaire'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre            = trim($_POST['titre'] ?? '');
    $auteur           = trim($_POST['auteur'] ?? '');
    $description      = trim($_POST['description'] ?? '');
    $maisonEdition    = trim($_POST['maison_edition'] ?? '');
    $nombreExemplaire = (int) ($_POST['nombre_exemplaire'] ?? 0);

    if ($titre === '' || $auteur === '') {
        $erreur = 'Le titre et l\'auteur sont obligatoires.';
    } elseif ($nombreExemplaire < 0) {
        $erreur = 'Le nombre d\'exemplaires ne peut pas être négatif.';
    } else {
        modifierLivre($id, $titre, $auteur, $description, $maisonEdition, $nombreExemplaire);
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Modifier ' . $livre['titre'];
$activeNav = 'admin';
require __DIR__ . '/../partials/header.php';
?>

<section class="admin-section">
    <div class="container">
        <a href="index.php" class="back-link">&larr; Retour à la liste des livres</a>
        <h1 class="page-title" style="font-size:1.7rem; margin-top:16px;">Modifier « <?= h($livre['titre']) ?> »</h1>

        <div class="admin-form-card" style="margin-top:28px;">
            <?php if ($erreur !== ''): ?>
            <div class="form-error"><?= h($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="modifier.php?id=<?= (int) $id ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="titre">Titre *</label>
                        <input type="text" id="titre" name="titre" class="form-input" value="<?= h($titre) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="auteur">Auteur *</label>
                        <input type="text" id="auteur" name="auteur" class="form-input" value="<?= h($auteur) ?>"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="maison_edition">Maison d'édition</label>
                    <input type="text" id="maison_edition" name="maison_edition" class="form-input"
                        value="<?= h($maisonEdition) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre_exemplaire">Nombre d'exemplaires</label>
                    <input type="number" id="nombre_exemplaire" name="nombre_exemplaire" class="form-input"
                        value="<?= (int) $nombreExemplaire ?>" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-input"
                        rows="5"><?= h($description) ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>