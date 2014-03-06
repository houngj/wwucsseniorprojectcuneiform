<?php

include("../connections/connection.php");
include("../tools/functions.php");
$start_time = microtime(true);
if (!isset($_GET['search'])) {
    http_response_code(400);
    die("search isn't set");
}
$search = makeQuery($_GET['search']);
$pdo = getConnection();

$subQuery = "SELECT t.tablet_id\n" .
            "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
            "WHERE MATCH(ts.section_text) AGAINST('$search' IN BOOLEAN MODE)\n" .
            "GROUP BY t.tablet_id";

$query   =  "SELECT cy.*, COUNT(*) as count\n" .
            "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
            "NATURAL JOIN `year_reference` yr NATURAL JOIN `canonical_year` cy\n" .
            "WHERE yr.confidence > 0 AND t.tablet_id IN (\n$subQuery\n)\n" .
            "GROUP BY cy.canonical_year_id;";

$result = $pdo->query($query);
echo json_encode($result->fetchAll(PDO::FETCH_ASSOC));
debugLog(["FILE"  => "REST/dates.php",
          "QUERY" => $search,
          "TIME"  => microtime(true) - $start_time]);
?>
