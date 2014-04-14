<?php

class User
{
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

    private static function beginSession($id, $name)
    {
        session_start();

        $_SESSION['id']   = $id;
        $_SESSION['name'] = $name;
    }

    public static function logout()
    {
        session_unset();
    }

    public static function continueSession()
    {
        session_start();
    }

    public static function login($pdo, $name, $pass)
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
            }
        }

        return $success;
    }
}

?>
