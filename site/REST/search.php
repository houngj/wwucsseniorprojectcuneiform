<?php
include("../connections/connection.php");
include("../tools/functions.php");
$start_time = microtime(true);
if (!isset($_GET['search'])) {
    http_response_code(400);
    die("search isn't set");
}
$search = makeQuery($_GET['search']);
// TODO change to prepared statement
$sql = "SELECT SQL_CALC_FOUND_ROWS t.tablet_id, SUM(MATCH(ts.section_text) AGAINST('$search')) as score\n" .
       "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
       "WHERE MATCH(ts.section_text) AGAINST('$search' IN BOOLEAN MODE)\n" .
       "GROUP BY t.tablet_id\n" .
       "ORDER BY `score` DESC\n";

$pdo                = getConnection();
$search_result      = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$found_rows_result  = $pdo->query("SELECT FOUND_ROWS();")->fetch();
$num_results        = $found_rows_result["FOUND_ROWS()"];

echo json_encode(["num_results" => $num_results,
                  "results"     => $search_result]);
debugLog(["FILE"  => "REST/search.php",
          "QUERY" => $search,
          "TIME"  => microtime(true) - $start_time]);
?>
