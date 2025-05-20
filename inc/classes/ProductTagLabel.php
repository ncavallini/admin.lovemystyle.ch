<?php
require_once __DIR__ . "/DBConnection.php";
require_once __DIR__ . "/DYMOLabel.php";
require_once __DIR__ . "/DYMOUtils.php";
$CONFIG = $GLOBALS['CONFIG'];

use GuzzleHttp\Client;
class ProductTagLabel implements DYMOLabel {

   private string $name;
   private string $brand;
   private string|null $color;
   private string|null $size;
   private string $sku;
   private int $price;
   private GuzzleHttp\Client $httpClient;

   public function __construct(string $name, string $brand, string|null $color, string|null $size, string $sku, int $price) {
       $this->name = $name;
       $this->brand = $brand;
       $this->color = $color;
       $this->size = $size;
       $this->sku = $sku;
       $this->price = $price;
       $this->httpClient = POSHttpClient::get_http_client();
   }

   public static function get_from_variant(int|string $productId, int|string $variantId) {
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT v.*, p.*, b.name AS brand_name FROM product_variants v JOIN products p USING(product_id) JOIN brands b USING(brand_id) WHERE product_id = ? AND variant_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$productId, $variantId]);
    $variant = $stmt->fetch();
    if(!$variant) {
        throw new Error("Variante non trovata.");
    }
    $sku = InternalNumbers::get_sku($productId, $variantId);
    $label = new ProductTagLabel($variant['name'], $variant["brand_name"], $variant['color'], $variant['size'], $sku, $variant['price']);
    return $label;
   }

   public function get_xml(): string {
        $xml = file_get_contents(__DIR__ . "/../../templates/labels/product_label.dymo");
        $bom = "\xEF\xBB\xBF";
if (substr($xml, 0, 3) === $bom) {
    $xml= substr($xml, 3);
} 
        return Utils::str_replace(kv: [
            "%product_name" => Utils::format_pos(substr($this->name, 0, 16)),
            "%brand" => Utils::format_pos($this->brand),
            "%size" => $this->size ?? "",
            "%color" => !empty($this->color) ? Utils::format_pos($this->color) : "",
            "%sku" => $this->sku,
            "%price" => Utils::format_price($this->price)
        ], str: $xml, escape_html_entities: true);
   }

   public static function get_test_label_xml(): string {
    $xml = file_get_contents(__DIR__ . "/../../templates/labels/test_label.dymo");
    return Utils::str_replace([
        "%datetime" => date("d/m/Y H:i:s"),
    ], $xml);
   }

   public function preview(): string {
    $xml = $this->get_xml();
    return DYMOUtils::preview($xml);
   }

   public function print(string $printerName, int $copies = 1): void {
    $xml = $this->get_xml();
    DYMOUtils::print($printerName, $xml, $copies);
   }

   public function download(): void {
    $xml = $this->get_xml();
    DYMOUtils::download($xml, "Etichetta-{$this->sku}");
   }
}
?>