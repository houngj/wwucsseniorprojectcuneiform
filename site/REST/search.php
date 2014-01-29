<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../connections/aws.php");

if (isset($_GET['search'])) {
    $search = htmlspecialchars(trim($_GET['search']));
    $query = "";
    foreach(explode(" ", $search) as $term) {
        $query .= '+"' . $term . '"';
    }
} else {
    http_response_code(400); // Bad Request
}

if (isset($_GET['page']) && ctype_digit($_GET['page']) && $_GET['page'] > 0) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

$start_limit = ($page - 1) * 10;
// TODO change to prepared statement
$sql = "SELECT SQL_CALC_FOUND_ROWS t.tablet_id, SUM(MATCH(ts.section_text) AGAINST('$query')) as score\n" .
       "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
       "WHERE MATCH(ts.section_text) AGAINST('$query' IN BOOLEAN MODE)\n" .
       "GROUP BY t.tablet_id\n" .
       "ORDER BY `score` DESC\n" .
       "LIMIT $start_limit,10";

$pdo                = getConnection();
$search_result      = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$found_rows_result  = $pdo->query("SELECT FOUND_ROWS();")->fetch();
$num_results        = $found_rows_result["FOUND_ROWS()"];

echo json_encode(["num_results" => $num_results,
                  "results"     => $search_result     ],
                 JSON_PRETTY_PRINT);

?>
