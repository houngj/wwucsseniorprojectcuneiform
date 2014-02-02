<?php

function newConnection() {
    $host = "localhost";
    $db   = "cuneiform";
    $user = "dingo";
    $pass = "hungry!";
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->exec("SET profiling = 1;");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

?>