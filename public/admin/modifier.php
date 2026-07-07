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
        $nouvelleImage    = uploadImageLivre($_FILES['image'] ?? []);
        $supprimerImage   = isset($_POST['supprimer_image']);

        if ($nouvelleImage !== null) {
            $image = $nouvelleImage;           // une nouvelle image a été envoyée
        } elseif ($supprimerImage) {
            $image = null;                     // l'admin a coché "supprimer l'image"
        } else {
            $image = $livre['image'];          // on garde l'image actuelle
        }

        modifierLivre($id, $titre, $auteur, $description, $maisonEdition, $nombreExemplaire, $image);
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

            <form method="post" action="modifier.php?id=<?= (int) $id ?>" enctype="multipart/form-data">
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
                    <label class="form-label">Image de couverture</label>
                    <div class="image-preview-row">
                        <div class="image-preview" id="image-preview" <?php if ($url = couvertureUrl($livre['image'])): ?>
                            style="background-image:url('<?= h($url) ?>');" <?php endif; ?>></div>
                        <div>
                            <input type="file" id="image" name="image" class="form-input"
                                accept="image/png, image/jpeg, image/webp">
                            <p class="form-hint">Laissez vide pour conserver l'image actuelle.</p>
                            <?php if ($livre['image']): ?>
                            <label class="checkbox-row" style="margin-top:8px;">
                                <input type="checkbox" name="supprimer_image" value="1">
                                Supprimer l'image actuelle (afficher le visuel par défaut)
                            </label>
                            <?php endif; ?>
                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('image');
        const preview = document.getElementById('image-preview');
        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                preview.style.backgroundImage = 'url(' + event.target.result + ')';
            };
            reader.readAsDataURL(file);
        });
    });
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>