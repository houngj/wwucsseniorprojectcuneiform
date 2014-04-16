<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/functions.php';
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
    debugLog(["FILE"  => "REST/names_dates.php",
              "QUERY" => $search,
              "CACHE" => "HIT",
              "TIME"  => microtime(true) - $start_time]);
    exit();
}

$pdo = getConnection();

$subQuery = "SELECT t.tablet_id\n" .
            "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
            "WHERE MATCH(ts.section_text) AGAINST('$search' IN BOOLEAN MODE)\n" .
            "GROUP BY t.tablet_id\n";

$query = "SELECT n.*, cy.*, COUNT(*) as count FROM\n" .
            "`canonical_year` cy NATURAL JOIN `year_reference` yr NATURAL JOIN `text_section` ts NATURAL JOIN `tablet_object` tbo NATURAL JOIN `tablet` t NATURAL JOIN `name_reference` nr NATURAL JOIN `name` n \n" .
            "WHERE t.tablet_id IN (\n$subQuery\n)\n" .
            "GROUP BY nr.name_id, cy.canonical_year_id\n" .
            "ORDER BY nr.name_id ASC";

$outarray = array(array('date'));

set_time_limit(10000);
$result = $pdo->query($query);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $row_index = (int) $row['canonical_year_id'];
    $col_index = array_search($row['name_text'], $outarray[0]);
    if (!$col_index) {
        array_push($outarray[0], $row['name_text']);
        for ($i = 1; $i < count($outarray); ++$i) {
            array_push($outarray[$i], null);
        }
        $col_index = array_search($row['name_text'], $outarray[0]); // todo: inefficeint
    }
    while ($row_index >= count($outarray)) {
        array_push($outarray, array_fill(0, count($outarray[0]), null));
    }
    $outarray[$row_index][0] = $row['abbreviation'];
    $outarray[$row_index][$col_index] = (int) $row['count'];
}
$return_value = json_encode($outarray);
echo $return_value;
if($cache !== FALSE) {
    $cache->set($cache_key, $return_value);
}
debugLog(["FILE"  => "REST/names_dates.php",
          "QUERY" => $search,
          "CACHE" => "MISS",
          "TIME"  => microtime(true) - $start_time]);
?>