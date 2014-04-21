<?php

function expandWidth($width) {

}

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

$subQuery = 'SELECT tg.tablet_group_id
             FROM `tablet_group` tg NATURAL JOIN `text_section` ts
             WHERE MATCH(ts.text_section_text) AGAINST(:search IN BOOLEAN MODE)
             GROUP BY tg.tablet_group_id';

$query = 'SELECT n.*, cy.*, COUNT(*) as count FROM
          `canonical_year` cy NATURAL JOIN `year_reference` yr NATURAL JOIN `text_section` ts NATURAL JOIN `name_reference` nr NATURAL JOIN `name` n
          WHERE ts.tablet_group_id IN
          (' . $subQuery . ')
          GROUP BY nr.name_id, cy.canonical_year_id
          ORDER BY nr.name_id ASC';

// First row of $outarray contains all the names,
// First column of $outarray contains all the dates

$outarray = array(array('date'));

set_time_limit(10000);
$statement = $pdo->prepare($query);
$statement->execute([':search' => $search]);

while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $row_index = (int) $row['canonical_year_id'];
    $col_index = array_search($row['name_text'], $outarray[0]);
    if (!$col_index) {
        // Put name at end of first row
        $outarray[0][] = $row['name_text'];
        // Extend rows
        for ($i = 1; $i < count($outarray); ++$i) {
            $outarray[$i][] = null;
        }
        $col_index = count($outarray[0]) - 1;
    }
    // Add rows until we're up to $row_index
    while ($row_index >= count($outarray)) {
        $outarray[] = array_fill(0, count($outarray[0]), null);
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