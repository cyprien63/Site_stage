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


function deconnexion() {
session_start();
session_unset();
session_destroy();

header("Location: /index.php"); 
exit();

}

function obtenirPermModeDoc($id_doc) {
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $mode = 'public';
    try {
        $res = $conn->query("SELECT perm_mode FROM doc WHERE id = '$id'");
        if ($res && $row = $res->fetch_assoc()) $mode = $row['perm_mode'] ?? 'public';
    } catch (mysqli_sql_exception $e) {
        // column not added yet
    }
    $conn->close();
    return $mode;
}

function definirPermModeDoc($id_doc, $mode) {
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $conn->query("UPDATE doc SET perm_mode = '$mode' WHERE id = '$id'");
    $conn->close();
}

function obtenirPermUtilisateurs($id_doc) {
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $res = $conn->query("SELECT p.user_id, u.nom, p.mode FROM doc_permissions p JOIN utilisateur u ON u.id = p.user_id WHERE p.doc_id = '$id'");
    $users = [];
    if ($res) while ($row = $res->fetch_assoc()) $users[] = $row;
    $conn->close();
    return $users;
}

function definirPermUtilisateur($id_doc, $id_utilisateur, $mode) {
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $uid = (int)$id_utilisateur;
    $conn->query("INSERT INTO doc_permissions (doc_id, user_id, mode) VALUES ('$id', $uid, '$mode') ON DUPLICATE KEY UPDATE mode = '$mode'");
    $conn->close();
}

function supprimerPermUtilisateur($id_doc, $id_utilisateur) {
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $uid = (int)$id_utilisateur;
    $conn->query("DELETE FROM doc_permissions WHERE doc_id = '$id' AND user_id = $uid");
    $conn->close();
}

function obtenirIdParNom($nom) {
    $conn = getConnection();
    $res = $conn->query("SELECT id FROM utilisateur WHERE nom = '$nom'");
    $id = null;
    if ($res && $row = $res->fetch_assoc()) $id = (int)$row['id'];
    $conn->close();
    return $id;
}

function peutModifierDoc($id_doc, $id_utilisateur) {
    $mode = obtenirPermModeDoc($id_doc);
    if ($mode === 'public') return true;
    if ($id_utilisateur === null) return false;
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $uid = (int)$id_utilisateur;
    $res = $conn->query("SELECT id_compte FROM doc WHERE id = '$id'");
    if ($res && $row = $res->fetch_assoc()) {
        if ((int)$row['id_compte'] === $uid) {
            $conn->close();
            return true;
        }
    }
    $res = $conn->query("SELECT mode FROM doc_permissions WHERE doc_id = '$id' AND user_id = $uid AND mode = 'edit'");
    $can = $res && $res->num_rows > 0;
    $conn->close();
    return $can;
}

function peutVoirDoc($id_doc, $id_utilisateur) {
    $mode = obtenirPermModeDoc($id_doc);
    if ($mode === 'public') return true;
    if ($id_utilisateur === null) return false;
    $conn = getConnection();
    $id = $conn->real_escape_string($id_doc);
    $uid = (int)$id_utilisateur;
    $res = $conn->query("SELECT id_compte FROM doc WHERE id = '$id'");
    if ($res && $row = $res->fetch_assoc()) {
        if ((int)$row['id_compte'] === $uid) {
            $conn->close();
            return true;
        }
    }
    $res = $conn->query("SELECT mode FROM doc_permissions WHERE doc_id = '$id' AND user_id = $uid");
    $can = $res && $res->num_rows > 0;
    $conn->close();
    return $can;
}