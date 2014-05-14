<html>
<body>
 <?php session_start(); ?> 
 <form action="writeComment.php?comments="+<?php $_POST['comments'] ?>>
   <textarea name="comments" id="comments" rows="16" cols="100"><?php $_SESSION['comments']?></textarea>
 
   <input type="submit" value="Submit Comment">
</form>


</body>
</html>
