<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../tools/archive.php';
include_once '../tools/user.php';
include_once '../connections/connection.php';

if (!isset($_GET['action'])) {
    // TODO, add more meaningful output
    echo json_encode(false);
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

if ($_GET['action'] === 'add_tablet' && isset($_GET['archive_id']) && isset($_GET['tablet_id'])) {
    $pdo = getConnection();
    $archive = new Archive($_GET['archive_id'], $pdo);
    $archive->addTablet($_GET['tablet_id'], $pdo);
    // TODO, add more meaningful output
    echo json_encode(true);
    exit;
}

