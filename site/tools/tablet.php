<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/archive.php';

class TabletGroup implements JsonSerializable {

    private $id;
    private $name;
    private $lang;
    private $objects;
    private $names;

    public function __construct($id, PDO $pdo) {
        $this->id = $id;
        $this->fetchTabletData($pdo); // Sets $this->{name, lang}
        $this->objects = $this->fetchContainers($pdo);
        $this->names = $this->fetchNames($pdo);
    }

    private function fetchTabletData(PDO $pdo) {
        /*$sql = "SELECT * FROM `tablet_group` WHERE `tablet_group_id` = :tablet_group_id";
        $statement = $pdo->prepare($sql);
        $statement->execute([':tablet_group_id' => $this->id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['tablet_group_name'];
        $this->lang = $row['tablet_group_lang'];*/
	
	

	return "";
    }

    private function fetchComments($user_id, $tablet_group_id){
    	
	$host = "localhost";
	$db = "cuneiform";
	$user = "dingo";
	$pass = "hungry!";
	$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
	$pdo->exec("SET profiling = 1");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	$sql = "SELECT * FROM `comments_table` WHERE tablet_group_id = :tablet_group_id AND user_id = :user_id";
	$statement = $pdo->prepare($sql);
	$statement->execute(array(':tablet_group_id' => $tablet_group_id, ':user_id'=> $user_id));
	$row = $statement->fetch();
	if($row == null){
		return "There is Nothing";
	} else {
		return $row[3];
	}
    }
    
    private function fetchNames(PDO $pdo) {
        $sql = "SELECT `name_text` FROM `text_section` NATURAL JOIN `name_reference` NATURAL JOIN `name` WHERE `tablet_group_id` = :tablet_group_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':tablet_group_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        // Converts from an array of arrays of strings to an array of strings.
        return array_column($result, 'name_text');
    }

    private function fetchContainers(PDO $pdo) {
        // First level containers have no parent contianer, their parent container is NULL
        $sql = 'SELECT * FROM `container`
                WHERE `tablet_group_id` = :tablet_group_id AND `parent_container_id` IS NULL';
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':tablet_group_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            // Append the new TabletObject to $output
            $output[] = new Container($row['container_id'], $row['container_name'], $pdo);
        }
        return $output;
    }

    public function display($termlist, $user_id, $pdo) {
        $cdliUrl = "http://www.cdli.ucla.edu/search/search_results.php?SearchMode=Text&ObjectID=" . substr($this->name, 1, 7) . "&requestFrom=Submit+Query";
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a name="tablet-<?php echo $this->id; ?>"></a><?php echo $this->name; ?>
                <div class="btn-group">
                    <?php if (User::isLoggedIn()) { ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                Add To <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a onclick="addTabletToNewArchive(<?php echo $this->id; ?>)">New Virtual Archive</a></li>
                                <li class="divider"></li>
                                <?php
                                // Display links for adding to the user's archives
                                $archives = User::getArchives();
                                foreach ($archives as $a) {
                                    $archiveID = $a->getID();
                                    echo "<li><a onclick=\"addTabletToArchive($archiveID, $this->id)\">", $a->getName(), "</a></li>\n";
                                }
                                ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            View At <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?php echo $cdliUrl; ?>">CDLI</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="panel-body col-md-8">
		    <?php
			$_SESSION['user_id'];
			$tablet_group_id = $this->id;
		    ?>
		    		    
                    <button onclick='(function (){
			

				var newWindow= window.open("inputComment.php?group_id="+"<?php echo $tablet_group_id ?>"+"&user_id="+"<?php echo $user_id ?>", null, "height=800, width=1600, status=yes,toolbar=no,menubar=no, location=no");
			})()'> Comment on Tablet </button>
		    
 		    
		
		    <?php
                    foreach ($this->objects as $object) {
                        $object->display($termlist, $this->names);
                    }
                    ?>
                </div>
                <div class="panel-body col-md-4">
                    <?php $this->displayNames(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function displayNames() {
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">Tablet Contents</div>
            <ul style="list-style: none;">
                <li><span class="glyphicon glyphicon-minus-sign list-minimizer"></span> Names
                    <ul>
                        <?php
                        foreach ($this->names as $n) {
                            echo "<li>$n</li>\n";
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
        <?php
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lang' => $this->lang,
            'objects' => $this->objects
        ];
    }

}

class Container implements JsonSerializable {

    private $id;
    private $name;
    private $childContainers;
    private $section;

    public function __construct($id, $name, PDO $pdo) {
        $this->id = $id;
        $this->name = $name;
        $this->childContainers = $this->fetchChildContainers($pdo);
        $this->section = $this->fetchSection($pdo);
    }

    private function fetchSection(PDO $pdo) {
        $sql = "SELECT * FROM `text_section` WHERE `container_id` = :my_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':my_id' => $this->id]);
        // Get results
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return new TextSection($row['text_section_id'], $pdo);
    }

    private function fetchChildContainers(PDO $pdo) {
        $sql = "SELECT * FROM `container` WHERE `parent_container_id` = :my_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':my_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            // Append the new Text Section to $output
            $output[] = new Container($row['container_id'], $row['container_name'], $pdo);
        }
        return $output;
    }

    public function getID() {
        return $this->id;
    }

    public function addSection($section) {
        array_push($this->childContainers, $section);
    }

    public function display(array $termlist, array $names) {
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a data-toggle=collapse data-target="<?php echo "#tablet-object-$this->id"; ?>">
        <?php echo $this->name; ?>
                </a>
            </div>
            <div class="panel-body collapse in" id="<?php echo "tablet-object-$this->id"; ?>" >
                <?php
                foreach ($this->childContainers as $container) {
                    $container->display($termlist, $names);
                }
                $this->section->display($termlist, $names);
                ?>
            </div>
        </div>
        <?php
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sections' => $this->childContainers
        ];
    }

}

class TextSection implements JsonSerializable {

    private $id;
    private $lines;

    public function __construct($id, PDO $pdo) {
        $this->id = $id;
        $this->lines = $this->fetchLines($pdo);
    }

    private function fetchLines(PDO $pdo) {
        $sql = "SELECT * FROM `line` WHERE `text_section_id` = :text_section_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':text_section_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            // Append the new line to $output
            $output[] = $row['line_text'];
        }
        return $output;
    }

    public function getID() {
        return $this->id;
    }

    public function insertMarks($line, $termlist, $names) {
        // This can be propgate up into Tablet, and then pass $patterns
        // and $replaces instead of $termslist and $names down through
        // the display() functions
        $patterns = array();
        $replaces = array();
        foreach ($termlist as $term) {
            $patterns[] = "/" . preg_quote($term) . "/i";
            $replaces[] = "<mark>$term</mark>";
        }
        foreach ($names as $name) {
            $patterns[] = "/" . preg_quote($name) . "/i";
            $replaces[] = "<b>$name</b>";
        }

        return preg_replace($patterns, $replaces, $line);
    }

    public function display($termlist, $names) {
        ?>
        <ol>
            <?php
            foreach ($this->lines as $line) {
                echo "<li>", $this->insertMarks($line, $termlist, $names), "</li>\n";
            }
            ?>
        </ol>
        <?php
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lines' => $this->lines
        ];
    }
}
