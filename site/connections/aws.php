<?php

function getConnection() {
    $host = "wwu-cuneiform.co5tt9crocw2.us-west-2.rds.amazonaws.com";
    $db   = "cuneiform";
    $user = "dingo";
    $pass = "hungry!";
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->exec("SET profiling = 1;");
    return $pdo;
}

?>