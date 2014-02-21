<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include 'connections/connection.php';
include 'tools/tablet.php';
include 'tools/functions.php';

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
    foreach (explode(" ", $search) as $term) {
        $query .= '+"' . $term . '"';
    }
}

function buildQuery() {
    global $page, $search, $query;
    $start_limit = ($page - 1) * 10;

    if (isset($_GET['regex_submit'])) {
        $sql = "SELECT SQL_CALC_FOUND_ROWS t.tablet_id\n" .
               "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
               "WHERE ts.section_text REGEXP '$search'\n" .
               "GROUP BY t.tablet_id\n" .
               "LIMIT $start_limit,10";
    } else {
        $sql = "SELECT SQL_CALC_FOUND_ROWS t.tablet_id, SUM(MATCH(ts.section_text) AGAINST('$query')) as score\n" .
               "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
               "WHERE MATCH(ts.section_text) AGAINST('$query' IN BOOLEAN MODE)\n" .
               "GROUP BY t.tablet_id\n" .
               "ORDER BY `score` DESC\n" .
               "LIMIT $start_limit,10";
    }
    echo "<pre style='text-align:left'>", $sql, "</pre>";
    return $sql;
}

function printTablet($tablet_id) {
    global $pdo;
    $tablet = new Tablet($tablet_id, $pdo);
    $tablet->display();
}

function getResults() {
    global $pdo, $numResults;
    $sql = buildQuery();
    $result = $pdo->query($sql);
    $foundRows = $pdo->query("SELECT FOUND_ROWS();")->fetch();
    $numResults = $foundRows["FOUND_ROWS()"];
    return $result;
}

function printResults($result) {
    global $numResults;
    echo "<p>Returned $numResults results</p>";
    while ($row = $result->fetch()) {
        printTablet($row['tablet_id']);
    }
}

function printPagination() {
    global $results_per_page, $page, $search, $php_self, $numResults;

    $lastPage = (int) (($numResults + $results_per_page - 1) / $results_per_page);
    $baseUrl = $php_self . "?search=" . $search;

    if(isset($_GET['regex_submit'])) {
        $baseUrl = $baseUrl . "&regex_submit=" . $_GET['regex_submit'];
    }

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
       
        <link href="css/jquery.tagit.css" rel="stylesheet" type="text/css">
        <link href="css/tagit.ui-zendesk.css" rel="stylesheet" type="text/css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>

        <!-- The real deal -->
        <script src="js/tag-it.js" type="text/javascript" charset="utf-8"></script>
        
        <script>
                jQuery(document).ready(function($){
                    $('#tags').tagit({
                        // This will make Tag-it submit a single form value, as a comma-delimited field.
                        singleField: true,
                        singleFieldNode: $('#search'),
                        singleFieldDelimiter: ' ',
                        afterTagRemoved: function() {
                            $('#searchform').submit(); //Submit the form
                        }
                    });
                });
 
        </script>
        
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
                <form name="searchform" id="searchform" action="<?php echo $php_self; ?>" method="get">
                    <div class="input-group">
                        <input type="hidden" name="search" id="search" value="<?php if (isset($search)) {echo $search;} ?>">
                        <ul id="tags" class="form-control"></ul>
                        <div class="input-group-btn">
                            <input type="submit" class="btn btn-default" tabindex="-1" name="submit_form" value="Search" />
                            <input type="submit" class="btn btn-default" tabindex="-1" name="regex_submit" value="Regex Search" />
                        </div>
                    </div><!-- /input-group -->
                </form>
                <div id="tablet-output">
                    <?php
                    if (isset($search)) {
                        $result = getResults();
                        printPagination();
                    ?>
                    <div>
                        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#date-modal">Date Distribution</button>
                        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#name-modal">Name Distribution</button>
                    </div>
                    <?php
                        printResults($result);
                        printPagination();
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
        <script src="js/site.js"></script>
        <script src="https://www.google.com/jsapi"></script>
        <script type="text/javascript">
            google.load("visualization", "1", {packages: ["corechart"]});
            if (document.getElementById("search").value.length !== 0) {
                graphDates(document.getElementById("search").value);
                graphNames(document.getElementById("search").value);
            }
        </script>



        <!-- Date Modal -->
        <div class="modal fade" id="date-modal" tabindex="-1" role="dialog" aria-labelledby="date-modalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 1100px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="date-modalLabel">Date Distribution</h4>
                    </div>
                    <div class="modal-body">
                        <div id="date_chart_div"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Name Modal -->
        <div class="modal fade" id="name-modal" tabindex="-1" role="dialog" aria-labelledby="name-modalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 1100px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="date-modalLabel">Name Distribution</h4>
                    </div>
                    <div class="modal-body">
                        <div id="name_chart_div"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


<?php
$result = $pdo->query("SHOW PROFILES;")->fetchAll(PDO::FETCH_ASSOC);
if (empty($result) == false) {
    dumpResultTable($result);
}
?>
