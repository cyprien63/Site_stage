<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>DocKey</title>
        <link rel="stylesheet" href="/style/style.css">
    </head>
    <body>

        <header class="top-bar">
            <div class="logo-container">
                <img src="/source/LOGO.png" id="logo" alt="Logo">
                <h1>DocKey<?php if (isset($_SESSION['id']) && $_SESSION['id'] !== ''): ?><span title="<?php echo htmlspecialchars($_SESSION['Nom'] ?? ''); ?>">*</span><?php endif; ?></h1>
            </div>
        </header>

        <div class="boutton-principal">
            <a href="/page/Creer_doc.php" class="btn">Créer Doc</a>
            <a href="/page/Ouvrir_doc.php" class="btn">Ouvrir Doc</a>
        </div>

        <div class="boutton-connection">
            <a href = "/page/page_connection.php" class="btn">Se connecter</a>
        </div>

    </body>
</html>