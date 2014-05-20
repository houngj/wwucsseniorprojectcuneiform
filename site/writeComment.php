<?php
	

		
	$host = "localhost";
	$db = "cuneiform";
	$user = "dingo";
	$pass = "hungry!";
	$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
	$pdo->exec("SET profiling = 1");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	session_start();

	$sql = "SELECT * FROM `comments_table` WHERE `tablet_group_id` = :tablet_group_id AND `user_id` = :user_id";
	$statement = $pdo->prepare($sql);
	$statement->execute(array(':tablet_group_id' => $_POST['group_id'], ':user_id'=> $_SESSION['user_id']));
	$row = $statement->fetch();		     
	$_SESSION['comment'] = $_POST['comments'];
	if($row == null){
		$sql = "INSERT INTO comments_table (user_id,tablet_group_id, comment_text) VALUES (:user_id, :tablet_group_id, :comment_text)";
		$q = $pdo->prepare($sql);
		$q->execute(array(':user_id'=>$_SESSION['user_id'],
	                  ':tablet_group_id'=>$_POST['group_id'],
			  ':comment_text'=>$_SESSION['comment']));
	}

	$comment_id = $row['comment_id'];
	
	$sql = "UPDATE comments_table SET comment_text=? WHERE comment_id=$comment_id";
	$q = $pdo->prepare($sql);
	$q->execute(array($_SESSION['comment'])); 

	
	echo "<script>";
		echo "window.close();";
	echo "</script>";
                   
           
    
   
	
 		
?>