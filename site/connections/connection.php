<?php
/*
 * USAGE:
 * use 'include("connections/connection.php");' in all files that need database
 * connection. Use getConnection() to get a new database connection.  To change
 * which connection is used, change the include here.
 */

// include("aws.php");
include("localhost.php");

function getConnection() {
    return newConnection();
}

?>