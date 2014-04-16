<?php
include_once '../connections/connection.php';
include_once '../tools/functions.php';
if (!isset($_GET['search'])) {
    http_response_code(400);
    die("search isn't set");
}
$start_time = microtime(true);
$search = makeQuery($_GET['search']);
$cache = getMemcached();
$cache_key = __FILE__ . $search;

if ($cache !== FALSE && ($return_value = $cache->get($cache_key)) !== FALSE) {
    echo $return_value;
    debugLog(["FILE"  => "REST/names.php",
              "QUERY" => $search,
              "CACHE" => "HIT",
              "TIME"  => microtime(true) - $start_time]);
    exit();
}

$pdo = getConnection();

$subQuery =     "SELECT t.tablet_id\n" .
                "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
                "WHERE MATCH(ts.section_text) AGAINST('$search' IN BOOLEAN MODE)\n" .
                "GROUP BY t.tablet_id";

$query =     "SELECT n.name_text, COUNT(*) AS count\n" .
             "FROM `tablet` t NATURAL JOIN `name_reference` nr NATURAL JOIN `name` n\n" .
             "WHERE t.tablet_id IN (\n$subQuery\n)\n" .
             "GROUP BY n.name_id\n" .
             "ORDER BY count DESC";

$result = $pdo->query($query);
$return_value = json_encode($result->fetchAll(PDO::FETCH_ASSOC));
echo $return_value;
if ($cache !== FALSE) {
    $cache->set($cache_key, $return_value);
}
debugLog(["FILE"  => "REST/names.php",
          "QUERY" => $search,
          "CACHE" => "MISS",
          "TIME"  => microtime(true) - $start_time]);
?>
