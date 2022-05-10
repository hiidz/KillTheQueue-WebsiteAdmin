<!-- This file is the manage operations page. It contains all the js/html/php codes. -->
<!-- The manage operations page consist of various fields for user to edit the store details and operating hours -->
<?php 
    session_start();
    include("config.php");
    
    
    // Image Validator Codes
    $emptyArray = [];
    $imgError = isset($_SESSION["imgErrorMsg"]) ? $_SESSION["imgErrorMsg"] : $emptyArray;
    unset($_SESSION["imgErrorMsg"]);
  
  
  
  
    //Checking for Login Session. If does not exist, redirect to login page
    if(!isset($_SESSION["loggedin"]) && !isset($_SESSION["userid"]))
    {
        header("Location: index.php");
    }
    
    
    
    
    //Setting Merchant Session variables - Dropdown
    if(isset($_POST['newMerchantid'])) {
        $_SESSION["merchantid"] = $_POST["newMerchantid"];
        $_SESSION["merchantname"] = $_POST["newMerchantname"];
        $_SESSION["merchantidList"] = $_POST["merchantidList"];
        $_SESSION["merchantnameList"] = $_POST["merchantnameList"];
    }
    
    
    
    
    //Update Merchant Profile
    if(isset($_POST["updateProfile"])) {
        $query = "UPDATE merchants SET merchant_name = '" . mysqli_real_escape_string($conn,$_POST['store_name']) . "', address = '" . mysqli_real_escape_string($conn,$_POST['address']) . "', Latitude = '" . $_POST['latitude'] . "', Longitude = '" . $_POST['longitude'] . "' WHERE merchants.merchant_id = '" . $_SESSION['merchantid'] . "'";
        mysqli_query($conn, $query);
        $_SESSION["merchantnameList"] = str_replace($_SESSION["merchantname"],$_POST['store_name'],$_SESSION["merchantnameList"]);
        $_SESSION["merchantname"] = $_POST['store_name'];
    }
    
    
    
    
    //Update Merchant Operating Hour
    if(isset($_POST["updateOperatingHours"])) {
        $numQuestions = 7;
        $daysOftheWeek = array("","Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
        
        for($x=1;$x<=$numQuestions;$x++)
        {
        	$query = " UPDATE operating_hour ";
        	$query .= " SET starting_hour ='" . $_POST['usr_Startingtime-'.$x.''] . "' ";
        	$query .= ", ending_hour = '". $_POST['usr_Endingtime-'.$x.''] ."' ";
        	$query .= ", active = '". $_POST['on/off-'.$x.''] ."' ";
        	$query .= " WHERE ";
        	$query .= " merchant_id=" . $_SESSION["merchantid"] . " AND weekday= '" . $daysOftheWeek[$x] . "'; ";		
        	$result = mysqli_query($conn, $query);
        }
    }
    
    
    
    
    //Fetch Merchant Profile Data
    $queryFetchMerchant = "SELECT u.email, m.merchant_name, m.address, m.Latitude, m.Longitude, m.image from merchants as m 
                            INNER JOIN users as u ON m.user_id = u.user_id where merchant_id=" . $_SESSION['merchantid'] . "";
    $results = mysqli_query($conn, $queryFetchMerchant);
    $fetchresult = mysqli_fetch_array($results);
    $email = $fetchresult[0];
    $merchantName = $fetchresult[1];
    $address = $fetchresult[2];
    $latitude = $fetchresult[3];
    $longitude = $fetchresult[4];
    $image = $fetchresult[5];
    
    
    
    
    //Fetch Merchant Operating Hours
    $queryFetchOperatingHour = "SELECT weekday, starting_hour, ending_hour, active FROM operating_hour 
                WHERE merchant_id=" . $_SESSION['merchantid'] . " 
                ORDER BY FIELD(weekday, 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY')";
    $operatingHourResults = mysqli_query($conn, $queryFetchOperatingHour);
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
    </head>
    <body class="">
        <div class="wrapper ">
            <div class="sidebar" data-background-color="dark_blue" data-color="danger" data-image="./assets/img/sidebar-1.jpg">\
                <div class="logo">
                    <a href="" class="simple-text logo-mini"></a>
                    <a href="dashboard.php" class="simple-text logo-normal">
                        <?php echo $_SESSION["username"]; ?>
                    </a>
                </div>
                <div class="sidebar-wrapper">
                    <div class="user">
                        <div class="user-info">
                            <div class="dropdown">
                                <a id="selectMerchantidBtn" class="username" data-toggle="collapse" href="#collapseExample">
                                    <span class="caret"></span>
                                    <?php echo $_SESSION["merchantname"]; ?>
                                </a>
                                <div class="collapse" id="collapseExample">
                                    <ul class="nav" id="storeNameDropdown"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav">
                        <li class="nav-item <?php if ($CURRENT_PAGE == "Dashboard") {?>active<?php }?>">
                            <a class="nav-link" href="dashboard.php">
                                <i class="material-icons">dashboard
                                </i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-item <?php if ($CURRENT_PAGE == "Manage Operations") {?>active<?php }?>">
                            <a class="nav-link" href="manage_operations.php">
                                <i class="material-icons">store
                                </i>
                                <p>
                                    Manage Operations
                                </p>
                            </a>
                        </li>
                        <li class="nav-item <?php if ($CURRENT_PAGE == "Stock Manager") {?>active<?php }?>">
                            <a class="nav-link" href="stock_manager.php">
                                <i class="material-icons">edit
                                </i>
                                <p>
                                    Stock Manager
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="material-icons">power_settings_new
                                </i>
                                <p>
                                    Logout
                                </p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="main-panel">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
                    <div class="container-fluid">
                        <div class="navbar-wrapper">
                            <div class="navbar-minimize">
                                <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                                    <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert</i>
                                    <i class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list</i>
                                </button>
                            </div>
                            <a class="navbar-brand">Manage Operations</a>
                      </div>
                    </div>
                </nav>
                <!-- End Navbar -->
                <div class="content">
                    <div class="content">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header card-header-danger">
                                            <h4 class="card-title">Edit Profile</h4>
                                            <p class="card-category">Complete your profile</p>
                                        </div>
                                        <div class="card-body">
                                            <form method="post" action="manage_operations.php">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Email</label>
                                                            <input type="text" name="email" value="<?php echo $email ?> (disabled)" class="form-control" disabled>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Store Name</label>
                                                            <input type="text" name="store_name" class="form-control" value="<?php echo $merchantName ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Address</label>
                                                            <input type="text" id="autocompleteAddressField" name="address" class="form-control" value="<?php echo $address ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Latitude</label>
                                                            <input type="text" name="latitude" id="latitude" class="form-control" value="<?php echo $latitude ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Longitude</label>
                                                            <input type="text" name="longitude" id="longitude" class="form-control" value="<?php echo $longitude ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" name="updateProfile" class="btn btn-danger pull-right">Update Profile</button>
                                                <div class="clearfix"></div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-profile">
                                        <form action="upload.php" method="post" enctype="multipart/form-data">  
                                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                                <div class="fileinput-new card-avatar">
                                                    <img class="img" src="<?php echo $image."?".rand()."" ?>" /  alt="...">
                                                </div>
                                            <div class="fileinput-preview fileinput-exists card-avatar"></div>
                                                <br/>
                                                <div>
                                                    <span class="btn btn-danger btn-round btn-file">
                                                        <span class="fileinput-new">Add Photo</span>
                                                        <span class="fileinput-exists">Change</span>
                                                        <input type="file" name="fileToUpload" id="fileToUpload" />
                                                        <input type="hidden" name="ID" value="<?php echo $_SESSION["merchantid"]; ?>" />
                                                        <input type="hidden" name="type" value="Merchant" />
                                                    </span>
                                                    <br/>
                                                    <button class="btn btn-danger btn-round fileinput-exists" type="submit" name="submitImg" class="btn btn-danger pull-right"><i class="fa fa-check"></i>Confirm</button>
                                                    <br/>
                                                    <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header card-header-danger">
                                    <h4 class="card-title ">Operating Hours</h4>
                                    <p class="card-category"> Edit stores operating hours</p>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="manage_operations.php">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead class=" text-danger">
                                                    <th>Day</th>
                                                    <th>On / Off</th>
                                                    <th>Opening Hour</th>
                                                    <th>Closing Hour</th>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $counter = 0;
                                                    while($row = mysqli_fetch_array($operatingHourResults)){
                                                        $counter++;
                                                        if($row["active"] == "Open"){
                                                            $onCheckedValue = 'checked';
                                                            $offCheckedValue = '';
                                                        } else if($row["active"] == "Close") {
                                                            $onCheckedValue = '';
                                                            $offCheckedValue = 'checked';
                                                        }
                                                        echo '
                                                            <tr>
                                                                <td>'.$row["weekday"].'</td>
                                                                <td>
                                                                    <div class="form-check">
                                                                        <label class="form-check-label">
                                                                            <input class="form-check-input" type="radio" name="on/off-'.$counter.'" value="Open" '.$onCheckedValue.'> On
                                                                            <span class="circle">
                                                                                <span class="check"></span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <label class="form-check-label">
                                                                            <input class="form-check-input" type="radio" name="on/off-'.$counter.'" value="Close" '.$offCheckedValue.'> Off
                                                                            <span class="circle">
                                                                                <span class="check"></span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td><input type="time" name="usr_Startingtime-'.$counter.'" value="'.$row["starting_hour"].'"></td>
                                                                <td><input type="time" name="usr_Endingtime-'.$counter.'" value="'.$row["ending_hour"].'"></td>
                                                            </tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="submit" name="updateOperatingHours" class="btn btn-danger pull-right">Update Time</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="footer">
                    <div class="container-fluid">
                        <nav class="float-left">
                            <ul>
                                <li><a href="https://www.killtheq.com/">
                                        About Us
                                    </a></li>
                                <li><a href="">
                                        Google Play Store
                                    </a></li>
                                <li><a href="">
                                        Apple App Store
                                    </a></li>
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
        <!-- Plugin for the momentJs  -->
        <script src="./assets/js/plugins/moment.min.js"></script>
        <!-- Forms Validations Plugin -->
        <script src="./assets/js/plugins/jquery.validate.min.js"></script>
        <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
        <script src="./assets/js/plugins/bootstrap-datetimepicker.min.js"></script>
        <!--  DataTables.net Plugin, full documentation here: https://datatables.net/  -->
        <script src="./assets/js/plugins/jquery.dataTables.min.js"></script>
        <!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
        <script src="./assets/js/plugins/bootstrap-tagsinput.js"></script>
        <!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
        <!--  Notifications Plugin    -->
        <script src="./assets/js/plugins/bootstrap-notify.js"></script>
        <!-- Chartist JS -->
        <script src="./assets/js/plugins/chartist.min.js"></script>
        <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="./assets/js/material-dashboard.js?v=2.1.0" type="text/javascript"></script>
        <script src="./assets/js/plugins/jasny-bootstrap.min.js"></script>
        <!-- GOOGLE MAP API KEY -->
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=insert_api_key&libraries=places"></script>
        
        
        <!-- Initialize Google Map & Image Validation -->
        <script>
            var imgError = <?php echo json_encode($imgError); ?>;
            function showNotification(error){
              $.notify({
                  icon: "warning",
                  message: "<b>"+error+"</b>"
              },{
                  type: 'danger',
                  timer: 500,
                  placement: {
                      from: "top",
                      align: "right"
                  }
              });
            }
            $(document).ready(function() {
                imgError.map(error => showNotification(error));
            });
            
            function initializeGoogleMap() {
                var input = document.getElementById('autocompleteAddressField');
                var autocomplete = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();
                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                });
            }
            google.maps.event.addDomListener(window, 'load', initializeGoogleMap); 
        </script>
        
        
        <!-- **CORE** Merchant Dropdown JS -->
        <script>
            var merchantidList = "<?php echo $_SESSION["merchantidList"]; ?>";
            var merchantnameList = "<?php echo $_SESSION["merchantnameList"]; ?>";
            var array = merchantidList.split(",").map(function (item) {
                return item.trim();
            });
            var array2 = merchantnameList.split(",").map(function (item) {
                return item.trim();
            });
            if (array.length == 0) {
                window.location.href = 'http://admin.mp02.projectsbit.org/error.html';
            } else {
                var output = "";
                for (var i = 0; i < array.length; i++) {
                    output += `<li class="nav-item ">
                                    <form id="newMerchantSelect${i}" method="post" action="manage_operations.php">
                                        <input type="hidden" name="newMerchantid" value="${ (array[i])}">
                                        <input type="hidden" name="newMerchantname" value="${ (array2[i])}">
                                        <input name="merchantidList" type="hidden" value="${merchantidList}">
                                        <input name="merchantnameList" type="hidden" value="${merchantnameList}">
                                    </form>
                                    <a class="nav-link selectIdBtn" id="newMerchantBtn${i}" data-value="${ (array[i])}" href="#">
                                        <span class="sidebar-normal">
                                            ${array2[i]}
                                        </span>
                                    </a>
                                </li>`;
                }
                $("#storeNameDropdown").append(output);
            }
            var merchantid = "<?php echo $_SESSION["merchantid"]; ?>";
            var btns = document.getElementsByClassName("selectIdBtn");
            for (let i = 0; i < btns.length; i++) {
                $('#newMerchantBtn' + i).click(function () {     // refresh page with new merchant selected
                    document.getElementById('newMerchantSelect' + i).submit();
                });
                if ($(btns[i]).data('value') == merchantid) {        // set selected merchantid btn active
                    btns[i].parentNode.className = "nav-item active";
                }
            }
        </script>
    </body>
</html>