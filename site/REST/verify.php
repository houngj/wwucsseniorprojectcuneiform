<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';

if (!isset($_GET['action'])) {
    // TODO, add more meaningful output
    echo json_encode(false);
    exit;
}

if ($_GET['action'] === 'verify_username' && isset($_GET['username'])) {
    $username = $_GET['username'];
    $pdo = getConnection();
    echo User::isUsernameAvailable($pdo, $username);
    exit;
}

echo json_encode("Invalid action specified");
http_response_code(400);