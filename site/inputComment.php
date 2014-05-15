<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';

$pdo = getConnection();
if (!User::isLoggedIn()) {
    die('Non logged in users cannot make comments.');
}

if (isset($_POST['tablet_group_id']) && isset($_POST['comments'])) {
    $sql = 'INSERT INTO `comment` (`comment_id`, `user_id`, `tablet_group_id`, `comment_text`)
            VALUES (NULL, :user_id, :tablet_group_id, :comment_text)
            ON DUPLICATE KEY UPDATE `comment_text`=VALUES(`comment_text`)';
    $q = $pdo->prepare($sql);
    $q->execute(array(":user_id"         => User::getUserId(),
                      ":tablet_group_id" => $_POST['tablet_group_id'],
                      ":comment_text"    => $_POST['comments']));
    echo "Comment saved!\n";
}
if (isset($_GET['tablet_group_id'])) {
    // Adding new comm
    $sql = "SELECT * FROM `comment` WHERE `tablet_group_id` = :tablet_group_id AND `user_id` = :user_id";
    $statement = $pdo->prepare($sql);
    $statement->execute(array(':tablet_group_id' => $_GET['tablet_group_id'],
                              ':user_id'         => User::getUserId()));
    $row = $statement->fetch();
    if ($row != null) {
        $comment_text = $row['comment_text'];
    } else {
        $comment_text = "";
    }
} else {
    die("Invalid action specified");
}
?>
<html>
    <body>
        <form method="post">
            <input name="tablet_group_id" type="hidden" id="tablet_group_id" value="<?php echo $_GET['tablet_group_id']; ?>"/>
            <textarea name="comments" id="comments" rows="32" cols="200"><?php echo htmlspecialchars($comment_text); ?></textarea>
            <br />
            <input type="submit" value="Submit Comment"/>
        </form>
    </body>
</html>
