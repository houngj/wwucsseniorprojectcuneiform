<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// <rant>
// When PHP `include`s or `require`s a file, it searches in the the directories
// in the php.ini variable `include_path`, then it searches paths relative to
// the FILE IT'S EXECUTING. This seems like a fine and dandy choice, until you
// have files in subdirecories that are including other files, then all the
// paths must be relative to all the possible files being executed.  Which isn't
// posible with our ./ and ./REST dirs.  So we have to resort to absolute paths.
// Which means site (the connections and tools, at least,) must be placed in the
// document root of the server, e.g. /var/www/tools, not /var/www/cunei/tools.
// This is bad for portablity, but it's the only idea i have.
// </rant>

require_once $_SERVER['DOCUMENT_ROOT'] . '/connections/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/tools/user.php';

$pdo = getConnection();

if (isset($_POST['username']) && isset($_POST['password1']))
{
    // The user has submitted the registration form.
    // Let's try to create the user and log them in.
    // If this works, we'll redirect away from this page.

    user::create($pdo, $_POST['username'], $_POST['password1']);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>WWU Cuneiform | Register</title>
        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom styles for this template -->
        <link href="css/dashboard.css" rel="stylesheet">
        <!-- Minified jquery -->
        <script src="js/jquery.min.js"></script>
        <script>

        $(document)
            .ready
            (
                function ()
                {
                     $('input:password').keyup(register_onPasswordChange);
                     $('#username').change(register_onUsernameChange);
                }
            );

        function register_onPasswordChange(e)
        {
            var valid = $('#password1').val() === $('#password2').val();
            var pwds  = $('input:password');

            updateFieldState(pwds, valid);
            register_updateSubmit();
        }

        function register_onUsernameChange(e)
        {
            var uname = $('#username');
            isUsernameAvailable( uname.val() );
        }

        function register_updateUsernameAvailability(valid)
        {
            updateFieldState($('#username'), valid);
            register_updateSubmit();
        }

        function isUsernameAvailable(username)
        {
            var result = 
                $.ajax
                (
                    {
                        url: 'REST/verify.php',
                        data:
                        {
                            'action':   'verify_username',
                            'username': encodeURIComponent(username)
                        },
                        dataType: 'text'
                    }
                )
                .done
                (                
                    function (data)
                    {
                        register_updateUsernameAvailability(data === 'true');
                    }
                );
        }

        function register_updateSubmit()
        {
            var l = $('span.glyphicon.glyphicon-remove').length;
            var s = $('input:submit');

            if (l > 0)
            {
                s.prop('disabled', 'disabled');
            } 
            else
            {
                s.removeProp('disabled');
            }
        }

        function updateFieldState(o, valid)
        {
            o.siblings('span')
                .toggleClass('glyphicon-remove', !valid)
                .toggleClass('glyphicon-ok', valid)
                .parent()
                    .toggleClass('has-error', !valid)
                    .toggleClass('has-success', valid);
        }

        </script>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.php">WWU Cuneiform</a>
                    <ul class="nav navbar-nav">
                        <li><a href="index.php">Home</a></li>
                        <?php if (! User::isLoggedIn()) { ?>
                            <li class="active"><a href="register.php">Register</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                    <!-- Begin main content div -->
                    <h1>Register</h1>

<form role="form" method="POST">
  <div class="form-group has-feedback has-error">
    <label class="control-label" for="username">User name</label>
    <input type="text" class="form-control" id="username" name="username" placeholder="desired username">
    <span class="glyphicon glyphicon-remove form-control-feedback"></span>
  </div>
  <div class="form-group has-feedback has-error">
    <label class="control-label" for="password1">Password</label>
    <input type="password" class="form-control" id="password1" name="password1" placeholder="password">
    <span class="glyphicon glyphicon-remove form-control-feedback"></span>
  </div>
  <div class="form-group has-feedback has-error">
    <label class="control-label" for="password2">Verify password</label>
    <input type="password" class="form-control" id="password2" name="password2" placeholder="repeat password">
    <span class="glyphicon glyphicon-remove form-control-feedback"></span>
  </div>
  <input type="submit" class="btn btn-default" disabled="disabled" value="Submit" />
</form><!-- End register form -->

                </div><!--/.container -->
            </div>
        </div>
        <!--Bootstrap core JavaScript
        ================================================== -->
        <!--Placed at the end of the document so the pages load faster -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/site.js"></script>
        <script type="text/javascript">
            if (document.getElementById("search").value.length !== 0) {
                graphDates(document.getElementById("search").value);
                graphNames(document.getElementById("search").value);
                graphAttestation(document.getElementById("search").value);
            }
        </script>
    </body>
</html>
