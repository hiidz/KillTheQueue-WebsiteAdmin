<!-- This file is the stock manager page. It contains all the js/html/php codes.  -->
<!-- The stock manager page allows user to view and delete all products, as well as add more products -->
<?php 
    session_start();
    include("config.php");
    
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
    
    
    
    
    //Add Product
    if(isset($_POST["addProduct"])) {
        $query = "INSERT INTO products (product_name, merchant_id, price,  stock_take, category, age_restricted, brand, description, original_cost) VALUES ('".mysqli_real_escape_string($conn,$_POST['product_name'])."', '".$_SESSION['merchantid']."', '".(int)$_POST['price']."','".$_POST['stock']."','".mysqli_real_escape_string($conn,$_POST['category'])."', '".$_POST['age_restricted']."', '".mysqli_real_escape_string($conn,$_POST['brand'])."','".mysqli_real_escape_string($conn,$_POST['description'])."','".(int)$_POST['original_cost']."')";
        mysqli_query($conn, $query);
        
        $new_productid = $conn->insert_id;
        $query2 = "UPDATE products set image = 'http://mp02.projectsbit.org/KillQ/Product%20Images/".$new_productid.".jpg' WHERE product_id = '".$new_productid."'";
        $query3 = "INSERT INTO product_discount (product_id, discount_value, discount_unit, date_created, valid_until) VALUES ('".$new_productid."', '0', 'none', '0000-00-00 00:00:00.000000', '0000-00-00 00:00:00.000000')";
        mysqli_query($conn, $query2);
        mysqli_query($conn, $query3);
        header('location:stock_manager.php');
    }
    
    
    
    //Delete Product
    if(isset($_POST["productIdDelete"])){
        $query2 = "UPDATE products set status = 'inactive' where product_id = '".$_POST["productIdDelete"]."'";
        mysqli_query($conn, $query2);
        header('location:stock_manager.php');
    }
    
    
    
    //Fetch Product Details
    $fetchProductQuery = "SELECT product_id,product_name,price,stock_take,brand,description,Image,original_cost FROM products WHERE merchant_id=" . $_SESSION['merchantid'] . " AND status='active'";
    $productResults = mysqli_query($conn, $fetchProductQuery);
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
                            <a class="navbar-brand">Stock Manager</a>
                        </div>
                    </div>
                </nav>
                <!-- End Navbar -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header card-header-danger card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">assignment</i>
                                        </div>
                                        <h4 class="card-title">Manage your stocks</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="toolbar">
                                        </div>
                                        <div class="material-datatables">
                                            <table id="datatables" class="table table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th class="disabled-sorting">Image</th>
                                                        <th>SKU</th>
                                                        <th>Name</th>
                                                        <th>Brand</th>
                                                        <th>Price</th>
                                                        <th>Original Cost</th>
                                                        <th>Stock level</th>
                                                        <th class="disabled-sorting">Description</th>
                                                        <th class="disabled-sorting text-right">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tfoot>
                                                    <tr>
                                                        <th>Image</th>
                                                        <th>Sku</th>
                                                        <th>Name</th>
                                                        <th>Brand</th>
                                                        <th>Price</th>
                                                        <th>Original Cost</th>
                                                        <th>Stock level</th>
                                                        <th>Description</th>
                                                        <th class="text-right">Actions</th>
                                                    </tr>
                                                </tfoot>
                                                <tbody>
                                                    <?php 
                                                    $loopVal = -1;
                                                    while($row = mysqli_fetch_array($productResults))
                                                    {
                                                        $loopVal++;
                                                        $stock_warning ='';
                                                        if ($row["stock_take"] < 30){
                                                            $stock_warning = 'class="table-danger"';
                                                        }
                                                        if (@getimagesize($row["Image"]) == true) {
                                                            $image = $row["Image"];
                                                        } else {
                                                            $image = "http://mp02.projectsbit.org/KillQ/Product%20Images/default.jpg";
                                                        }
                                                        echo '<tr '.$stock_warning.'>
                                                              <td><img src='.$image.'?'.rand().' height="100" width="100" alt="..." /></td>
                                                              <td>'.$row["product_id"].'</td>
                                                              <td>'.$row["product_name"].'</td>
                                                              <td>'.$row["brand"].'</td>
                                                              <td>$'.$row["price"].'</td>
                                                              <td>$'.$row["original_cost"].'</td>
                                                              <td>'.$row["stock_take"].'</td>
                                                              <td class="text-wrap">'.$row["description"].'</td>
                                                              <td class="text-right">
                                                                <a href="edit_product.php?sku='.$row["product_id"].'" class="btn btn-link btn-danger btn-just-icon edit"><i class="material-icons">edit</i></a>
                                                                <button id="delete_btn_'.$loopVal.'" class="delete_btn btn btn-link btn-danger btn-just-icon remove"><i class="material-icons">close</i></button>
                                                                <a href="product_info.php?sku='.$row["product_id"].'" target="_blank" class="btn btn-link btn-danger btn-just-icon edit"><i class="material-icons">open_in_new</i></a>
                                                                <form id="delete_form_'.$loopVal.'" method="post" action="stock_manager.php">
                                                                    <input type="hidden" name="productIdDelete" value="'.$row["product_id"].'">
                                                                </form>
                                                            </td>
                                                            </tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header card-header-danger">
                                        <h4 class="card-title">Add Product</h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="stock_manager.php" id="addProductForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Product Name</label>
                                                        <input type="text" name="product_name" value="" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Original Cost</label>
                                                        <input type="number" step="0.01" min="0.1" name="original_cost" class="form-control" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Price</label>
                                                        <input type="number" step="0.01" min="0.1" name="price" class="form-control" value="" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Brand</label>
                                                        <input type="text" name="brand" class="form-control" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Category</label>
                                                        <input type="text" name="category" class="form-control" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                      <label class="bmd-label-floating">Stock</label>
                                                      <input type="number" min="0" name="stock" class="form-control" value="" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Age Restricted</label>
                                                        <input type="number" min="0" max="100" name="age_restricted" class="form-control" value="" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="form-group">
                                                            <label class="bmd-label-floating">Description</label>
                                                            <textarea class="form-control" rows="5" name="description" form="addProductForm"></textarea>
                                                        </div>
                                                    </div>                   
                                                    <button type="submit" name="addProduct" class="btn btn-danger pull-right">Add Product</button>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
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
        <!--  DataTables.net Plugin, full documentation here: https://datatables.net/  -->
        <script src="./assets/js/plugins/jquery.dataTables.min.js"></script>
        <!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
        <script src="./assets/js/plugins/bootstrap-tagsinput.js"></script>
        <!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
        <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="./assets/js/material-dashboard.js?v=2.1.0" type="text/javascript"></script>
        <!-- Library for adding dinamically elements -->
        <script src="./assets/js/plugins/arrive.min.js"></script>
        <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
        <script src="./assets/js/plugins/jasny-bootstrap.min.js"></script>

        <script>
            var deleteBtns = document.getElementsByClassName("delete_btn");
            for (let i = 0; i < deleteBtns.length; i++) {
                $('#delete_btn_' + i).click(function () {     // refresh page with new merchant selected
                    document.getElementById('delete_form_' + i).submit();
                    console.log(i);
                });
            }
            $(document).ready(function() {
                $('#datatables').DataTable({
                    "columns": [
                        { "width": "5%" },
                        { "width": "5%" },
                        { "width": "16%"},
                        { "width": "6%" },
                        { "width": "6%" },
                        { "width": "8%" },
                        { "width": "7%" },
                        { "width": "10%" },
                        { "width": "5%" },
                        ],
                    "pagingType": "full_numbers",
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    responsive: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search records",
                    }
                });
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