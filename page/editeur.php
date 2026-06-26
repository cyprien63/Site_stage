<?php
session_start();
require_once __DIR__ . '/../source/connection.php';

$code_unique = $_GET['id'] ?? '';
$editorText = '';
$lecture_seule = false;

if ($code_unique !== '') {
    $id_user = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;
    if (!peutVoirDoc($code_unique, $id_user)) {
        header('Location: /index.php');
        exit;
    }
    if (!peutModifierDoc($code_unique, $id_user)) {
        $lecture_seule = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lecture_seule) {
    $code_unique = $_POST['id'] ?? $code_unique;
    $editorText = $_POST['contents'] ?? '';

    if ($code_unique !== '') {
        $error = updateDocContents($code_unique, $editorText);
        header("Location: /page/editeur.php?id=" . urlencode($code_unique));
        exit;
    }
} elseif ($code_unique !== '') {
    $editorText = getDocContents($code_unique);
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
        <h1>DocKey<?php if (isset($_SESSION["id"]) && $_SESSION["id"] !== ""): ?><span title="<?php echo htmlspecialchars($_SESSION["Nom"] ?? ""); ?>">*</span><?php endif; ?></h1>
    </header>

    <form action="" method="POST">
        <textarea class="feuille" name="contents" placeholder="Commencez à écrire votre document ici..." spellcheck="false" <?php echo $lecture_seule ? 'readonly' : ''; ?>><?php echo htmlspecialchars($editorText); ?></textarea>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($code_unique); ?>">
        <?php if (!$lecture_seule): ?>
        <div class="conteneur-bouton1">
            <button class="btn" type="submit">Enregistrer</button>
        </div>
        <?php endif; ?>
    </form>

    <div class="code-unique-editeur"><?php echo htmlspecialchars($code_unique); ?></div>

</body>
</html>