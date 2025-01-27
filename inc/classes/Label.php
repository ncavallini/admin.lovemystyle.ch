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
       $this->httpClient = new Client(['base_uri'=> $GLOBALS['CONFIG']['POS_MIDDLEWARE_URL'], "timeout" => 2.0]);
   }

   private function get_xml() {
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

   public function preview() {
        
        $base64 = $this->httpClient->request("POST",  "/label/preview", [
            "json" => ["xml" => $this->get_xml()]
        ])->getBody()->getContents();

        return "<img style='border-style: solid' src='data:image/png;base64,$base64'>";
    
   }

   public function print() {

   }

   public function download() {


   }
}
?>