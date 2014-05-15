<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// <rant>
// When PHP `include`s or `require`s a file, it searches in the the directories
// in the php.ini variable `include_path`, then it searches paths relative to
// the FILE IT'S EXECUTING. This seems like a fine and dandy choice, until you
// have files in subdirecories that are including other files, then all the
// paths must be relative to all the possible files being executed.  Which isn't
// posible with our ./ and ./REST dirs.  So we have to resort to absolute paths.
// Which means site (the connections and tools, at least,) must be placed in the
// document root of the server, e.g. /var/www/tools, not /var/www/cunei/tools.
// This is bad for portablity, but it's the only idea i have.
// </rant>

require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/archive.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/search.php';

$pdo = getConnection();

if (isset($_POST['username']) && isset($_POST['password'])) {
// If the user has submitted the login form, try to log in.
    User::login($pdo, $_POST['username'], $_POST['password'])
            or die("Invalid username or password");
}

if (isset($_GET['search'])) {
    $search = new Search($_GET['search'], $pdo);
}

// Check if the page is specified, and that it's a positive integer
if (isset($_GET['page']) && ctype_digit($_GET['page']) && $_GET['page'] > 0) {
    $page = $_GET['page'];
} else {
    $page = 1;
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
                        <?php if (! User::isLoggedIn()) { ?>
                            <li class="active"><a href="register.php">Register</a></li>
                        <?php } ?>
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
                        User::printArchives($pdo);
                    } else {
                        echo "<p>Log in to use Virtual Archives</p>\n";
                    }
                    ?>
                    <!--- End sidebar --->
                </div>
                <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                    <!-- Begin main content div -->
                    <h1>Tablet Search</h1>
                    <form method="get">
                        <div class="input-group"><!-- Begin search form -->
                            <input type="text" name="search" id="search" class="form-control" value="<?php
                            if (isset($search)) {
                                echo $search->getSearch();
                            }
                            ?>">
                            <div class="input-group-btn">
                                <input type="submit" class="btn btn-default" name="submit_form" value="Search" />
                                <input type="submit" class="btn btn-default" name="regex_submit" value="Regex Search" />
                            </div>
                        </div><!-- /input-group -->
                    </form><!-- End search form -->
                    <?php if (isset($search)) { ?>
                        <div id="search-results"><!-- Begin search results -->
                            <hr>
                            <div>
                                <ul id="tabs-nav" class="nav nav-tabs">
                                    <li class="active"><a href="#tablet-result" data-toggle="tab">Tablet Results (<?php echo $search->getResultsCount(); ?>)</a></li>
                                    <li><a href="#date-distribution" data-toggle="tab">Date Distribution</a></li>
                                    <li><a href="#name-distribution" data-toggle="tab">Name Distribution</a></li>
                                    <li><a href="#attestation-graph" data-toggle="tab">Attestation Graph</a></li>
                                </ul>
                                <div id="tabs-content" class="tab-content">
                                    <div class="tab-pane fade in active" id="tablet-result"><!-- Begin tablet results -->
                                        <?php
                                        $search->printResults($page);
                                        ?>
                                    </div><!-- End tablet results -->
                                    <div class="tab-pane fade" id="date-distribution"><!-- Begin date distribution -->
                                        <!-- Contents drawn by js function graphDates -->
                                    </div><!-- End date distribution -->
                                    <div class="tab-pane fade" id="name-distribution"><!-- Begin name distribution -->
                                        <!-- Contents drawn by js function graphNames -->
                                    </div><!-- End name distribution -->
                                    <div class="tab-pane fade" id="attestation-graph"><!-- Begin attestation graph -->
                                        <!-- Contents drawn by js function graphAttestation -->
                                    </div><!-- End attestation graph -->
                                </div><!-- end tabs-content -->
                            </div>
                        </div><!-- End search results -->
                    <?php } ?>
                </div><!--/.container -->
            </div>
        </div>
        <!--Bootstrap core JavaScript
        ================================================== -->
        <!--Placed at the end of the document so the pages load faster -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/site.js"></script>
        <script type="text/javascript">
            if (document.getElementById("search").value.length !== 0) {
                graphDates(document.getElementById("search").value);
                graphNames(document.getElementById("search").value);
                graphAttestation(document.getElementById("search").value);
            }
        </script>
    </body>
</html>
