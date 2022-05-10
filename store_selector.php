<!-- This file is the store selector page. It contains all the js/html/php codes. -->
<!-- The store selector page allows user to select the store they want to manage. If there is more than 4 stores, there will be a slider. -->
<?php
    session_start();
    include("config.php");
    
    //Checking for Login Session. If does not exist, redirect to login page
    if(!isset($_SESSION["loggedin"]) && !isset($_SESSION["userid"]))
    {
       header("Location: index.php");
    }
    
    
    //Setting Merchant Session variables - Dropdown
    if(isset($_POST["merchantSelect"])){
        $_SESSION["merchantid"] = $_POST["merchantid"];
        $_SESSION["merchantname"] = $_POST["merchantname"];
        $_SESSION["merchantidList"] = $_POST["merchantidList"];
        $_SESSION["merchantnameList"] = $_POST["merchantnameList"];
        header('location: dashboard.php');
    }
    $query = "SELECT u.user_id, u.full_name, m.merchant_id, m.merchant_name, m.address, m.image, m.Latitude, m.Longitude FROM users as u INNER JOIN merchants as m ON u.user_id=m.user_id WHERE u.user_id='".$_SESSION["userid"]."'";
    $data = array();
    $results = mysqli_query($conn, $query);
    
    if ($result = $results) {
        while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
        $dataResult = json_encode($data);
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
        <link rel="shortcut icon" href="adminFavicon.ico" />
        <title>
            <?php print $PAGE_TITLE;?>
        </title>
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport'/>
        
        <!--     Fonts and icons     -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
        
        <!-- CSS Files -->
        <link href="./assets/css/material-dashboard.css?v=2.1.0" rel="stylesheet"/>
        <style>
            .carousel {
              margin: 1.5rem;
            }
            .carousel-inner {
              height: auto;
            }
            
            .carousel-control-prev {
              margin-left: -100px;
            }
            
            .carousel-control-next {
              margin-right: -100px;
            }
        </style>
    </head>
    <body class="off-canvas-sidebar">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top text-white">
            <div class="container">
                <div class="navbar-wrapper">
                    <p class="navbar-brand">Kill The Queue | Admin</p>
                </div>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.killtheq.com/">
                                <i class="material-icons">face</i>
                                About Us
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">
                                Google Play Store
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">
                                App Store
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="wrapper wrapper-full-page">
            <div class="page-header pricing-page header-filter" filter-color="orange" style="background-image: url('./assets/img/bg-pricing.jpg')">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 ml-auto mr-auto text-center">
                            <h2 class="title">Select Store to Manage</h2>
                        </div>
                    </div>
                    <div id="storeCard"></div>
                </div>
            </div>
        </div>
        <!--   Core JS Files   -->
        <script src="./assets/js/core/jquery.min.js"></script>
        <script src="./assets/js/core/popper.min.js"></script>
        <script src="./assets/js/core/bootstrap-material-design.min.js"></script>
        <script src="./assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
        <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="./assets/js/material-dashboard.js?v=2.1.0" type="text/javascript"></script>
        <script>
            var data = <?php echo $dataResult; ?>;
            var output = "";
            var merchantidList = [];
            var merchantnameList = [];
            for (var i = 0; i < data.length; i++) {
                merchantidList.push(data[i]["merchant_id"]);
                merchantnameList.push(data[i]["merchant_name"]);
            }
            if (data.length == 0) {
                window.location.href = 'http://admin.mp02.projectsbit.org/error.html';
            } else if (data.length < 5) {
                output = "<div class='row'>";
                for (var i = 0; i < data.length; i++) {
                    output += loopCard(i);
                }
                if ((i % 4) != 0) {
                    output += "</div>";
                }
            } else {
                $("#storeCard").wrap(`<div class="carousel slide" data-interval="false" data-ride="carousel" id="carouselExampleControls">
                                        <div class="carousel-inner"></div>
                                    </div>`);
                $(`<a class="carousel-control-prev" data-slide="prev" href="#carouselExampleControls" role="button">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" data-slide="next" href="#carouselExampleControls" role="button">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>`).insertAfter(".carousel-inner");
                output = "<div class='row'>";
                for (var i = 0; i < data.length; i++) {
                    if (i == 0) {
                        output += "</div><div class='carousel-item active'><div class='row'>" + loopCard(i);
                    } else if ((i % 4) == 0) {
                        output += "</div></div><div class='carousel-item'><div class='row'>" + loopCard(i);
                    } else {
                        output += loopCard(i);
                    }
                }
                if ((i % 4) != 0) {
                    output += "</div><div class='row'></div>";
                }
            }
            function loopCard(i) {
                var data = <?php echo $dataResult; ?>;
                loadCard = `<div class="col-lg-3 col-md-6">
                                                    <div class="card card-pricing ">
                                                        <div class="card-body">
                                                            <div class="card-icon icon-white ">
                                                                <img src="  ${data[i]["image"]}  "  class="img-thumbnail">
                                                            </div>
                                                            <h3 class="card-title">  ${data[i]["merchant_name"]}  </h3>
                                                            <p class="card-description">  ${data[i]["address"]}  </p>
                                                        </div>
                                                        <div class="card-footer justify-content-center ">
                                                            <form method="post" action="store_selector.php">
                                                                <input name="merchantid" type="hidden" value="${data[i]["merchant_id"]}">
                                                                <input name="merchantname" type="hidden" value="${data[i]["merchant_name"]}">
                                                                <input name="merchantidList" type="hidden" value="${merchantidList.toString()}">
                                                                <input name="merchantnameList" type="hidden" value="${merchantnameList.toString()}">
                                                                <button name="merchantSelect" type="submit" class="btn btn-round btn-danger">Manage Store</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>`;
                return loadCard;
            }
        </script>
        <script>
            $(document).ready(function () {
                document.getElementById("storeCard").innerHTML += output;
            });
        </script>
    </body>
</html>