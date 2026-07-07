<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

if (estConnecte()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erreur = '';
$email = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['password'] ?? '';

    if ($email === '' || $mdp === '') {
        $erreur = 'Merci de renseigner votre e-mail et votre mot de passe.';
    } else {
        $lecteur = connecterLecteur($email, $mdp);

        if ($lecteur === false) {
            $erreur = 'E-mail ou mot de passe incorrect.';
        } else {
            header('Location: ' . BASE_URL . '/' . ltrim($redirect, '/'));
            exit;
        }
    }
}

$pageTitle = 'Connexion';
$activeNav = '';
require __DIR__ . '/partials/header.php';
?>

<main class="auth-page">
    <section class="auth-section">
        <div class="auth-card">
            <h1>Connexion</h1>
            <p class="auth-subtitle">Connectez-vous pour accéder à votre liste de lecture.</p>

            <?php if ($erreur !== ''): ?>
            <div class="form-error"><?= h($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <input type="hidden" name="redirect" value="<?= h($redirect) ?>">

                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= h($email) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>

                <button type="submit" class="btn-primary btn-block">Se connecter</button>
            </form>

            <p class="auth-footer-note">Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </div>
    </section>

    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>