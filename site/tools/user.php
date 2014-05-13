<?php

if (!isset($_SESSION)) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/archive.php';

class User
{
    private static $errorMessage;
    private static $archives = null;

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

    public static function create(PDO $pdo, $name, $pass)
    {
        if (User::isUsernameAvailable($pdo, $name))
        {
            $sql =
                'INSERT INTO user (name, hash)
                 VALUES (:name, :hash)';

            $stmt = $pdo->prepare($sql);

            if (! $stmt->execute(
                array(':name' => $name,
                      ':hash' => password_hash($pass, PASSWORD_DEFAULT))))
            {
                // The third element of errorInfo contains a human-
                // readable error message.

                die($stmt->errorInfo()[2]);
            }

            if (User::login($pdo, $name, $pass))
            {
                header('Location: index.php');
            }
        }

        return false;
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
                // Invalid password. Maybe throwing exceptions is better?
                User::$errorMessage = "Invalid username or password";
            }
        }

        return $success;
    }

    private static function fetchArchives(PDO $pdo) {
        User::$archives = array();
        assert(User::isLoggedIn(), "User isn't logged in");
        $sql = "SELECT * FROM `archive` WHERE `user_id`= :user_id";
        $statement = $pdo->prepare($sql);
        // Bind user_id
        $statement->execute([':user_id' => User::getUserId()]);
        // Get results
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $row) {
            User::$archives[] = new Archive($row['archive_id'], $pdo);
        }
    }

    static function getArchives() {
        if (User::isLoggedIn() === false) {
            throw new Exception('User isn\'t logged in');
        } else if (User::$archives === null) {
            throw new Exception('User::\$archives is null');
        }
        return User::$archives;
    }

    public static function printArchives(PDO $pdo, $links = false) {
        assert(User::isLoggedIn(), "User isn't logged in");
        User::fetchArchives($pdo); // Sets User::$archives
        echo "<ul style=\"list-style-type:none\">\n";
        foreach (User::$archives as $archive){
            $archive->display($links);
        }
        echo "</ul>";
    }

    public static function isUsernameAvailable(PDO $pdo, $name)
    {
        $sql =
            'SELECT COUNT(U.name) AS count
             FROM   user U
             WHERE  U.name = :name';

        $stmt = $pdo->prepare($sql);

        if (! $stmt->execute( array(':name' => $name) ))
        {
            // The third element of errorInfo contains a human-
            // readable error message.

            die($stmt->errorInfo()[2]);
        }

        $success = 'undefined';

        if ($row = $stmt->fetch())
        {
            $success =
                (0 == $row['count'])
                    ? 'true'
                    : 'false';
        }

        return $success;
    }
}

?>
