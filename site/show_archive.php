<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/archive.php';

$pdo = getConnection();

if (!User::isLoggedIn()) {
    die("User must be logged in.");
}

if (!isset($_GET['archive_id'])) {
    die("\$_GET['archive_id'] must be set");
}

try {
    $archive = new Archive($_GET['archive_id'], $pdo);
} catch (ArchiveException $e) {
    // Archive doesn't exist, go home.
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>WWU Cuneiform | Search</title>
        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom styles for this template -->
        <link href="css/dashboard.css" rel="stylesheet">
        <!-- Minified jquery -->
        <script src="js/jquery.min.js"></script>
        <!-- Google jsapi, for google charts api. Must be at start of page -->
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>

        <script type="text/javascript">
            // Loads Google chart api, must be at start of page
            google.load("visualization", "1", {packages: ["corechart"]});
        </script>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.php">WWU Cuneiform</a>
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="index.php">Home</a></li>
                    </ul>
                </div>
                <div class="navbar-collapse collapse">
                    <?php if (User::isLoggedIn()) { ?>
                        <!-- User dashboard -->
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href=""><span class="glyphicon glyphicon-user"></span> <?php echo User::getName(); ?></a></li>
                            <li><a href="logout.php"><span class="glyphicon glyphicon-off"></span> Logout</a></li>
                        </ul>
                    <?php } else { ?>
                        <!-- Login form -->
                        <form method="POST" id="login" class="navbar-form navbar-right">
                            <!-- ul must be inside form to display properly -->
                            <ul class="nav navbar-nav navbar-right">
                                <li><input name="username" type="text" placeholder="username" class="form-control" /></li>
                                <li><input name="password" type="password" placeholder="password" class="form-control" /></li>
                                <li><button type="submit" class="btn btn-default">Log In</button></li>
                            </ul>
                        </form>
                    <?php } ?>
                </div><!--/.nav-collapse -->
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3 col-md-2 sidebar">
                    <!-- Begin sidebar -->
                    <h4>My Virtual Archives</h4>
                    <?php
                    if (User::isLoggedIn()) {
                        User::printArchives($pdo, true);
                    } else {
                        echo "<p>Log in to use Virtual Archives</p>\n";
                    }
                    ?>
                    <!--- End sidebar --->
                </div>
                <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                    <!-- Begin main content div -->
                    <h1><?php echo $archive->getName(); ?></h1>
                    <div id="search-results"><!-- Begin search results -->
                        <div>
                            <?php
                            $archive->displayFullTablets($pdo);
                            ?>
                        </div>
                    </div><!-- End search results -->
                </div><!--/.container -->
            </div>
        </div>
        <!--Bootstrap core JavaScript
        ================================================== -->
        <!--Placed at the end of the document so the pages load faster -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/site.js"></script>
    </body>
</html>
