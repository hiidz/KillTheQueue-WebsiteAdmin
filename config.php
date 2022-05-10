<!-- This file is simply the connection to our database. -->
<!-- We also set some header variables, so the pages can be a little dynamic in the title name -->

<?php
    // Connect to the Database
    $dbServername = "redacted";
    $dbUsername = "redacted";
    $dbPassword = "redacted";
    $dbName = "redacted";
    $conn = new mysqli($dbServername, $dbUsername, $dbPassword, $dbName);
    
    switch ($_SERVER["SCRIPT_NAME"]) {
        case "/store_selector.php":
			$CURRENT_PAGE = "Store Selector"; 
			$PAGE_TITLE = "KillTheQ | Select Store";
			break;
		case "/dashboard.php":
			$CURRENT_PAGE = "Dashboard"; 
			$PAGE_TITLE = "KillTheQ | Dashboard";
			break;
		case "/manage_operations.php":
			$CURRENT_PAGE = "Manage Operations"; 
			$PAGE_TITLE = "KillTheQ | Manage Operations";
			break;
		case "/stock_manager.php":
			$CURRENT_PAGE = "Stock Manager";
			$PAGE_TITLE = "KillTheQ | Stock Manager";
			break;
		case "/edit_product.php":
			$CURRENT_PAGE = "Stock Manager";
			$PAGE_TITLE = "KillTheQ | Add Product";
			break;
		case "/settings.php":
			$CURRENT_PAGE = "Settings"; 
			$PAGE_TITLE = "KillTheQ | Settings";
			break;
        default:
        $CURRENT_PAGE = "Index";
        $PAGE_TITLE = "Kill the Queue | Admin";
    }
?>
