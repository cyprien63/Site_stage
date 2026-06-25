<?php

    session_start();


    if (!isset($_SESSION['id'])) {
        header('Location: /page/connexion.php');
        exit;
    }

    require_once __DIR__ . '/../source/connection.php';
    $conn = getConnection();

    $id_connecte = $_SESSION['id'];

    if (isset($_GET['delete']) && $_GET['delete'] !== '') {
        $deleteId = $conn->real_escape_string($_GET['delete']);
        $conn->query("DELETE FROM doc WHERE id = '$deleteId' AND id_compte = '$id_connecte'");
    }

    $list = $conn->query("SELECT id FROM doc WHERE id_compte = '$id_connecte'");
?>

<?php

    require_once __DIR__ . '/../source/connection.php';


    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        deconnexion();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DocKey - Mes documents</title>
    <link rel="stylesheet" href="/style/style.css">
</head>
<body>

    <header class="top-bar">
        <h1>DocKey</h1>
        <a href="?action=logout" class="btn_deconnextion">Déconnexion</a>
    </header>
    
    <div class="liste">
        <p>Liste des documents :</p>
        <?php if ($list && $list->num_rows > 0): ?>
            <ul>
                <?php while ($doc = $list->fetch_assoc()): ?>
                    <li>
                        <a class="doc-link" href="/page/editeur.php?id=<?php echo urlencode($doc['id']); ?>"><?php echo htmlspecialchars($doc['id']); ?></a>
                        <a class="delete-link" href="?delete=<?php echo urlencode($doc['id']); ?>" title="Supprimer ce document">✕</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Aucun document trouvé.</p>
        <?php endif; ?>
    </div>

    <div class="conteneur-bouton">
        <a href="/page/Creer_doc.php" class="btn">Créer un document</a>
    </div>

</body>
</html>