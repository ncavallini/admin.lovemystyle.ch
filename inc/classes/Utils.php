<?php
require_once __DIR__ . "/MoneyUtils.php";
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
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

    public static function print_table_row(?string $data, $class = "", $colspan = 1)
    {
        if ($data == null)
            $data = "";
        echo "<td class='$class text-nowrap' colspan='$colspan'>$data</td>";
    }

    public static function format_address(string $street = "", string $postcode = "", string $city = "", string | null $country = ""): string
    {
        if(empty($country)) return "";
        return "$street, $postcode $city, " . Country::iso2name($country);
    }


    public static function get_salutation(string $gender)
    {
        if ($gender === "M")
            return "Egregio Signor";
        else if ($gender === "F")
            return "Gentile Signora";
        else
            return "Spettabile";
    }

    public static function format_phone_number(string $phone, bool $tel_link = false): string
    {
        if (empty($phone))
            return "-";
        $formatted = (PhoneNumber::parse($phone))->format(PhoneNumberFormat::INTERNATIONAL) ?? "-";
        if ($tel_link) {
            return "<a href='tel:$phone'>$formatted</a>";
        }
        return $formatted ;
    }

    public static function format_iban(string | null $iban) : string {
        if(empty($iban)) return "-";
        $cleanedIban = preg_replace('/[^A-Za-z0-9]/', '', $iban);

    // Group the IBAN into chunks of 4 characters separated by spaces
    $formattedIban = trim(chunk_split($cleanedIban, 4, ' '));

    return $formattedIban;
    }

    public static function get_phone_regex() {
            return "\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$";
    }

    public static function dropdown(array $items, string $divClass = 'dropdown', string $button = "<i class='fa-solid fa-gear'></i>")
    {
        $str = <<<EOF
        <div class='$divClass'>
        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">$button</button>
        <ul class="dropdown-menu">
    EOF;

        foreach ($items as $key => $value) {
            if ($value === "DIVIDER") {
                $str .= "<li><hr class='dropdown-divider'></li>";
            } else
                $str .= "<li><a class='dropdown-item' href='$value'>$key</a></li>";
        }

        $str .= "</div>";
        return $str;
    }

    public static function format_date(string $date_mysql)
    {
        $date = new DateTime($date_mysql);
        return $date->format("d/m/Y");
    }

    public static function format_datetime(string $date_mysql)
    {
        $date = new DateTime($date_mysql);
        return $date->format("d/m/Y, H:i:s");
    }

    public static function price_to_db(float $price): int {
        return (int) ($price * 100);
    }

    public static function format_price(int|float $price): string {
        if(is_float($price)) {
            $price = round($price, 2, PHP_ROUND_HALF_UP);
        }
        return MoneyUtils::format_price_int((int)$price, "CHF");
    }

    public static function str_replace(array $kv, string $str) {
        return str_replace(array_keys($kv), array_values($kv), $str);
    }

    public static function print_status_icon(string | int $status) {
        if($status === "OK" || $status == 0) {
            echo "<i class='fa-solid fa-check-circle text-success'></i>";
        } else if($status === "WARN" || $status == 1) {
            echo '<i class="fa-solid fa-triangle-exclamation text-warning"></i>';
        } else if($status === "ERROR" || $status == 2) {
            echo "<i class='fa-solid fa-times-circle text-danger'></i>";
        }
        else return;
    }


    public static function compute_discounted_price(int $subtotal, float $discount, string $discountType) {
        if($discountType === "CHF") {
            return $subtotal - $discount;
        }
        else {
            return $subtotal * (1 - $discount / 100);
        }
    }

    public static function format_pos(string $text): string {
        return self::str_replace([
            "à" => "a'",
            "è" => "e'",
            "ì", "i'",
            "ò" => "o'",
            "ù" => "u'",
            "á" => "a'",
            "é" => "e'",
            "í" => "i'",
            "ó" => "o'",
            "ú" => "u'",
            "À" => "A'",
            "È" => "E'",
            "Ì" => "I'",
            "Ò" => "O'",
            "Ù" => "U'",
            "Á" => "A'",
            "É" => "E'",
            "Í" => "I'",
            "Ó" => "O'",
            "Ú" => "U'",
            "ä" => "ae",
            "ë" => "e",
            "ï" => "i",
            "ö" => "oe",
            "ü" => "ue",
            "Ä" => "Ae",
            "Ë" => "E",
            "Ï" => "I",
            "Ö" => "Oe",
            "Ü" => "Ue",

        ], $text);
    }

    public static function uuidv4(): string {
        $data = random_bytes(16);
    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
?>