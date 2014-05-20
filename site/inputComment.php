<html>
<body>
 <?php session_start(); 
       function fetchComments($user_id, $tablet_group_id){
    	
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
	}else{
		return $row[3];
	}
    };
       $comment = fetchComments($_GET['user_id'], $_GET['group_id']);
       $_SESSION['comment'] = $comment;
       ?>
 <form action="writeComment.php" method="post">
<input name="group_id" type="hidden" id="group_id" value="<?php echo $_GET['group_id'] ?>"/>
<textarea name="comments" id="comments" rows="32" cols="200"><?php echo $comment ?></textarea>
</br>   
<input type="submit" value="Submit Comment"/>
</form>






</body>
</html>
