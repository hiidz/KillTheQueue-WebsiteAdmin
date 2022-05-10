<!-- This file is the dashboard page. It contains all the js/html/php codes. -->
<!-- We used chartist.js plugin to display the charts. And all the charts data is fetched from the database -->
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
    
    
    
    
    //Getting New Customer values for last 7 days
    $queryNewCustomer = "SELECT user_id, created_at FROM orders WHERE merchant_id=".$_SESSION['merchantid']."
                GROUP BY(user_id) HAVING created_at >= NOW() + INTERVAL -7 DAY AND created_at <  NOW() + INTERVAL  0 DAY ";
    $resultNewCustomer = mysqli_query($conn, $queryNewCustomer);
    if ($resultNewCustomer->num_rows > 0){
        $newCustomerVal = mysqli_num_rows($resultNewCustomer);
    } else {
        $newCustomerVal = 0;
    }
    
    
    
    
    //Getting Total Revenue for last 7 days
    $queryTotalRevenue = "SELECT SUM(price) as TotalRevenue FROM orders WHERE merchant_id=".$_SESSION['merchantid']." 
                        and (created_at >= NOW() + INTERVAL -7 DAY AND created_at <  NOW() + INTERVAL  0 DAY )";
    $resultTotalRevenue = mysqli_query($conn, $queryTotalRevenue);
    if ($resultTotalRevenue->num_rows > 0){
        $totalRevenueVal = mysqli_fetch_array($resultTotalRevenue)[0];
    }
    if ($totalRevenueVal == null){
        $totalRevenueVal=0;
    }
    
    
    
    
    //Getting Total Product values
    $queryTotalProduct = "SELECT COUNT(product_id) as totalProducts FROM products WHERE merchant_id=".$_SESSION['merchantid']." AND status='active'";
    $resultTotalProduct = mysqli_query($conn, $queryTotalProduct);
    if ($resultTotalProduct->num_rows > 0){
        $totalProductVal = mysqli_fetch_array($resultTotalProduct)[0];
    } else {
        $totalProductVal = 0;
    }
    
    
    
    
    //Getting Total Invoice values
    $queryTotalInvoice = "SELECT COUNT(order_id) as UniqueCustomer FROM orders WHERE merchant_id=".$_SESSION['merchantid']."";
    $resultTotalInvoice = mysqli_query($conn, $queryTotalInvoice);
    if ($resultTotalInvoice->num_rows > 0){
        $totalInvoiceVal = mysqli_fetch_array($resultTotalInvoice)[0];
    } else {
        $totalInvoiceVal = 0;
    }
    
    
    
    
    //Total Profit Line Chart -- WEEK
    $queryLineWeekChart = "SELECT a.day, COALESCE(b.TotalProfit, '0.00') as TotalProfit FROM days a
                            LEFT JOIN (
                                SELECT created_at , SUM(price)-SUM(COGS) as TotalProfit FROM orders 
                                WHERE merchant_id=".$_SESSION['merchantid']." and (created_at >= NOW() + INTERVAL -7 DAY AND created_at <  NOW() + INTERVAL  0 DAY ) 
                                GROUP BY DATE_FORMAT(created_at, '%W') 
                            ) b ON a.day = DATE_FORMAT(b.created_at, '%W')
                            ORDER BY a.val";
    $resultLineWeekChart = mysqli_query($conn, $queryLineWeekChart);
    $lineWeekLabel = '';
    $lineWeekSeries = '';
    while($row = mysqli_fetch_array($resultLineWeekChart))   
        {
            $lineWeekLabel = $lineWeekLabel.'"'.$row["day"].'",';
            $lineWeekSeries = $lineWeekSeries.'"'.$row["TotalProfit"].'",';
        }
    $lineWeekLabel = trim($lineWeekLabel, ",");
    $lineWeekSeries = trim($lineWeekSeries, ",");
    
    
    
    
    //Total Profit Line Chart -- MONTH
    $queryLineMonthChart = "SELECT a.month, COALESCE(b.TotalProfit, '0.00') as TotalProfit FROM months a
                            LEFT JOIN (
                                SELECT created_at , SUM(price)-SUM(COGS) as TotalProfit FROM orders 
                                WHERE merchant_id=".$_SESSION['merchantid']." and (created_at >= NOW() + INTERVAL -12 MONTH AND created_at <  NOW() + INTERVAL  0 DAY ) 
                                GROUP BY DATE_FORMAT(created_at, '%b') 
                            ) b ON a.month = DATE_FORMAT(b.created_at, '%b')
                            ORDER BY a.val";
    $resultLineMonthChart = mysqli_query($conn, $queryLineMonthChart);
    $lineMonthLabel = '';
    $lineMonthSeries = '';
    while($row = mysqli_fetch_array($resultLineMonthChart))   
        {
            $lineMonthLabel = $lineMonthLabel.'"'.$row["month"].'",';
            $lineMonthSeries = $lineMonthSeries.'"'.$row["TotalProfit"].'",';
        }
    $lineMonthLabel = trim($lineMonthLabel, ",");
    $lineMonthSeries = trim($lineMonthSeries, ",");
    
    
    
    
    //Total Profit Line Chart -- YEAR
    $queryLineYearChart = "SELECT a.year, COALESCE(b.TotalProfit, '0.00') as TotalProfit FROM years a
                            LEFT JOIN (
                                SELECT created_at , SUM(price)-SUM(COGS) as TotalProfit FROM orders 
                                WHERE merchant_id=".$_SESSION['merchantid']." and (created_at >= NOW() + INTERVAL -5 YEAR AND created_at <  NOW() + INTERVAL  0 DAY ) 
                                GROUP BY DATE_FORMAT(created_at, '%Y') 
                            ) b ON a.year = DATE_FORMAT(b.created_at, '%Y')
                            ORDER BY a.val";
    $resultLineYearChart = mysqli_query($conn, $queryLineYearChart);
    $lineYearLabel = '';
    $lineYearSeries = '';
    while($row = mysqli_fetch_array($resultLineYearChart))   
        {
            $lineYearLabel = $lineYearLabel.'"'.$row["year"].'",';
            $lineYearSeries = $lineYearSeries.'"'.$row["TotalProfit"].'",';
        }
    $lineYearLabel = trim($lineYearLabel, ",");
    $lineYearSeries = trim($lineYearSeries, ",");
    
    
    
    
    //Pie Chart -- Category
    $queryPieChartCategory = "SELECT (IF(t2.row_number<6, t2.category,'Others')) as category, (SUM(t2.c)) as category_count 
                            FROM (SELECT t1.category, t1.c, @n:=@n+1 as row_number
                     			FROM  (SELECT p.category as category, SUM(o.quantity) as c FROM products as p
                                        INNER JOIN order_items as o ON o.product_id = p.product_id
                                        INNER JOIN orders as oo ON o.order_id = oo.order_id
                                        WHERE p.merchant_id=".$_SESSION['merchantid']."
                                        GROUP BY p.category
                                        ORDER BY c DESC) 
                              	t1) t2
                            GROUP BY IF(t2.row_number<6,t2.row_number,6)";
    mysqli_query($conn, "SET @n=0");
    $resultDonutPieChart = mysqli_query($conn, $queryPieChartCategory);
    $DonutPieLabel = '';
    $DonutPieSeries = '';
    while($row = mysqli_fetch_array($resultDonutPieChart))   
        {
            $DonutPieLabel = $DonutPieLabel.'"'.$row["category"].'",';
            $DonutPieSeries = $DonutPieSeries.'"'.$row["category_count"].'",';
        }
    $DonutPieLabel = trim($DonutPieLabel, ",");
    $DonutPieSeries = trim($DonutPieSeries, ",");
    
    
    
    
    //Pie Chart -- Brand
    $queryPieChartBrand = "SELECT (IF(t2.row_number<6, t2.brand,'Others')) as brand, (SUM(t2.c)) as brand_count 
                                FROM (SELECT t1.brand, t1.c, @n:=@n+1 as row_number
                                      FROM  (SELECT p.brand as brand, SUM(o.quantity) as c FROM products as p
                                             INNER JOIN order_items as o ON o.product_id = p.product_id
                                             INNER JOIN orders as oo ON o.order_id = oo.order_id
                                             WHERE p.merchant_id=".$_SESSION['merchantid']."
                                             GROUP BY p.brand
                                             ORDER BY c DESC) 
                                      t1) t2
                            GROUP BY IF(t2.row_number<6,t2.row_number,6)";
    mysqli_query($conn, "SET @n=0");
    $resultDonut2PieChart = mysqli_query($conn, $queryPieChartBrand);
    $Donut2PieLabel = '';
    $Donut2PieSeries = '';
    while($row = mysqli_fetch_array($resultDonut2PieChart))   
        {
            $Donut2PieLabel = $Donut2PieLabel.'"'.$row["brand"].'",';
            $Donut2PieSeries = $Donut2PieSeries.'"'.$row["brand_count"].'",';
        }
    $Donut2PieLabel = trim($Donut2PieLabel, ",");
    $Donut2PieSeries = trim($Donut2PieSeries, ",");
    
    
    
    
    //Peak Hour Bar Chart
    $queryBarPeakChart = "SELECT a.hour, COALESCE(b.num_sales, '0') as num_sales FROM hours a
                            LEFT JOIN (SELECT count(*) as num_sales, date_format( created_at, '%H' ) as `hour`
                             FROM orders WHERE merchant_id = ".$_SESSION['merchantid']."
                             group by `hour` order by created_at desc)
                             b ON a.hour = b.hour order by a.hour";
    $resultBarPeakChart = mysqli_query($conn, $queryBarPeakChart);
    $BarPeakLabel = '';
    $BarPeakSeries = '';
    while($row = mysqli_fetch_array($resultBarPeakChart))   
        {
            $BarPeakLabel = $BarPeakLabel.'"'.$row["hour"].'",';
            $BarPeakSeries = $BarPeakSeries.'"'.$row["num_sales"].'",';
        }
    $BarPeakLabel = trim($BarPeakLabel, ",");
    $BarPeakSeries = trim($BarPeakSeries, ",");
    
    
    
    
    //Product Performance Table
    $queryTable = "SELECT a.status, a.product_id, a.product_name, SUM(a.quantity) as totalQty, 
                    round(SUM((CASE WHEN discount_unit='currency' THEN a.price - discount_value WHEN discount_unit='percent' THEN a.price - (a.price * discount_value / 100) else a.price END) * a.quantity),2) AS totalRevenue,
                    round(SUM(((CASE WHEN discount_unit='currency' THEN a.price - discount_value WHEN discount_unit='percent' THEN a.price - (a.price * discount_value / 100) else a.price END) * a.quantity)-(a.original_cost * a.quantity)),2) as totalProfit
                    FROM (
                        SELECT oi.order_id, oi.product_id, p.product_name, oi.quantity,p.price,p.original_cost,p.status,
                        if(pd.date_created<=o.created_at AND pd.valid_until>=o.created_at,pd.discount_unit,null) as discount_unit,
                        if(pd.date_created<=o.created_at AND pd.valid_until>=o.created_at,pd.discount_value,null) as discount_value
                        FROM order_items as oi 
                        INNER JOIN products as p on p.product_id = oi.product_id 
                        INNER JOIN orders as o on oi.order_id = o.order_id
                        LEFT JOIN 
                            (SELECT pd.product_id, pd.discount_value, pd.discount_unit, pd.date_created, pd.valid_until 
                             FROM product_discount as pd) as pd 
                        on p.product_id = pd.product_id
                        WHERE p.merchant_id = ".$_SESSION['merchantid'].") as a
                    GROUP by a.product_id";
    $resultTable= mysqli_query($conn, $queryTable);
    $tableArray = [];
    while ($row = mysqli_fetch_assoc($resultTable))
    {
        $tableArray[] = $row;
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
        <link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
        <link href="./assets/css/chartist-plugin-tooltip.css" rel="stylesheet"/>
        <link href="./assets/css/test.css" rel="stylesheet"/>
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
                                <a id="MerchantidBtnDropdown" class="username" data-toggle="collapse" href="#collapseExample">
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
                            <a class="navbar-brand">Dashboard</a>
                        </div>
                    </div>
                </nav>
                <!-- End Navbar -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-primary card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">person_add</i>
                                        </div>
                                        <p class="card-category">New Customers</p>
                                        <h3 class="card-title">
                                            <?php echo $newCustomerVal; ?>
                                        </h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <i class="material-icons">date_range</i>
                                            Last 7 days
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-success card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">attach_money</i>
                                        </div>
                                        <p class="card-category">Total Revenue</p>
                                        <h3 class="card-title">
                                            <?php echo '$'.$totalRevenueVal.''; ?>
                                        </h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <i class="material-icons">date_range</i>
                                            Last 7 days
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-info card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">shopping_basket</i>
                                        </div>
                                        <p class="card-category">Number of Products</p>
                                        <h3 class="card-title">
                                            <?php echo $totalProductVal; ?>
                                        </h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <i class="material-icons">edit</i>
                                            <a href="stock_manager.php">Add more products</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-warning card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">receipt</i>
                                        </div>
                                        <p class="card-category">Total Invoice</p>
                                        <h3 class="card-title">
                                            <?php echo $totalInvoiceVal; ?>
                                        </h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <i class="material-icons">get_app</i>
                                            <a href="sales_report.php">Download sales data</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class ="row">
                            <div class="col-12" >
                                <div class="card card-chart">
                                    <div class="card-header card-header-danger card-header-icon">
                                        <div class="card-icon">
                                          <i class="material-icons">attach_money</i>
                                        </div>
                                        <h4 class="card-title">Total Profits</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="btn-group dropdown"></div>
                                            <ul class="nav nav-pills nav-pills-rounded chart-action flex-column">
                                            <li class="nav-item"><a class="active nav-link" id="profitSelectorWeekBtn" href="#" data-toggle="tab">Week</a></li>
                                            <li class="nav-item"><a class="nav-link"id="profitSelectorMonthBtn" href="#" data-toggle="tab">Month</a></li>
                                            <li class="nav-item"><a class="nav-link"id="profitSelectorYearBtn" href="#" data-toggle="tab">Year</a></li>
                                            </ul>
                                        </div>
                                        <div class="col-10">
                                            <div class="card-header card-header-transparent py-20"></div>
                                            <div class="widget-content tab-content bg-white p-20">
                                                <div class="ct-line-chart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-chart">
                                    <div class="card-header card-header-icon card-header-rose">
                                        <div class="card-icon">
                                            <i class="material-icons">pie_chart</i>
                                        </div>
                                        <h4 class="card-title">Sales by Brand</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="ct-donut2-chart"></div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="row">
                                            <div id="donut2Legend" class="col-md-12"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-chart">
                                    <div class="card-header card-header-icon card-header-success">
                                        <div class="card-icon">
                                            <i class="material-icons">category</i>
                                        </div>
                                        <h4 class="card-title">Sales by Category</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="ct-donut-chart"></div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="row">
                                            <div id="donutLegend" class="col-md-12"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-chart">
                                    <div class="card-header card-header-icon card-header-info">
                                        <div class="card-icon">
                                          <i class="material-icons">access_time</i>
                                        </div>
                                        <h4 class="card-title">Peak Hours</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="ct-bar-chart"></div>
                                        <h5 class="card-title"><center>Time of Day</center></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="card-header card-header-text card-header-warning">
                                        <div class="card-text">
                                            <h4 class="card-title">Top Five Product
                                        </div>
                                    </div>
                                    <div class="btn-group dropdown"></div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="nav nav-pills nav-pills-warning" role="tablist">
                                                <li class="nav-item">
                                                  <a class="nav-link active" data-toggle="tab" href="#topTableQty" role="tablist">
                                                    By Quantity
                                                  </a>
                                                </li>
                                                <li class="nav-item">
                                                  <a class="nav-link" data-toggle="tab" href="#topTableRevenue" role="tablist">
                                                    By Revenue
                                                  </a>
                                                </li>
                                              </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card-body table-responsive">
                                                <div class="tab-content tab-space">
                                                    <div class="tab-pane active show" id="topTableQty">
                                                        <table class="table table-hover">
                                                            <thead class="text-warning">
                                                                <th>SKU
                                                                </th>
                                                                <th>Product Name
                                                                </th>
                                                                <th>Quantity Sold
                                                                </th>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                function sortByTopQty($a, $b)
                                                                  {
                                                                    return strnatcmp($a['totalQty'], $b['totalQty']);
                                                                  }
                                                                  usort($tableArray, 'sortByTopQty');
                                                                  $topTableArray=array_reverse($tableArray,false);
                                                                  
                                                                    for($i=0;$i<5;$i++){
                                                                        if(empty($topTableArray[$i]["product_id"])){
                                                                            $topTableArray[$i]["product_id"] = "-";
                                                                            $topTableArray[$i]["product_name"] = "-";
                                                                            $topTableArray[$i]["totalQty"] = "-";
                                                                            $topTableArray[$i]["totalRevenue"] = "-";
                                                                            $topTableArray[$i]["totalProfit"] = "-";
                                                                            $topTableArray[$i]["status"] = "-";
                                                                        }
                                                                        if($topTableArray[$i]["status"]=="inactive"){
                                                                            $discProd = "table-warning";
                                                                        } else {
                                                                            $discProd = "";
                                                                        }
                                                                        echo '
                                                                            <tr class="'.$discProd.'">
                                                                              <td>'.$topTableArray[$i]["product_id"].'</td>
                                                                              <td>'.$topTableArray[$i]["product_name"].'</td>
                                                                              <td>'.$topTableArray[$i]["totalQty"].'</td>
                                                                            </tr>'; 
                                                                    }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane" id="topTableRevenue">
                                                        <table class="table table-hover">
                                                            <thead class="text-warning">
                                                                <th>SKU
                                                                </th>
                                                                <th>Product Name
                                                                </th>
                                                                <th>Total Revenue
                                                                </th>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                function sortByTopRevenue($a, $b)
                                                                  {
                                                                    return strnatcmp($a['totalRevenue'], $b['totalRevenue']);
                                                                  }
                                                                      usort($tableArray, 'sortByTopRevenue');
                                                                      $topTableArray=array_reverse($tableArray,false);
                                                                      
                                                                    for($i=0;$i<5;$i++){
                                                                        if(empty($topTableArray[$i]["product_id"])){
                                                                            $topTableArray[$i]["product_id"] = "-";
                                                                            $topTableArray[$i]["product_name"] = "-";
                                                                            $topTableArray[$i]["totalQty"] = "-";
                                                                            $topTableArray[$i]["totalRevenue"] = "-";
                                                                            $topTableArray[$i]["totalProfit"] = "-";
                                                                            $topTableArray[$i]["status"] = "-";
                                                                        }
                                                                        if($topTableArray[$i]["status"]=="inactive"){
                                                                            $discProd = "table-warning";
                                                                        } else {
                                                                            $discProd = "";
                                                                        }
                                                                        echo '
                                                                            <tr class="'.$discProd.'">
                                                                              <td>'.$topTableArray[$i]["product_id"].'</td>
                                                                              <td>'.$topTableArray[$i]["product_name"].'</td>
                                                                              <td>'.$topTableArray[$i]["totalRevenue"].'</td>
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
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card">
                                    <div class="card-header card-header-text card-header-warning">
                                        <div class="card-text">
                                            <h4 class="card-title">Bottom Five Product
                                        </div>
                                    </div>
                                    <div class="btn-group dropdown"></div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="nav nav-pills nav-pills-warning" role="tablist">
                                                <li class="nav-item">
                                                  <a class="nav-link active" data-toggle="tab" href="#bottomTableQty" role="tablist">
                                                    By Quantity
                                                  </a>
                                                </li>
                                                <li class="nav-item">
                                                  <a class="nav-link" data-toggle="tab" href="#bottomTableRevenue" role="tablist">
                                                    By Revenue
                                                  </a>
                                                </li>
                                              </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card-body table-responsive">
                                                <div class="tab-content tab-space">
                                                    <div class="tab-pane active show" id="bottomTableQty">
                                                        <table class="table table-hover">
                                                            <thead class="text-warning">
                                                                <th>SKU
                                                                </th>
                                                                <th>Product Name
                                                                </th>
                                                                <th>Quantity Sold
                                                                </th>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                function sortByBottomQty($a, $b)
                                                                  {
                                                                    return strnatcmp($a['totalQty'], $b['totalQty']);
                                                                  }
                                                                      usort($tableArray, 'sortByBottomQty');
                                                                      $bottomTableArray=$tableArray;
                                                                      
                                                                    for($i=0;$i<5;$i++){
                                                                        if(empty($bottomTableArray[$i]["product_id"])){
                                                                            $bottomTableArray[$i]["product_id"] = "-";
                                                                            $bottomTableArray[$i]["product_name"] = "-";
                                                                            $bottomTableArray[$i]["totalQty"] = "-";
                                                                            $bottomTableArray[$i]["totalRevenue"] = "-";
                                                                            $bottomTableArray[$i]["totalProfit"] = "-";
                                                                            $bottomTableArray[$i]["status"] = "-";
                                                                        }
                                                                        if($topTableArray[$i]["status"]=="inactive"){
                                                                            $discProd = "table-warning";
                                                                        } else {
                                                                            $discProd = "";
                                                                        }
                                                                        echo '<tr class="'.$discProd.'">
                                                                              <td>'.$bottomTableArray[$i]["product_id"].'</td>
                                                                              <td>'.$bottomTableArray[$i]["product_name"].'</td>
                                                                              <td>'.$bottomTableArray[$i]["totalQty"].'</td>
                                                                            </tr>'; 
                                                                    }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane" id="bottomTableRevenue">
                                                        <table class="table table-hover">
                                                            <thead class="text-warning">
                                                                <th>SKU
                                                                </th>
                                                                <th>Product Name
                                                                </th>
                                                                <th>Total Revenue
                                                                </th>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                function sortByBottomRevenue($a, $b)
                                                                  {
                                                                    return strnatcmp($a['totalRevenue'], $b['totalRevenue']);
                                                                  }
                                                                  
                                                                      usort($tableArray, 'sortByBottomRevenue');
                                                                      $bottomTableArray=$tableArray;
                                                                      
                                                                    for($i=0;$i<5;$i++){
                                                                        if(empty($bottomTableArray[$i]["product_id"])){
                                                                            $bottomTableArray[$i]["product_id"] = "-";
                                                                            $bottomTableArray[$i]["product_name"] = "-";
                                                                            $bottomTableArray[$i]["totalQty"] = "-";
                                                                            $bottomTableArray[$i]["totalRevenue"] = "-";
                                                                            $bottomTableArray[$i]["totalProfit"] = "-";
                                                                            $bottomTableArray[$i]["status"] = "-";
                                                                        }
                                                                        if($bottomTableArray[$i]["status"]=="inactive"){
                                                                            $discProd = "table-warning";
                                                                        } else {
                                                                            $discProd = "";
                                                                        }
                                                                        echo '<tr class="'.$discProd.'">
                                                                              <td>'.$bottomTableArray[$i]["product_id"].'</td>
                                                                              <td>'.$bottomTableArray[$i]["product_name"].'</td>
                                                                              <td>'.$bottomTableArray[$i]["totalRevenue"].'</td>
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
        <!--  Plugin for Sweet Alert -->
        <script src="./assets/js/plugins/sweetalert2.js"></script>
        <!-- Forms Validations Plugin -->
        <script src="./assets/js/plugins/jquery.validate.min.js"></script>
        <!-- Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
        <script src="./assets/js/plugins/jquery.bootstrap-wizard.js"></script>
        <!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
        <script src="./assets/js/plugins/bootstrap-selectpicker.js"></script>
        <!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
        <script src="./assets/js/plugins/bootstrap-datetimepicker.min.js"></script>
        <!--  DataTables.net Plugin, full documentation here: https://datatables.net/  -->
        <script src="./assets/js/plugins/jquery.dataTables.min.js"></script>
        <!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
        <script src="./assets/js/plugins/bootstrap-tagsinput.js"></script>
        <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
        <script src="./assets/js/plugins/jasny-bootstrap.min.js"></script>
        <!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
        <!-- Library for adding dinamically elements -->
        <script src="./assets/js/plugins/arrive.min.js"></script>
        <!-- Chartist JS -->
        <script src="./assets/js/plugins/chartist.min.js"></script>
        <!-- Chartist JS Plugins -->
        <script src="./assets/js/plugins/chartist-plugin-legend.js"></script>
        <script src="./assets/js/plugins/chartist-plugin-tooltip.js"></script>
        <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="./assets/js/material-dashboard.js?v=2.1.0" type="text/javascript"></script>
        
        
        <!-- Dashboard Chart JS Processor -->
        <script>
            var lineWeekLabel = [<?php echo $lineWeekLabel; ?>];
            var lineWeekSeries = [<?php echo $lineWeekSeries; ?>];
            var weekday = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
            var todaysDay = weekday[new Date().getDay()];
            var i = 0;
            while(lineWeekLabel[i]){
                if(lineWeekLabel[i]==todaysDay){
                    lineWeekLabel = lineWeekLabel.concat(lineWeekLabel.splice(0,i+1));
                    lineWeekSeries = lineWeekSeries.concat(lineWeekSeries.splice(0,i+1));
                    break;
                }
                i++;
            }
            
            
            var lineMonthLabel = [<?php echo $lineMonthLabel; ?>];
            var lineMonthSeries = [<?php echo $lineMonthSeries; ?>];
            var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
            var todaysMonth = months[new Date().getMonth()];
            var i = 0;
            while(lineMonthLabel[i]){
                if(lineMonthLabel[i]==todaysMonth){
                    lineMonthLabel = lineMonthLabel.concat(lineMonthLabel.splice(0,i+1));
                    lineMonthSeries = lineMonthSeries.concat(lineMonthSeries.splice(0,i+1));
                    break;
                }
                i++;
            }
            
            var lineYearLabel = [<?php echo $lineYearLabel; ?>].reverse();
            var lineYearSeries = [<?php echo $lineYearSeries; ?>].reverse();
            
            var DonutPieLabel = [<?php echo $DonutPieLabel; ?>];
            var DonutPieSeries = [<?php echo $DonutPieSeries; ?>].map(Number);
            
            var Donut2PieLabel = [<?php echo $Donut2PieLabel; ?>];
            var Donut2PieSeries = [<?php echo $Donut2PieSeries; ?>].map(Number);
            
            var BarPeakLabel = [<?php echo $BarPeakLabel; ?>];
            var BarPeakSeries = [<?php echo $BarPeakSeries; ?>];
            
            var lineChart = new Chartist.Line('.ct-line-chart', {
                    labels: lineWeekLabel,
                    series: [lineWeekSeries]
                }, {
                  fullWidth: true,
                  chartPadding: {
                    right: 70
                  },
                  height: '250px'
                }
            );
                
            $("#profitSelectorWeekBtn").on("click", function () {
                lineChart.update({
                labels: lineWeekLabel,
                series: [lineWeekSeries]
                });
            });
            $("#profitSelectorMonthBtn").on("click", function () {
                lineChart.update({
                labels: lineMonthLabel,
                series: [lineMonthSeries]
                });
            });
            $("#profitSelectorYearBtn").on("click", function () {
                lineChart.update({
                labels: lineYearLabel,
                series: [lineYearSeries]
                });
            });
            
            var donutLegend = document.getElementById('donutLegend');

            var sum = function(a, b) { return a + b };
            
            var donutChart =new Chartist.Pie('.ct-donut-chart', {
                series: DonutPieSeries,
            }, {
                showLabel: false,
                plugins: [
                    Chartist.plugins.legend({
                      position:donutLegend,
                      legendNames: DonutPieLabel
                    }),
                    Chartist.plugins.tooltip({
                        transformTooltipTextFnc: function(value) {
                            return Math.round(value / DonutPieSeries.reduce(sum) * 100) + '%';
                        },
                    }),
                ]
            });
            var donut2Legend = document.getElementById('donut2Legend');
            var donutChart2 = new Chartist.Pie('.ct-donut2-chart', {
                    series: Donut2PieSeries
            }, {
              showLabel: false,
                plugins: [
                    Chartist.plugins.legend({
                      position:donut2Legend,
                      legendNames: Donut2PieLabel
                    }),
                    Chartist.plugins.tooltip({
                        transformTooltipTextFnc: function(value) {
                            return Math.round(value / Donut2PieSeries.reduce(sum) * 100) + '%';
                        },
                    }),
                ]
            });
            console.log(BarPeakSeries);
            var barChart = new Chartist.Bar('.ct-bar-chart', {
                labels: ['1am',' ','3am',' ','5am',' ','7am',' ','9am',' ','11am',' ','1pm',' ','3pm',' ','5pm',' ','7pm',' ','9pm',' ','11pm',' '],
                series: BarPeakSeries
            }, {
              distributeSeries: true,
              axisY: {
                showLabel: false,
              },
              height: '180px'
            });
            barChart.on('draw', function(context) {
              if(context.type === 'bar') {
                context.element.attr({
                    style: 'stroke: #764280'
                });
              }
            });
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
                                    <form id="newMerchantSelect${i}" method="post" action="dashboard.php">
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