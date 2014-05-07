<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';
// Logout the user
User::logout();
// Redirect to index.php
header("Location: index.php");
