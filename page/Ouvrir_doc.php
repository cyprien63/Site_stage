<?php
    require_once __DIR__ . '/../source/connection.php';
    
    $id = $_GET['id'] ?? '';
    $errorMessage = '';

    if ($id !== '') {
        $conn = getConnection();
        $safeId = $conn->real_escape_string($id);
        $sql = "SELECT id FROM doc WHERE id = '$safeId'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            header("Location: /page/editeur.php?id=" . urlencode($id));
            exit;
        } else {
            $errorMessage = 'Code incorrect.';
        }

        $conn->close();
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

    <form action="/page/Ouvrir_doc.php" method="get">
        <div class="conteneur">

            <div class="boite">

                <p>votre code ? :</p>

                <input type="text" class="code-unique" placeholder="Entrez le code..." id="code-input" name="id" maxlength="10" value="<?php echo htmlspecialchars($id); ?>">

                <?php if ($errorMessage !== ''): ?>
                    <p style="color: red; margin-top: 20px;"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php endif; ?>

            </div>

            
            <div class="bouton_ouvrir_boite">
                
                <button type="submit" class="btn" id="ouvrir-btn">Ouvrir</button>
            
            </div>
        </div>
    </form>

</body>
</html>