<?php

function dumpResultTable($result) {
    echo "<table width=100%><tr>";
    foreach ($result[0] as $key => $value) {
        echo "<th>", $key, "</th>";
    }
    echo "</tr>";
    foreach ($result as $row) {
        echo "<tr>";
        foreach ($row as $colum) {
            echo "<td>", $colum, "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

function printInDiv($string) {
    echo "<div class='panel panel-default'>";
    echo $string;
    echo "</div>";
}

function debugLog($array) {
    $log = "================================================================\n";
    foreach ($array as $key => $value) {
        $log = sprintf("%s%-10s %s\n", $log, $key . ":", $value);
    }
    error_log($log, 3, sys_get_temp_dir() . DIRECTORY_SEPARATOR . "php-debug.log");
}

function makeQuery($search_value) {
    $search = htmlspecialchars(trim($search_value));
    $query = "";
    foreach (explode(" ", $search) as $term) {
        $query .= '+"' . $term . '"';
    }
    return $query;
}

?>
