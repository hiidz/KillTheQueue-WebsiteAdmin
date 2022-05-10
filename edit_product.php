<!-- The file is connected to the database to UPDATE the product data -->
<?php 
    session_start();
    include("config.php");
    
    
    // Image Validator Codes
    $emptyArray = [];
    $imgError = isset($_SESSION["imgErrorMsg"]) ? $_SESSION["imgErrorMsg"] : $emptyArray;
    unset($_SESSION["imgErrorMsg"]);
    
    
    
    
    // Unset Discount Error
    unset($error);
    
    
    
    
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
        header('location:stock_manager.php');
    }
    
    
    
    
    //Update Stock Details
    if(isset($_POST["updateStock"])) {
        $updateStockQuery = "UPDATE products SET 
                            product_name = '" . mysqli_real_escape_string($conn,$_POST['product_name']) . "', 
                            price = '" . $_POST['price'] . "', 
                            stock_take = '" . $_POST['stock'] . "', 
                            category = '" . mysqli_real_escape_string($conn,$_POST['category']) . "', 
                            age_restricted = '" . $_POST['age_restricted'] . "', 
                            brand = '" . mysqli_real_escape_string($conn,$_POST['brand']) . "', 
                            description = '" . mysqli_real_escape_string($conn,$_POST['description']). "' 
                            WHERE products.product_id = '" . $_GET['sku'] . "'";
        mysqli_query($conn, $updateStockQuery);
    }
    
    
    
    
    //Add Product Discount
    if(isset($_POST["addDiscountBtn"])) {
        if($_POST["discountedPrice"] <= 0){
            $error = "Price is below \$0";
        }
        if (empty($error)) {
            $date_created_sql=  date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $_POST['date_created']))); 
            $valid_until_sql = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $_POST['valid_until'])));
            $addProductQuery = "UPDATE product_discount SET discount_value= '" . $_POST['discount_value'] . "', discount_unit= '" . mysqli_real_escape_string($conn,$_POST['discount_unit']) . "', date_created= '" . $date_created_sql . "', valid_until= '" . $valid_until_sql . "' WHERE product_id='" . $_GET['sku'] . "'";
            mysqli_query($conn, $addProductQuery);
        }
        
    }
    
    
    
    
    //Delete Existing Product Discount
    if(isset($_POST["deleteDiscountBtn"])){
        $deleteProductQuery = "UPDATE product_discount SET discount_value= '0.00', discount_unit= 'none', date_created= '0000-00-00 00:00:00', 
                    valid_until= '0000-00-00 00:00:00' WHERE product_id='" . $_GET['sku'] . "'";
        mysqli_query($conn, $deleteProductQuery);
    }
    
    
    
    
    //Fetch Product Details
    $fetchProductQuery = "SELECT p.product_name,p.price,p.stock_take,p.brand,p.description,p.image, p.category, p.age_restricted,pd.discount_value,pd.discount_unit,
                            (CASE when NOW() between pd.date_created and pd.valid_until then pd.date_created else null END) as date_created, 
                            (CASE when NOW() between pd.date_created and pd.valid_until then pd.valid_until else null END) as valid_until
                            FROM products as p
                            inner join product_discount as pd on p.product_id = pd.product_id
                            WHERE p.merchant_id= ".$_SESSION['merchantid']." AND p.product_id = '" . $_GET['sku'] . "' AND p.status='active'";
    $results = mysqli_query($conn, $fetchProductQuery);
    if(mysqli_num_rows($results) > 0){
        $fetchresult = mysqli_fetch_array($results);
        $product_name = $fetchresult[0];
        $price = $fetchresult[1];
        $stock_take = $fetchresult[2];
        $brand = $fetchresult[3];
        $description = $fetchresult[4];
        $image = $fetchresult[5];
        if (@getimagesize($image) == true) {
            $image = $image;
        } else {
            $image = "http://mp02.projectsbit.org/KillQ/Product%20Images/default.jpg";
        }
        $category = $fetchresult[6];
        $age_restricted = $fetchresult[7];
        $discount_value = $fetchresult[8];
        $discount_unit = $fetchresult[9];
        $date_created = new DateTime($fetchresult[10]);
        $valid_until = new DateTime($fetchresult[11]);
        $date_created_formatted = date_format($date_created, 'Y-m-d H:i:s');
        $valid_until_formatted = date_format($valid_until, 'Y-m-d H:i:s');
        $currentTime = date('Y-m-d H:i:s', time());
        $percentRadioChecked = null;
        $currencyRadioChecked = null;
        if ($currentTime > $date_created_formatted && $currentTime < $valid_until_formatted){
            $displaydatestart = date_format($date_created, 'd/m/Y h:i A');
            $displaydateend = date_format($valid_until, 'd/m/Y h:i A');
            if($discount_unit="percent"){
                $percentRadioChecked = "checked";
            } else{
                $currencyRadioChecked = "checked";
            }
            $discountStringdisplay = "View";
            $discountBtnStringDisplay = "Delete";
            $btnName = "deleteDiscountBtn";
            $formStatus = "disabled";
        }else{
            $displaydatestart = null;
            $displaydateend = null;
            $date_created_formatted = null;
            $valid_until_formatted = null;
            $discount_value = null;
            $discount_unit = null;
            $discountStringdisplay = "Add";
            $discountBtnStringDisplay = "Add";
            $btnName = "addDiscountBtn";
            $formStatus = "";
        }
    } else {
        header('location:error.html');
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
        <link href="./assets/css/test.css" rel="stylesheet"/>
        <link href="./assets/css/material-dashboard.css?v=2.1.0" rel="stylesheet" />
    </head>
    <body class="">
        <div class="wrapper ">
            <div class="sidebar" data-background-color="dark_blue" data-color="danger" data-image="./assets/img/sidebar-1.jpg">
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
                            <a class="navbar-brand">Edit Stocks</a>
                        </div>
                    </div>
                </nav>
            <!-- End Navbar -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header card-header-danger">
                                        <h4 class="card-title">Edit Product</h4>
                                        <p class="card-category">Make changes for your product</p>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="edit_product.php?sku=<?php echo $_GET['sku']; ?>" id="editStockForm">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Product Name</label>
                                                        <input type="text" name="product_name" value="<?php echo $product_name;?>" class="form-control" >
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Category</label>
                                                        <input type="text" name="category" class="form-control" value="<?php echo $category ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Brand</label>
                                                        <input type="text" name="brand" class="form-control" value="<?php echo $brand ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Price</label>
                                                        <input type="number" step="0.01" min="0.1" name="price" class="form-control" value="<?php echo $price ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Stock</label>
                                                        <input type="number" min="0" name="stock" class="form-control" value="<?php echo $stock_take ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Age Restricted</label>
                                                        <input type="number" min="0" max="100" name="age_restricted" class="form-control" value="<?php echo $age_restricted ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="form-group">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Description</label>
                                                            <textarea class="form-control" rows="5" name="description" form="editStockForm"><?php echo $description?></textarea>
                                                        </div>
                                                    </div>     
                                                </div>
                                            </div>   
                                            <button type="submit" name="updateStock" class="btn btn-danger pull-right">Edit Product</button>
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
                                            <br>
                                            <div>
                                                <span class="btn btn-danger btn-round btn-file">
                                                    <span class="fileinput-new">Add Photo</span>
                                                    <span class="fileinput-exists">Change</span>
                                                    <input type="file" name="fileToUpload" id="fileToUpload" />
                                                    <input type="hidden" name="ID" value="<?php echo $_GET['sku']; ?>" />
                                                    <input type="hidden" name="type" value="Product" />
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
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header card-header-danger">
                                <h4 class="card-title"><?php echo $discountStringdisplay;?> Discount</h4>
                            </div>
                            <div class="card-body">
                                <form method="post" action="edit_product.php?sku=<?php echo $_GET['sku']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label-control">Discount Value</label>
                                                <input type="number" id="discount_value_input" min="0" step="0.1" name="discount_value" value="<?php echo $discount_value; ?>" class="form-control" required <?php echo $formStatus;?>>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="discount_unit" value="currency" <?php echo $currencyRadioChecked; ?> required <?php echo $formStatus;?>>Currency
                                                    <span class="circle">
                                                        <span class="check"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" type="radio" name="discount_unit" value="percent" <?php echo $percentRadioChecked; ?> <?php echo $formStatus;?>>Percentage
                                                    <span class="circle">
                                                        <span class="check"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label-control">Start Date</label>
                                                <input type="text" name="date_created" class="form-control datetimepicker" value="<?php echo $displaydatestart; ?>" required/ <?php echo $formStatus;?>>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="label-control">End Date</label>
                                                <input type="text" name="valid_until" class="form-control datetimepicker" value="<?php echo $displaydateend; ?>" required/ <?php echo $formStatus;?>>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="discountedPriceHiddenInput"></div>
                                    <button type="submit" name="<?php echo $btnName; ?>" class="btn btn-danger pull-right"><?php echo $discountBtnStringDisplay;?></button>
                                    <div id="totalPrice"></div>
                                    <?php  if (isset($error)) : ?>
                                        <div class="productDiscountError" id="error">
                                            <p>
                                                <?php echo $error ?>
                                            </p>
                                        </div>
                                    <?php  endif ?>
                                    <div class="clearfix"></div>
                                </form>
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
        <!--  DataTables.net Plugin, full documentation here: https://datatables.net/  -->
        <script src="./assets/js/plugins/jquery.dataTables.min.js"></script>
        <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="./assets/js/material-dashboard.js?v=2.1.0" type="text/javascript"></script>
        <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
        <script src="./assets/js/plugins/jasny-bootstrap.min.js"></script>
        <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
        <script src="./assets/js/plugins/bootstrap-datetimepicker.min.js"></script>
        <!--  Notifications Plugin    -->
        <script src="./assets/js/plugins/bootstrap-notify.js"></script>
        <!-- Plugin for the momentJs  -->
        <script src="./assets/js/plugins/moment.min.js"></script>
        <!--  Plugin for Sweet Alert -->
        <script src="./assets/js/plugins/sweetalert2.js"></script>
        <!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
        <script src="../../assets/js/plugins/bootstrap-selectpicker.js"></script>
              
        <script>
            var price = <?php echo $price ?>;
            
            var divobj = document.getElementById('totalPrice');
            divobj.style.display='none';
            
            $("#discount_value_input").change(function(){
              calculateTotal();
            });
            
            $('input:radio').change(function(){
                calculateTotal();
            });   
            
            function calculateTotal() {
                if($('#error').length){
            		var errorDiv = document.getElementById('error');
                    errorDiv.style.display='none';
            	}
                var discountedPrice;
                if($('input:radio').is(':checked') && $('#discount_value_input').val()){
                    var discountValue = $('#discount_value_input').val();
                    var radioValue = $("input:radio:checked").val();
                    if(radioValue=="currency"){
                        discountedPrice = price - discountValue;
                    } else{
                        discountedPrice = price - (price * discountValue / 100);
                    }
                    discountedPrice = discountedPrice.toFixed(2);
                    var divobj = document.getElementById('totalPrice');
                    divobj.style.display='block';
                    divobj.innerHTML = "Price after Discount is  $"+discountedPrice;
                    var output = `<input type='hidden' name='discountedPrice' value="${discountedPrice}">`
                    $("#discountedPriceHiddenInput").append(output);
                }
            }
            
            //Initialize DateTime picker
            $('.datetimepicker').datetimepicker({
                format: 'D/M/YYYY h:mm A',
                icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-screenshot',
                    clear: 'fa fa-trash',
                    close: 'fa fa-remove'
                }
            });
            
            //Image Validation
            var imgError = <?php echo json_encode($imgError); ?>;
            function showNotification(error){
                $.notify({
                    icon: "warning",
                    message: "<b>"+error+"</b>"
                },
                {
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
        </script>
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
                                    <form id="newMerchantSelect${i}" method="post" action="stock_manager.php">
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