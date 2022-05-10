<!-- This file will be used to generate the excel file containing sales transation history, using PHP.  -->
<?php
    session_start();
    include("config.php");
    
    header("Content-Disposition: attachment; filename='KillQ-".$_SESSION["merchantname"]."-SalesReport.xls'");
    header("Content-Type: application/vnd.ms-excel");
    
    function dataFilter($str_val){
        $str_val = preg_replace("/\t/", "\\t", $str_val);
        $str_val = preg_replace("/\r?\n/", "\\n", $str_val);
        if(strstr($str_val, '"')) $str_val = '"' . str_replace('"', '""', $str_val) . '"';
    }
    
    $post_list = array();
    
    $query = "SELECT
                o.order_id, o.created_at as date_purchased, p.product_id, p.product_name, p.category, p.brand, oi.quantity, (p.price*oi.quantity) as revenue, u.user_id, u.date_of_birth
                from order_items as oi 
                inner join products as p on p.product_id = oi.product_id 
                inner join orders as o on oi.order_id = o.order_id 
                inner join product_discount as pd on p.product_id=pd.product_id
                inner join users as u on u.user_id = o.user_id
                where o.merchant_id=".$_SESSION['merchantid']."
                order by o.order_id ASC,
                p.product_id ASC";
                
    $result = mysqli_query($conn, $query);
                
    $rowCount = mysqli_num_rows($result);
    
    $sno = 1;
    if($rowCount > 0){
        while($row = mysqli_fetch_assoc($result)){
            $post_list[] = array(
                "row_id"=>$sno, 
                "order_id"=>$row["order_id"], 
                "date_purchased"=>$row["date_purchased"],
                "product_id"=>$row["product_id"],
                "product_name"=>$row["product_name"],
                "category"=>$row["category"],
                "brand"=>$row["brand"],
                "quantity"=>$row["quantity"],
                "revenue"=>$row["revenue"],
                "user_id"=>$row["user_id"],
                "dob"=>$row["date_of_birth"],
                );
            $sno++;
        }
    }
    
    $title_flag = false;
    foreach($post_list as $post){
        if(!$title_flag){
            echo implode("\t", array_keys($post)) . "\n";
            $title_flag = true;
        }
        array_walk($post, 'dataFilter');
        echo implode("\t", array_values($post)) . "\n";
    }
    
?>