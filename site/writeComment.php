<?php
	

	
	$host = "localhost";
	$db = "cuneiform";
	$user = "dingo";
	$pass = "hungry!";
	$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
	$pdo->exec("SET profiling = 1");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	
	$sql = "INSERT INTO comments_table (user_id,tablet_group_id, comment_text) VALUES (:user_id, :tablet_group_id, :comment_text)";
	$q = $pdo->prepare($sql);
	$q->execute(array(':user_id'=>1,
	                  ':tablet_group_id'=>2,
			  ':comment_text'=>'hello'));
	

	
			   
                   
           
    
   
	
 		
?>