<?php

    session_start();

    // Si l'utilisateur n'est pas connecté, on le renvoie à la connexion
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
    </header>
    
    <div class="liste">
        <p>Liste des documents :</p>
        <?php if ($list && $list->num_rows > 0): ?>
            <ul>
                <?php while ($doc = $list->fetch_assoc()): ?>
                    <li>
                        <?php echo htmlspecialchars($doc['id']); ?>
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