<!-- This file is the login page. It contains all the js/html/php codes. -->
<!-- The login page will check if user has entered the correct password/username, by cross checking with the database -->
<?php 
    session_start();
    include("config.php");
    
    unset($error);
    
    if (isset($_POST['login_user'])) {
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $password = mysqli_real_escape_string($conn, md5($_POST["password"]));
        
        if (empty($email) || empty($password)) {
            $error = "Login is Invalid";
        }
        
        if (empty($error)) {
            $query = "SELECT u.user_id, u.full_name, m.merchant_id, m.merchant_name, m.address, m.image, m.Latitude, m.Longitude FROM users as u INNER JOIN merchants as m ON u.user_id=m.user_id WHERE email='$email' AND password='$password' AND role='adm'";
            $results = mysqli_query($conn, $query);
            $fetchresult = mysqli_fetch_array($results);
            
            if (isset($fetchresult[0])) {
                unset($error);
                $_SESSION["loggedin"] = "true";
                $_SESSION["userid"] = $fetchresult[0];
                $_SESSION["username"] = $fetchresult[1];
                header('location: store_selector.php');
            }else {
                $error = "Wrong email or password";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link rel="shortcut icon" href="adminFavicon.ico" />
        <title>
            <?php print $PAGE_TITLE;?>
        </title>
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
        
        <!--     Fonts and icons     -->
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
        
        <!-- CSS Files -->
        <link href="./assets/css/material-dashboard.css?v=2.1.0" rel="stylesheet" />
        
        <!-- VALIDATION CSS -->
        <style>
            .error {
            width: 80%;
            margin: 0px auto;
            margin-bottom: 10px;
            padding: 10px 10px 0px 10px;
            border: 1px solid #a94442;
            color: #a94442;
            background: #f2dede;
            border-radius: 5px;
            text-align: center;
        }
        </style>
    </head>

<body class="off-canvas-sidebar">
    <div class="wrapper wrapper-full-page">
        <div class="page-header login-page header-filter" filter-color="black" style="background-image: url('./assets/img/login.jpg'); background-size: cover; background-position: top center;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-8 ml-auto mr-auto">
                        <form class="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="card card-login">
                                <div class="card-header card-header-danger text-center">
                                    <h4 class="card-title">Login</h4>
                                </div>
                                <div class="card-body ">
                                    <p class="card-description text-center">Kill The Queue | Admin</p>
                                    <span class="bmd-form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="material-icons">email</i>
                                                </span>
                                            </div>
                                            <input type="email" class="form-control" name="email" required="true" placeholder="Email">
                                        </div>
                                    </span>
                                    <span class="bmd-form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="material-icons">lock_outline</i>
                                                </span>
                                            </div>
                                            <input type="password" class="form-control" name="password" required="true" placeholder="Password">
                                        </div>
                                    </span>
                                </div>
                                <div class="card-footer justify-content-center">
                                    <button type="submit" class="btn btn-danger" name="login_user">Lets go</button>
                                </div>
                                <?php  if (isset($error)) : ?>
                                    <div class="error">
                                        <p><?php echo $error ?></p>
                                    </div>
                                <?php  endif ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="container">
                    <nav class="float-left">
                        <ul>
                            <li>
                                <a href="https://www.killtheq.com/">
                                    About Us
                                </a>
                            </li>
                            <li>
                                <a href="">
                                    Google Play Store
                                </a>
                            </li>
                            <li>
                                <a href="">
                                    App Store
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </footer>
        </div>
    </div>
    <!--   Core JS Files   -->
    <script src="./assets/js/core/jquery.min.js"></script>
    <script src="./assets/js/core/popper.min.js"></script>
    <script src="./assets/js/core/bootstrap-material-design.min.js"></script>
    <script src="./assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="./assets/js/material-dashboard.js?v=2.1.0" type="text/javascript"></script>
</body>
</html>