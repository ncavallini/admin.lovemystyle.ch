<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();

// Query per recuperare tutto in un'unica chiamata
$sql = "SELECT p.*, v.*, b.name AS brand_name 
        FROM products p 
        JOIN product_variants v USING(product_id) 
        JOIN brands b ON p.brand_id = b.brand_id
        ORDER BY b.name ASC, p.name ASC, size ASC, color ASC";

$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

// Organizza i prodotti per brand
$brands_products = [];
foreach ($products as $product) {
    $brands_products[$product['brand_name']][] = $product;
}

// Carica il template HTML
$html = file_get_contents(__DIR__ . "/../../templates/internals/inventory.html");

$content = "<p>Data/Ora di estrazione: " . date("d/m/Y, H:i:s") . "</p>";

foreach ($brands_products as $brand_name => $products) {
    $content .= "<h2>{$brand_name}</h2>";
    $content .= "<table>";
    $content .= "<thead><tr>
                    <th>Nome</th>
                    <th>Prezzo (CHF)</th>
                    <th>SKU</th>
                    <th>EAN</th>
                    <th>Taglia</th>
                    <th>Colore</th>
                    <th>Stock</th>
                </tr></thead><tbody>";

    foreach ($products as $product) {
        $formattedPrice = Utils::format_price($product['price']);
        $sku = InternalNumbers::get_sku($product['product_id'], $product['variant_id']);

        $content .= "<tr>
                        <td>{$product['name']}</td>
                        <td align='right'>{$formattedPrice}</td>
                        <td>{$sku}</td>
                        <td class='barcodecell'><barcode code='{$sku}' type='C128B' class='barcode' /></td>
                        <td>{$product['size']}</td>
                        <td>{$product['color']}</td>
                        <td class='stock-{$product['stock']}'>{$product['stock']}</td>
                     </tr>";
    }

    $content .= "</tbody></table>"; // Chiusura corretta della tabella
    // newpage
    $content .= "<div style='page-break-after: always;'></div>";
}

$html = str_replace("%content", $content, $html);

$mpdf = new \Mpdf\Mpdf(["orientation" => "L"]);

// --- ADD FOOTER CORRECTLY ---
$footer = '<div style="font-size: 10px; text-align: right;">' . date("d/m/Y / H:i:s") . ' - Pagina {PAGENO} di {nb}</div>';
$mpdf->SetHTMLFooter($footer); // Set the footer globally
$mpdf->WriteHTML($html);
$mpdf->Output("Inventario.pdf", "I");
?>
