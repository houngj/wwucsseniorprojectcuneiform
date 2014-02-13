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

?>
