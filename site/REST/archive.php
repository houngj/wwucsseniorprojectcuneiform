<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/archive.php';

if (!isset($_GET['action'])) {
    // TODO, add more meaningful output
    echo json_encode("\$_GET['action'] is not set.");
    http_response_code(400);
    exit;
}

if ($_GET['action'] === 'new_archive' && isset($_GET['title'])) {
    $pdo = getConnection();
    $title = $_GET['title'];
    $archive = Archive::addArchive($title, $pdo);
    // TODO, add more meaningful output
    echo json_encode($archive->getID());
    exit;
}

if ($_GET['action'] === 'add_tablet' && isset($_GET['archive_id']) && isset($_GET['tablet_group_id'])) {
    $pdo = getConnection();
    $archive = new Archive($_GET['archive_id'], $pdo);
    $archive->addTablet($_GET['tablet_group_id'], $pdo);
    // TODO, add more meaningful output
    echo json_encode(true);
    exit;
}

echo json_encode("Invalid action specified");
http_response_code(400);