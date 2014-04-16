<?php
include_once '../connections/connection.php';
include_once '../tools/tablet.php';
$pdo = getConnection();

if (isset($_GET['tablet_id']) && ctype_digit($_GET['tablet_id']) && $_GET['tablet_id'] > 0) {
    $tablet_id = $_GET['tablet_id'];
    $tablet = new Tablet($tablet_id, $pdo);
    echo json_encode($tablet, JSON_PRETTY_PRINT);
} else {
    http_response_code(400);  // Bad Request
    die();
}
?>
