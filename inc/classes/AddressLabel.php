<?php
class AddressLabel implements DYMOLabel {

    private string $title;
    private string $first_name;
    private string $last_name;
    private string $street;
    private string $city;
    private string $postcode;
    private string $country;

    public function __construct(string $title, string $first_name, string $last_name, string $street, string $city, string $postcode, string $country) {
        $this->title = $title;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->street = $street;
        $this->city = $city;
        $this->postcode = $postcode;
        $this->country = $country;
    }
  
    public function get_xml(): string {
        $xml = file_get_contents(__DIR__ . "/../../templates/labels/address_label.dymo");
        return Utils::str_replace([
            "%title" => $this->title,
            "%first_name" => $this->first_name,
            "%last_name" => $this->last_name,
            "%street" => $this->street,
            "%city" => $this->city,
            "%postcode" => $this->postcode,
            "%country" => $this->country
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
        DYMOUtils::download($xml, "Etichetta Indirizzo-{$this->first_name} {$this->last_name}");
       }
}
?>