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
?>