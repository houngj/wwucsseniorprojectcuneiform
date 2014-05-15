<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/archive.php';

if (!isset($_GET['action'])) {
    echo json_encode(['success' => false,
                      'error'   => "\$_GET['action'] is not set."]);
    http_response_code(400);
    exit;
}

if ($_GET['action'] === 'new_archive' && isset($_GET['title'])) {
    $pdo = getConnection();
    $title = $_GET['title'];
    $archive = Archive::addArchive($title, $pdo);
    echo json_encode(['success'    => true,
                      'archive_id' => $archive->getID()]);
    exit;
}

if ($_GET['action'] === 'add_tablet' && isset($_GET['archive_id']) && isset($_GET['tablet_group_id'])) {
    $pdo = getConnection();
    $archive = new Archive($_GET['archive_id'], $pdo);
    if ($archive->contains($_GET['tablet_group_id'])) {
        echo json_encode(['success' => false,
                          'error'   => "Tablet already exists in archive."]);
    } else {
        $archive->addTablet($_GET['tablet_group_id'], $pdo);
        echo json_encode(['success' => true]);
    }
    exit;
}

if ($_GET['action'] === 'remove_tablet' && isset($_GET['archive_id']) && isset($_GET['tablet_group_id'])) {
    $pdo = getConnection();
    $archive = new Archive($_GET['archive_id'], $pdo);
    $archive->removeTablet($_GET['tablet_group_id'], $pdo);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false,
                  'error'   => "Invalid action specified"]);
http_response_code(400);