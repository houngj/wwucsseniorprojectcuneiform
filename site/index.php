<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function dumpResult($result) {
    echo "<table width=100%>";

    echo "<tr>";
    foreach ($result[0] as $key => $value) {
        echo "<th>" . $key . "</th>";
    }
    echo "</tr>";
    foreach ($result as $row) {
        echo "<tr>";
        foreach ($row as $colum) {
            echo "<td>" . $colum . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

include 'tablet.php';
include 'connections/aws.php';

$pdo = getConnection();
$results_per_page = 10;

$php_self = $_SERVER['PHP_SELF'];

if (isset($_GET['page']) && ctype_digit($_GET['page']) && $_GET['page'] > 0) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

if (isset($_GET['search'])) {
    $search = htmlspecialchars(trim($_GET['search']));
    $query = "";
    foreach(explode(" ", $search) as $term) {
        $query .= '+"' . $term . '"';
    }
} else {

}

function buildQuery() {
    global $page, $search, $query;
    $start_limit = ($page - 1) * 10;

    $sql = "SELECT t.tablet_id, SUM(MATCH(ts.section_text) AGAINST('$query' IN BOOLEAN MODE)) as score\n" .
            "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
            "GROUP BY t.tablet_id\n" .
            "ORDER BY `score` DESC\n" .
            "LIMIT $start_limit,10";
    return $sql;
}

function divit($string) {
    echo "<div class='panel panel-default'>";
    echo $string;
    echo "</div>";
}

function printTablet($tablet_id) {
    global $pdo;
    $tablet = new Tablet($tablet_id, $pdo);
    $tablet->display();
}

function printResults() {
    global $pdo;
    $sql = buildQuery();
    $result = $pdo->query($sql);
    while ($row = $result->fetch()) {
        echo "<h3>", $row['score'], "</h3>";
        printTablet($row['tablet_id']);
    }
}

function printPagination($numResults) {
    global $results_per_page, $page, $search, $php_self;

    $lastPage = ($numResults + $results_per_page - 1) / $results_per_page;
    $baseUrl = $php_self . "?search=" . $search;

    $minPage = max(1, $page - 2);
    $maxPage = min($minPage + 4, $lastPage);

    echo "<ul class='pagination'>\n";
    echo "  <li><a href='$baseUrl&page=1'>&laquo;</a></li>\n";

    for ($i = $minPage; $i <= $maxPage; $i++) {
        if ($i == $page) {
            echo "  <li class='active'><a href='$baseUrl&page=$i'>$i</a></li>\n";
        } else {
            echo "  <li><a href='$baseUrl&page=$i'>$i</a></li>\n";
        }
    }

    echo "<li><a href='$baseUrl&page=$lastPage'>&raquo;</a></li>\n";
    echo "</ul>\n";
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
        <link href="css/bootstrap.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="css/starter-template.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>

        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="">WWU Cuneiform</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">Home</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>

        <div class="container">
            <div class="starter-template">
                <h1>Tablet Search</h1>
                <form action="<?php echo $php_self; ?>" method="get">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" value="<?php if (isset($search)) echo $search; ?>">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">Go!</button>
                        </span>
                    </div><!-- /input-group -->
                </form>

                <div>
                    <?php
                    if (isset($search)) {
                        printPagination(101, $page);
                        printResults();
                        printPagination(101, $page);
                    }
                    ?>
                </div>
            </div>
        </div><!--/.container -->


        <!--Bootstrap core JavaScript
        ================================================== -->
        <!--Placed at the end of the document so the pages load faster -->
        <script src = "https://code.jquery.com/jquery-1.10.2.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
    </body>
</html>

<?php
$result = $pdo->query("SHOW PROFILE;")->fetchAll();
if (empty($result) == false) {
    dumpResult($result);
}
?>
