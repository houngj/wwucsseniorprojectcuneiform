<?php

function getConnection() {
    $host = "localhost";
    $db   = "cuneiform";
    $user = "dingo";
    $pass = "hungry!";
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->exec("SET profiling = 1;");
    return $pdo;
}

?>