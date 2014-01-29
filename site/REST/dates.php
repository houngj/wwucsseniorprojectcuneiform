<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../connections/aws.php");

$search = htmlspecialchars(trim($_GET['search']));
$query = "";
foreach (explode(" ", $search) as $term) {
    $query .= '+"' . $term . '"';
}

$pdo = getConnection();

$subQuery = "SELECT t.tablet_id FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts WHERE MATCH(ts.section_text) AGAINST('$query' IN BOOLEAN MODE) GROUP BY t.tablet_id";

$query = "SELECT cy.*, COUNT(*) as count\n" .
        "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts NATURAL JOIN `year_reference` yr NATURAL JOIN `canonical_year` cy\n" .
        "WHERE t.tablet_id IN (\n$subQuery\n)\n" .
        "GROUP BY cy.canonical_year_id;";

$result = $pdo->query($query);

echo json_encode($result->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>