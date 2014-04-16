<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/tablet.php';

class Search {

    private static $resultsPerPage = 10;
    private $search;
    private $regex;
    private $pdo;
    private $query;
    private $termlist = array();
    private $results;

    public function __construct($search, $pdo, $regex = false) {
        $this->search = htmlspecialchars($search);
        $this->pdo = $pdo;
        $this->regex = $regex;
        $this->buildQuery();
        $this->results = $this->fetchResults();
    }

    function getSearch() {
        return $this->search;
    }

    function getResultsCount() {
        return count($this->results);
    }

    private function buildQuery() {
        // MySQL's full text indexing uses + and - to denote whether terms
        // should be present and not present.  To get around our words having
        // a '-' in them, we need to surround the whole word in double quotes
        // and prefix it with +. Ex. +"word" or +"ku3-babbar"
        $this->query = "";
        foreach (explode(" ", $this->search) as $term) {
            $this->query .= '+"' . $term . '"';
            $this->termlist[] = $term;
        }
    }

    function fetchResults() {
        // TODO: Possibly add caching, may require not using prepared statements
        if ($this->regex) {
            $sql = "SELECT t.tablet_id\n" .
                    "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
                    "WHERE ts.section_text REGEXP :search\n" .
                    "GROUP BY t.tablet_id\n";
            $statement = $this->pdo->prepare($sql);
            // Map ':search' to $this->search for regex
            $statement->execute([':search' => $this->search]);
            // Get results
            $result = $statement->fetchAll();
        } else {
            $sql = "SELECT t.tablet_id, SUM(MATCH(ts.section_text) AGAINST(:query)) as score\n" .
                    "FROM `tablet` t NATURAL JOIN `tablet_object` o NATURAL JOIN `text_section` ts\n" .
                    "WHERE MATCH(ts.section_text) AGAINST(:query IN BOOLEAN MODE)\n" .
                    "GROUP BY t.tablet_id\n" .
                    "ORDER BY `score` DESC\n";
            $statement = $this->pdo->prepare($sql);
            // Map ':search' to $this->query for index search
            $statement->execute([':query' => $this->query]);
            // Get results
            $result = $statement->fetchAll();
        }
        return $result;
    }

    function printResults($page) {
        $this->printPagination($page);
        $numResults = count($this->results);
        $start_limit = ($page - 1) * Search::$resultsPerPage;
        $end_limit = min($start_limit + Search::$resultsPerPage, $numResults);
        for ($i = $start_limit; $i < $end_limit; ++$i) {
            $tablet = new Tablet($this->results[$i]['tablet_id'], $this->pdo);
            $tablet->display($this->termlist);
        }
    }

    function printPagination($page) {
        $numResults = count($this->results);
        $baseUrl = $_SERVER['PHP_SELF'] . "?search=" . urlencode($this->search);
        $lastPage = (int) (($numResults + Search::$resultsPerPage - 1) / Search::$resultsPerPage);

        if ($this->regex) {
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

        echo "  <li><a href='$baseUrl&page=$lastPage'>&raquo;</a></li>\n";
        echo "</ul>\n";
    }

}

?>
