<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../connections/aws.php");

$pdo = getConnection();

if (isset($_GET['search'])) {
    $search = htmlspecialchars(trim($_GET['search']));
    $query = "";
    foreach (explode(" ", $search) as $term) {
        $query .= '+"' . $term . '"';
    }

    $subQuery = "SELECT t.tablet_id\n" .
                "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
                "WHERE MATCH(ts.section_text) AGAINST('$query' IN BOOLEAN MODE)\n" .
                "GROUP BY t.tablet_id";

    $query = "SELECT n.name_text, COUNT(*) AS count\n" .
             "FROM `tablet` t NATURAL JOIN `name_reference` nr NATURAL JOIN `name` n\n" .
             "WHERE t.tablet_id IN (\n$subQuery\n)\n" .
             "GROUP BY n.name_id\n" .
             "ORDER BY count DESC";

    $result = $pdo->query($query);
    echo json_encode($result->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
} else if (isset($_GET['add_name'])) {
    $name_to_add = $_GET['add_name'];
    $query = "INSERT INTO `name` (name_text) VALUES ('$name_to_add')";
    $pdo->exec($query);

    $query = "INSERT INTO `name_reference` (tablet_id, name_id)\n" .
             "SELECT t.tablet_id, n.name_id\n" .
             "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts, `name` n\n" .
             "WHERE `section_text` REGEXP '(^|[[:blank:]]+)$name_to_add([[:blank:]]+|$)' AND n.name_text = '$name_to_add'\n" .
             "GROUP BY t.tablet_id";
    echo "\n<pre>\n", $query, "\n</pre>\n";
    $pdo->exec($query);
} else {
    http_response_code(400);
    die();
}
?>