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

function getMemcached() {
    if (!extension_loaded("memcached")) {
        return false;
    }
    $m = new Memcached();
    return ($m->addServer('localhost', 11211)) ? ($m) : (false);
}

?>