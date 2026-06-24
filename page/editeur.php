<?php
require_once __DIR__ . '/../source/connection.php';

$code_unique = $_GET['id'];
$editorText = '';
$savedMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_unique = $_POST['id'] ?? $code_unique;
    $editorText = $_POST['contents'] ?? '';

    if ($code_unique !== '') {
        $error = updateDocContents($code_unique, $editorText);
        $savedMessage = $error === '' ? 'Enregistré avec succès.' : 'Erreur : ' . $error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DocKey</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

    <header class="top-bar">
        <h1>DocKey</h1>
    </header>

    <form action="" method="POST">
        <textarea class="feuille" name="contents" placeholder="Commencez à écrire votre document ici..." spellcheck="false"><?php echo htmlspecialchars($editorText); ?></textarea>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($code_unique); ?>">
        <div class="conteneur-bouton">
            <button class="btn" type="submit">Enregistrer</button>
        </div>
    </form>

    <?php if ($savedMessage !== ''): ?>
        <p><?php echo htmlspecialchars($savedMessage); ?></p>
    <?php endif; ?>

    <div class="code-unique-editeur"><?php echo htmlspecialchars($code_unique); ?></div>

</body>
</html>