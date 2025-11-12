<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = Utils::get_mpdf(["format" => [148, 210]]);
$mpdf->title = "Display prezzi";

// Retrieve and decode JSON data from GET parameter
$raw = $_POST['products'];	

$products = json_decode($raw, true);

// Validate and ensure you have an array of IDs
if (!is_array($products) || empty($products)) {
    var_dump($products);
   Utils::redirect("/index.php?page=products_view");
    die;
}

// Generate placeholders dynamically based on product IDs
$placeholders = implode(',', array_fill(0, count($products), '?'));


$html = file_get_contents(__DIR__ . "/../../templates/internals/price_display.html");

$tbody = "";


foreach($products as $product) {
    $tbody .= "<tr>";
    $tbody .= "<td style='width: 50%; padding: 5px 0;'>" . htmlspecialchars($product["it"]) . "</td>";
    $tbody .= "<td style='width: 30%; padding: 5px 0;'>" . htmlspecialchars($product["brandName"]) . "</td>";
    $tbody .= "<td style='width: 20%; text-align: right; padding-right: 0.5cm; padding: 5px 0; white-space: nowrap;'>"
              . ($product["price"]) 
              . "</td>";
    $tbody .= "</tr>"; 
}
$html = Utils::str_replace(["%tbody_it" => $tbody], $html);


$tbody = "";
foreach($products as $product) {
    $tbody .= "<tr>";
    $tbody .= "<td style='width: 50%; padding: 5px 0;'>" . htmlspecialchars($product["de"]) . "</td>";
    $tbody .= "<td style='width: 30%; padding: 5px 0;'>" . htmlspecialchars($product["brandName"]) . "</td>";
    $tbody .= "<td style='width: 20%; text-align: right; padding-right: 0.5cm; padding: 5px 0; white-space: nowrap;'>"
              . ($product["price"]) 
              . "</td>";
    $tbody .= "</tr>";
    
}
$html = Utils::str_replace(["%tbody_de" => $tbody], $html);


$tbody = "";
foreach($products as $product) {
    $tbody .= "<tr>";
    $tbody .= "<td style='width: 50%; padding: 5px 0;'>" . htmlspecialchars($product["en"]) . "</td>";
    $tbody .= "<td style='width: 30%; padding: 5px 0;'>" . htmlspecialchars($product["brandName"]) . "</td>";
    $tbody .= "<td style='width: 20%; text-align: right; padding-right: 0.5cm; padding: 5px 0; white-space: nowrap;'>"
              . ($product["price"]) 
              . "</td>";
    $tbody .= "</tr>";
    
}

$html = Utils::str_replace(["%tbody_en" => $tbody], $html);


$mpdf->WriteHTML($html);

$mpdf->Output("Display prezzi.pdf", "I");
