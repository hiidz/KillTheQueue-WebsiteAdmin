<!-- Uploads/updates the image in the file manager and database, when the user wants to edit store/product image -->
<?php
session_start();
include("config.php");
$_SESSION["imgErrorMsg"] = array();
$target_dir = "../public_html/KillQ/".$_POST["type"]." Images/";

$filename = $_FILES["fileToUpload"]["name"];
$arr = explode(".", $filename);
$extension = end($arr);
$newfilename = $_POST["ID"] .".".$extension;

$target_file = $target_dir . basename($newfilename);


$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submitImg"])) {
  $uploadOk = 1;
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check == false) {
    $uploadOk = 0;
  }
  if ($_FILES["fileToUpload"]["size"] > 500000) {
    array_push($_SESSION["imgErrorMsg"],"Sorry, your file is too large.");
    $uploadOk = 0;
  }
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
    array_push($_SESSION["imgErrorMsg"],"Sorry, only JPG, JPEG & PNG files are allowed.");
    $uploadOk = 0;
  }
  
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    if ($_POST["type"] == "Product"){
          header('Location: edit_product.php?sku='.$_POST["ID"].'');
    } else {
          header('Location: manage_operations.php');
    }
  } else {
    $mask = ''.$_POST["ID"].'*.*';
    array_map('unlink', glob($target_dir."/".$mask));
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      if ($_POST["type"] == "Product"){
          $query = "UPDATE products set image = 'http://mp02.projectsbit.org/KillQ/".$_POST["type"]."%20Images/".$_POST["ID"].".".$extension."' where ".$_POST["type"]."_id=".$_POST["ID"]."";
          mysqli_query($conn, $query);
          header('Location: edit_product.php?sku='.$_POST["ID"].'');
      } else {
          $query = "UPDATE merchants set image = 'http://mp02.projectsbit.org/KillQ/".$_POST["type"]."%20Images/".$_POST["ID"].".".$extension."' where ".$_POST["type"]."_id=".$_POST["ID"]."";
          mysqli_query($conn, $query);
          header('Location: manage_operations.php');
      }
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }
}
?>