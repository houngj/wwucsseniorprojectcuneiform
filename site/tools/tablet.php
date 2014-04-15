<?php

class Tablet implements JsonSerializable {

    private $id;
    private $name;
    private $lang;
    private $objects;
    private $names;

    public function __construct($id, PDO $pdo) {
        $this->id = $id;
        $this->fetchTabletData($pdo); // Sets $this->{name, lang}
        $this->objects = $this->fetchObjects($pdo);
        $this->names = $this->fetchNames($pdo);
    }

    private function fetchTabletData(PDO $pdo) {
        $sql = "SELECT * FROM `tablet` WHERE `tablet_id` = :tablet_id";
        $statement = $pdo->prepare($sql);
        $statement->execute([':tablet_id' => $this->id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['name'];
        $this->lang = $row['lang'];
    }

    private function fetchNames(PDO $pdo) {
        $sql = "SELECT `name_text` FROM `name_reference` NATURAL JOIN `name` WHERE `tablet_id` = :tablet_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':tablet_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        // Converts from an array of arrays of strings to an array of strings.
        return array_column($result, 'name_text');
    }

    private function fetchObjects(PDO $pdo) {
        $sql = "SELECT * FROM `tablet_object` WHERE `tablet_id` = :tablet_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':tablet_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            // Append the new TabletObject to $output
            $output[] = new TabletObject($row['tablet_object_id'], $row['obj_name'], $pdo);
        }
        return $output;
    }

    public function display($termlist) {
        $cdliUrl = "http://www.cdli.ucla.edu/search/search_results.php?SearchMode=Text&ObjectID=" . substr($this->name, 1, 7) . "&requestFrom=Submit+Query";
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo $this->name; ?>
                <div class="btn-group">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            Add To <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#">New Virtual Archive</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Virtual Archive 1</a></li>
                            <li><a href="#">Virtual Archive 2</a></li>
                            <li><a href="#">Virtual Archive 3</a></li>
                            <li><a href="#">Empty Virtual Archive</a></li>
                        </ul>
                    </div>
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

class TabletObject implements JsonSerializable {

    private $id;
    private $name;
    private $sections;

    public function __construct($id, $name, PDO $pdo) {
        $this->id = $id;
        $this->name = $name;
        $this->sections = $this->fetchSections($pdo);
    }

    private function fetchSections(PDO $pdo) {
        $sql = "SELECT * FROM `text_section` WHERE `tablet_object_id` = :tablet_object_id";
        $statement = $pdo->prepare($sql);
        // Bind :tablet_id to $this->id
        $statement->execute([':tablet_object_id' => $this->id]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach ($result as $row) {
            // Append the new Text Section to $output
            $output[] = new TextSection($row['text_section_id'], $row['text_section_name'], $pdo);
        }
        return $output;
    }

    public function getID() {
        return $this->id;
    }

    public function addSection($section) {
        array_push($this->sections, $section);
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
                foreach ($this->sections as $section) {
                    $section->display($termlist, $names);
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sections' => $this->sections
        ];
    }

}

class TextSection implements JsonSerializable {

    private $id;
    private $name;
    private $lines;

    public function __construct($id, $name, PDO $pdo) {
        $this->id = $id;
        $this->name = $name;
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
            $output[] = $row['text'];
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
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $this->name; ?></div>
            <div class="panel-body">
                <ol>
                    <?php
                    foreach ($this->lines as $line) {
                        echo "<li>", $this->insertMarks($line, $termlist, $names), "</li>\n";
                    }
                    ?>
                </ol>
            </div>
        </div>
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
?>
