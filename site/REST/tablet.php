<?php

include("../connections/aws.php");
include("../tablet.php");
$pdo = getConnection();

$tabletID = $_GET['tabletID'];

$tablet = new Tablet($tabletID, $pdo);

echo json_encode($tablet, JSON_PRETTY_PRINT);

?>