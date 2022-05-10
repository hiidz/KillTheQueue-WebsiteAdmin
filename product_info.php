<!-- This file is the product info page, which is a PDF page. It contains all the js/html/php codes. A PHP  library, TCPDF, is used to generate PDF -->
<!-- The product info page allows user to view the product info and QR code of the selected product -->
<?php
    session_start();
    include("config.php");
    
    //Checking for Login Session. If does not exist, redirect to login page
    if(!isset($_SESSION["loggedin"]) && !isset($_SESSION["userid"]))
    {
       header("Location: index.php");
    }
    
    $query = 'SELECT product_id as SKU, product_name, original_cost, price, category, age_restricted, brand, description, image
            from products
            where product_id="'. $_GET['sku'] .'"
            and merchant_id = "'.$_SESSION['merchantid'].'"
            and status="active";';

    $results = mysqli_query($conn,$query);
    if(mysqli_num_rows($results) > 0){
        $fetchresult = mysqli_fetch_array($results);
        $sku = $fetchresult[0];
        $productname = $fetchresult[1];
        $originalcost = $fetchresult[2];
        $price = $fetchresult[3];
        $category = $fetchresult[4];
        $agerestricted = $fetchresult[5];
        $brand = $fetchresult[6];
        $description = $fetchresult[7];
        $image = $fetchresult[8];
        if (@getimagesize($image) == true) {
            $image = $image;
        } else {
            $image = "http://mp02.projectsbit.org/KillQ/Product%20Images/default.jpg";
        }
    }else{
        header('location:error.html');
    }
    
// Include the main TCPDF library (search for installation path).
require_once('TCPDF/tcpdf.php');
require_once('TCPDF/config/tcpdf_config.php');
require_once('TCPDF/tcpdf_barcodes_2d.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Kill The Queue');
$pdf->SetTitle('Product Name');
$pdf->SetSubject('Product Info');
$pdf->SetKeywords('KillTheQueue, Product, Info, QR, ID');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $_SESSION['merchantname'], PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// NOTE: 2D barcode algorithms must be implemented on 2dbarcode.php class file.

// set font
$pdf->SetFont('helvetica', '', 11);

// add a page
$pdf->AddPage();




// print a message
$sku = "SKU: ".$sku;
$productName = "Product Name: ".$productname;
$productBrand = "Brand: ".$brand;
$originalPrice = "Original Cost: $".$originalcost;
$sellingPrice = "Selling Price: $".$price;
$category = "Category: ".$category;
$ageRestricted = "Age Restriction: ".$agerestricted;
$description = "Description: ".$description;


// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)


$pdf->MultiCell(70, 50, $sku, 0, 'L', false, 1, 125, 30, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $productName, 0, 'L', false, 1, 125, 45, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $productBrand, 0, 'L', false, 1, 125, 60, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $originalPrice, 0, 'L', false, 1, 125, 75, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $sellingPrice, 0, 'L', false, 1, 125, 90, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $category, 0, 'L', false, 1, 125, 105, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $ageRestricted, 0, 'L', false, 1, 125, 120, true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(70, 50, $description, 0, 'L', false, 1, 125, 135, true, 0, false, true, 0, 'T', false);


$pdf->SetFont('helvetica', '', 10);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// set style for barcode
$style = array(
    'border' => 2,
    'padding' => 'auto',
    'bgcolor' => array(211,211,211),
    'fgcolor' => array(139,0,0)
);
 - - -

// QRCODE,L : QR-CODE Low error correction
$pdf->write2DBarcode($_GET["sku"], 'QRCODE,1', 20, 30, 65, 65, $style, 'N');

//writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true);

$pdf->writeHTMLCell(65, 65, '20', '110', '<img src="'.$image.'"/>', 'LRTB', 1, 0, true, 'L', true);


//$pdf->writeHTML('<img src="http://mp02.projectsbit.org/KillQ/Product%20Images/3.jpg" alt="test alt attribute" width="200" height="200" border="0" />', true, false, true, false, '');

//$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,1', 20, 90, 50, 50, $style, 'N');
// -------------------------------------------------------------------


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_050.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
?>