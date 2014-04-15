<?php

if (!isset($_SESSION)) {
    session_start();
}

include('archive.php');

class User
{
    private static $errorMessage;

    public static function isLoggedIn()
    {
        return (isset($_SESSION) && isset($_SESSION['id']));
    }

    public static function getName()
    {
        if (User::isLoggedIn())
        {
            return $_SESSION['name'];
        }

        return "";
    }

    public static function getUserId()
    {
        if (User::isLoggedIn())
        {
            return $_SESSION['id'];
        }

        return 0;
    }
    
    public static function getErrorMessage() {
        return User::$errorMessage;
    }

    private static function beginSession($id, $name)
    {
        $_SESSION['id']   = $id;
        $_SESSION['name'] = $name;
    }

    public static function logout()
    {
        // Frees all $_SESSION variables
        session_unset();
    }

    public static function continueSession()
    {
    }

    public static function login(PDO $pdo, $name, $pass)
    {
        $sql =
            'SELECT U.user_id, U.name, U.hash
             FROM   user U 
             WHERE  U.name = :name';

        $stmt = $pdo->prepare($sql);

        if (! $stmt->execute( array(':name' => $name) ))
        {
            // The third element of errorInfo contains a human-
            // readable error message.

            die($stmt->errorInfo()[2]);
        }

        $success = false;

        if ($row = $stmt->fetch())
        {
            // At this point, we know that the user entered
            // the name of an existing user. Verify that the
            // supplied password matches the specified hash.

            if (password_verify($pass, $row['hash']))
            {
                $success = true;

                User::beginSession
                (
                    $row['user_id'],
                    $row['name']
                );
                // No error.
                User::$errorMessage = null;
            } 
            else 
            {
                // Invalid password.
                User::$errorMessage = "Invalid username or password";
            }
        }

        return $success;
    }
    
    private static function getArchives(PDO $pdo) {
        $sql = "SELECT * FROM `archive` WHERE `user_id`= :user_id";
        $statement = $pdo->prepare($sql);
        // Bind user_id
        $statement->execute([':user_id' => User::getUserId()]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $output = array();
        foreach($result as $row) {
            $output[] = new Archive($row['archive_id'], $pdo);
        }
        return $output;
    }

    public static function printArchives(PDO $pdo) {
        $archives = User::getArchives($pdo);
        echo "<ul style=\"list-style-type:none\">\n";
        foreach ($archives as $archive){
            $archive->display();
        }
        echo "</ul>";
    }

}

?>
