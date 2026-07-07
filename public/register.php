<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Si déjà connecté, inutile de rester sur cette page
if (estConnecte()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erreur = '';
$nom = $prenom = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $mdp     = $_POST['password'] ?? '';
    $mdpConf = $_POST['password_confirm'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $mdp === '') {
        $erreur = 'Merci de remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse e-mail invalide.';
    } elseif (strlen($mdp) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($mdp !== $mdpConf) {
        $erreur = 'Les deux mots de passe ne correspondent pas.';
    } else {
        $idLecteur = inscrireLecteur($nom, $prenom, $email, $mdp);

        if ($idLecteur === false) {
            $erreur = 'Un compte existe déjà avec cet e-mail.';
        } else {
            connecterLecteur($email, $mdp);
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . BASE_URL . '/' . ltrim($redirect, '/'));
            exit;
        }
    }
}

$pageTitle = 'Inscription';
$activeNav = '';
require __DIR__ . '/partials/header.php';
?>

<main class="auth-page">
    <section class="auth-section">
        <div class="auth-card">
            <h1>Créer un compte</h1>
            <p class="auth-subtitle">Inscrivez-vous pour ajouter des livres à votre liste de lecture.</p>

            <?php if ($erreur !== ''): ?>
            <div class="form-error"><?= h($erreur) ?></div>
            <?php endif; ?>

            <form method="post"
                action="register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="form-input" value="<?= h($nom) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-input" value="<?= h($prenom) ?>"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= h($email) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input" minlength="6" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input"
                        minlength="6" required>
                </div>

                <button type="submit" class="btn-primary btn-block">S'inscrire</button>
            </form>

            <p class="auth-footer-note">Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
        </div>
    </section>

    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>