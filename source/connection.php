<?php
function getConnection(): mysqli {
    $servername = "localhost";
    $username = "SITE";
    $password = "";
    $dbname = "DocKey";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function getDocContents(string $id): string {
    $conn = getConnection();
    $safeId = $conn->real_escape_string($id);
    $sql = "SELECT contents FROM doc WHERE id='$safeId'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['contents'] ?? '';
    }

    $conn->close();
    return '';
}

function updateDocContents(string $id, string $contents): string {
    $conn = getConnection();

    $id = $conn->real_escape_string($id);
    $Contents = $conn->real_escape_string($contents);
    $sql = "UPDATE doc 
            SET contents='$Contents', updated_at=NOW() 
            WHERE id='$id'";

    if ($conn->query($sql) === true) {
        $conn->close();
        return '';
    }

    $error = $conn->error;
    $conn->close();
    return $error;
}


function SESSION($nom_utilisateur) {

    $conn = getConnection();


    $res = $conn->query("SELECT id FROM utilisateur WHERE nom = '$nom_utilisateur'");
    
    $id_utilisateur = null;
    if ($res && $row = $res->fetch_assoc()) {

        $id_utilisateur = $row['id']; 
    }
    
    $conn->close(); 


    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    

    $_SESSION['id'] = $id_utilisateur;
    $_SESSION['nom'] = $nom_utilisateur;
}