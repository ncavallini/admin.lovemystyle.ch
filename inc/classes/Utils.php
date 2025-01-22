<?php
class Utils
{
    public static function print_error(string $message, bool $needs_bootstrap = false)
    {

        if ($needs_bootstrap) {
            echo <<<EOD
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
                EOD;
        }

        echo <<<EOD
                <div class="alert alert-danger" role="alert">
                    $message
                </div>
                EOD;
    }

    public static function redirect(string $destination)
    {
        echo <<<EOD
                <script>
                    window.location.href = "$destination";
                </script>
            EOD;
    }

   public static function print_table_row(?string $data, $class="", $colspan=1) {
        if($data == null) $data = "";
        echo "<td class='$class text-nowrap' colspan='$colspan'>$data</td>";
    }
    
    public static function get_full_address(string $street, string $postcode, string $city, string $country): string {
        return "$street, $postcode $city, " . Country::iso2name($country);
    }
    
    
    public static function get_salutation(string $gender)
    {
        if($gender === "M") return "Egregio Signor";
        else if($gender === "F") return "Gentile Signora";
        else return "Spettabile";
    }
    
    public static function format_phone_number(string $phone): string {
        if($phone === "") return "-";
        return (PhoneNumber::parse($phone))->format(PhoneNumberFormat::INTERNATIONAL) ?? "-";
    }
    
    public static function dropdown(array $items, string $divClass = 'dropdown', string $button = "<i class='fa-solid fa-gear'></i>") {
        $str = <<<EOF
        <div class='$divClass'>
        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">$button</button>
        <ul class="dropdown-menu">
    EOF;
    
        foreach($items as $key => $value) {
            if($value === "DIVIDER") {
                $str .= "<li><hr class='dropdown-divider'></li>";
            }
            else $str .= "<li><a class='dropdown-item' href='$value'>$key</a></li>";
        }
    
        $str .= "</div>";
        return $str;
    }
    
}
?>