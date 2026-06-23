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
    <div class="conteneur">
        <div class="boite">
            <p>votre code :</p>

                <?php
                    require_once __DIR__ . '/../source/connection.php';
                    
                    $conn = getConnection();
                    $code_unique = bin2hex(random_bytes(5));
                    
                    $sql = "INSERT INTO doc (
                                id,
                                compte, 
                                contents, 
                                created_at, 
                                updated_at
                            ) VALUES (
                                '$code_unique',
                                'NO', 
                                'Ton texte ici', 
                                NOW(), 
                                NOW()
                            )";
                    
                    if ($conn->query($sql) !== true) {
                        $code_unique = 'Erreur SQL : ' . $conn->error;
                    }
                    
                    $conn->close();
                ?>

            <div class="code-unique"><?php echo htmlspecialchars($code_unique); ?></div>

        </div>
        <div class="bouton_ouvrir_boite"><a href="/page/editeur.html" class="btn">Ouvrir</a></div>
    </div>
</body>
</html>