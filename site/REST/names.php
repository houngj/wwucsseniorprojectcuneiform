<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/functions.php';

if (!isset($_GET['search'])) {
    echo json_encode("GET['search'] is not set");
    http_response_code(400);
    exit;
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

$subQuery = 'SELECT tg.tablet_group_id
             FROM `tablet_group` tg NATURAL JOIN `text_section` ts
             WHERE MATCH(ts.text_section_text) AGAINST(:search IN BOOLEAN MODE)
             GROUP BY tg.tablet_group_id';

$query =    'SELECT n.name_text, COUNT(*) AS count
             FROM `text_section` ts NATURAL JOIN `name_reference` nr NATURAL JOIN `name` n
             WHERE ts.tablet_group_id IN
             (' . $subQuery . ')
             GROUP BY n.name_id
             ORDER BY count DESC';

$statement = $pdo->prepare($query);
$statement->execute([':search' => $search]);
$return_value = json_encode($statement->fetchAll(PDO::FETCH_ASSOC));
echo $return_value;
if ($cache !== FALSE) {
    $cache->set($cache_key, $return_value);
}
debugLog(["FILE"  => "REST/names.php",
          "QUERY" => $search,
          "CACHE" => "MISS",
          "TIME"  => microtime(true) - $start_time]);
?>
