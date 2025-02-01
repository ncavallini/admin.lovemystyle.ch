<?php
require_once __DIR__ . "/DBConnection.php";
$CONFIG = $GLOBALS['CONFIG'];

use GuzzleHttp\Client;
class Label {

   private string $name;
   private string $supplier;
   private string $color;
   private string $size;
   private string $sku;
   private int $price;
   private GuzzleHttp\Client $httpClient;

   public function __construct(string $name, string $supplier, string $color, string $size, string $sku, int $price) {
       $this->name = $name;
       $this->supplier = $supplier;
       $this->color = $color;
       $this->size = $size;
       $this->sku = $sku;
       $this->price = $price;
       $this->httpClient = POSHttpClient::get_http_client();
   }

   public static function get_from_variant(int|string $productId, int|string $variantId) {
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT v.*, p.*, s.name AS supplier_name FROM product_variants v JOIN products p USING(product_id) JOIN suppliers s USING(supplier_id) WHERE product_id = ? AND variant_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$productId, $variantId]);
    $variant = $stmt->fetch();
    if(!$variant) {
        throw new Error("Variante non trovata.");
    }
    $sku = InternalNumbers::get_sku($productId, $variantId);
    $label = new Label($variant['name'], $variant["supplier_name"], $variant['color'], $variant['size'], $sku, $variant['price']);
    return $label;
   }

   private function get_xml(): string {
        $xml = file_get_contents(__DIR__ . "/../../templates/labels/product_label.dymo");
        return Utils::str_replace([
            "%product_name" => $this->name,
            "%supplier" => $this->supplier,
            "%size" => $this->size,
            "%color" => $this->color,
            "%sku" => $this->sku,
            "%price" => Utils::format_price($this->price)
        ], str: $xml);
   }

   public static function get_test_label_xml(): string {
    $xml = file_get_contents(__DIR__ . "/../../templates/labels/test_label.dymo");
    return Utils::str_replace([
        "%datetime" => date("d/m/Y H:i:s"),
    ], $xml);
   }

   public function preview() {
        
        $base64 = $this->httpClient->request("POST",  "/label/preview", [
            "json" => ["xml" => $this->get_xml()]
        ])->getBody()->getContents();

        return "<img style='border-style: solid' src='data:image/png;base64,$base64'>";
    
   }

   public function print(string $printerName, int $copies = 1) {
    $xml = $this->get_xml();
    for($i = 1; $i <= $copies; $i++) {
        $this->httpClient->post("/label/print", [
            "json" => [
                "xml" => $xml,
                "printerName" => $printerName,
                "copies" => $copies
            ]
        ]);
    }
    
   }

   public function download() {
    $xml = $this->get_xml();
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=Etichetta-{$this->sku}.dymo");
    header("Content-Length: " . strlen($xml));
    echo $xml;
   }
}
?>