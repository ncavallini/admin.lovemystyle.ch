<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$format = $_GET['format'] ?? "xlsx";

$sql = "SELECT p.*, v.*, b.name AS brand_name 
        FROM products p 
        JOIN product_variants v USING(product_id) 
        JOIN brands b ON p.brand_id = b.brand_id
        ORDER BY b.name ASC, p.name ASC, size ASC, color ASC";

$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll();

const startRow = 5;
const startCol = 1; // A


$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
$spreadsheet = $reader->load(__DIR__ . "/../../templates/internals/inventory.xlsx");

$sheet = $spreadsheet->getActiveSheet();

$col = startCol;
$row = startRow;
$largestCol = $col;

foreach($data as $dataRow) {
        $sheet->setCellValue([$col, $row], $dataRow['name']);
        $col++;
        $sheet->setCellValue([$col, $row], $dataRow['brand_name']);
        $col++;
        $sheet->setCellValue([$col, $row], Utils::format_price($dataRow['price']));
        $col++;
        $sheet->setCellValue([$col, $row], InternalNumbers::get_sku($dataRow['product_id'], $dataRow['variant_id']));
        $col++;
        $sheet->setCellValue([$col, $row], $dataRow['size']);
        $col++;
        $sheet->setCellValue([$col, $row], $dataRow['color']);
        $col++;
        $sheet->setCellValue([$col, $row], $dataRow['stock']);

        $largestCol = $col;
        $col = startCol;
        $row++;
}


// Set table range
$sheet->getTableByName("inventory")->setRange([startCol, startRow -1, $largestCol, $row - 1]);

$filename = "inventory_" . date("Y-m-d_H-i-s") . ".xlsx";
$spreadsheet->setActiveSheetIndex(0);
$spreadsheet->getActiveSheet()->setTitle("Inventario");



header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=$filename");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
