<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/tablet.php';

class Archive {

    private $id;
    private $name;
    private $tablets;

    static function addArchive($name, PDO $pdo) {
        $sql = "INSERT INTO `archive` (`archive_id`, `user_id`, `archive_name`) VALUES (NULL, :user_id, :archive_name)";
        $statement = $pdo->prepare($sql);
        if (!$statement->execute([':user_id' => USER::getUserId(), ':archive_name' => $name])) {
            var_dump($pdo->errorInfo());
            var_dump($statement->errorInfo());
            die("error");
        }
        return new Archive($pdo->lastInsertId(), $pdo);
    }

    public function __construct($id, PDO $pdo) {
        // The name of the archive is probably known when looking up the user's
        // archives, and could should be able to be set without a database call.
        // Since PHP does not allow multiple constructors, this could be changed
        // to a Factory pattern, with static methods that build the Archive from
        // just an id or and id and name. This way is more general, though.
        $this->id = $id;
        $this->fetchArchiveData($pdo); // Sets $this->name;
        $this->tablets = $this->fetchArchiveTablets($pdo);
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    private function fetchArchiveData(PDO $pdo) {
        $sql = "SELECT * FROM `archive` WHERE `archive_id` = :archive_id";
        $statement = $pdo->prepare($sql);
        // Bind archive_id to $this->id
        $statement->execute([':archive_id' => $this->id]);
        // Get data, must be only one row
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['archive_name'];
        // While we have the data, verify the logged in user is the owner of the archive
        if ($row['user_id'] != User::getUserId()) {
            throw new Exception("Archive doesn't belong to logged in user");
        }
    }

    private function fetchArchiveTablets(PDO $pdo) {
        $sql = "SELECT * from `archive_tablet` NATURAL JOIN `tablet_group` WHERE `archive_id` = :archive_id";
        $statement = $pdo->prepare($sql);
        // Bind archive_id to $this->id
        $statement->execute([':archive_id' => $this->id]);
        // Get data, must be only one row
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            $output[] = ['tablet_id' => $row['tablet_group_id'], 'tablet_group_name' => $row['tablet_group_name']];
        }
        return $output;
    }

    public function addTablet($tabletGroupID, PDO $pdo) {
        $sql = "INSERT INTO `archive_tablet` (`archive_tablet_id`, `archive_id`, `tablet_group_id`) VALUES (NULL, :archive_id, :tablet_group_id)";
        $statement = $pdo->prepare($sql);
        if (!$statement->execute([':archive_id' => $this->id, ':tablet_group_id' => $tabletGroupID])) {
            die($statement->errorInfo());
        }
        return true;
    }

    public function display($links = false) {
        $tablets_count = count($this->tablets);
        ?>
        <li><span class="glyphicon glyphicon-minus-sign list-minimizer"></span>
            <a href="show_archive.php?archive_id=<?php echo $this->id; ?>"> <?php echo "$this->name ($tablets_count)"; ?></a>
            <ul>
                <?php
                foreach ($this->tablets as $tablet) {
                    $tablet_id = $tablet['tablet_id'];
                    $tablet_name = $tablet['tablet_group_name'];
                    if ($links) {
                        echo "<li><a href=\"#tablet-$tablet_id\">", $tablet_name,  "</a></li>\n";
                    } else {
                        echo "<li>", $tablet_name, "</li>\n";
                    }
                }
                ?>
            </ul>
        </li>
        <?php
    }

    public function displayFullTablets($pdo) {
        foreach ($this->tablets as $tablet) {
            $tablet = new TabletGroup($tablet['tablet_id'], $pdo);
            $tablet->display(array());
        }
    }

}
?>
