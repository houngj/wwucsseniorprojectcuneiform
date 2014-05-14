<html>
<body>
 <?php session_start() ?>  
 <form action="writeComment.php" method="post">
<input name="group_id" type="hidden" id="group_id" value="<?php echo $_GET['group_id'] ?>"/>
<textarea name="comments" id="comments" rows="32" cols="200"><?php echo $_SESSION['comment'] ?></textarea>
</br>   
<input type="submit" value="Submit Comment"/>
</form>






</body>
</html>
