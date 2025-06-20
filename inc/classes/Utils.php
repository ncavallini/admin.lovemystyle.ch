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
        if (empty($country)) return "";
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
        $formatted = "";
        try {
            $formatted = (PhoneNumber::parse($phone))->format(PhoneNumberFormat::INTERNATIONAL) ?? "-";
        } catch (\Brick\PhoneNumber\PhoneNumberParseException $e) {
            $formatted = $phone; // Fallback to original if parsing fails
        }
        if ($tel_link) {
            return "<a href='tel:$phone'>$formatted</a>";
        }
        return $formatted;
    }

    public static function format_iban(string | null $iban): string
    {
        if (empty($iban)) return "-";
        $cleanedIban = preg_replace('/[^A-Za-z0-9]/', '', $iban);

        // Group the IBAN into chunks of 4 characters separated by spaces
        $formattedIban = trim(chunk_split($cleanedIban, 4, ' '));

        return $formattedIban;
    }

    public static function get_phone_regex()
    {
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
                $str .= "<li><a class='dropdown-item' href='$value' target='_blank'>$key</a></li>";
        }

        $str .= "</div>";
        return $str;
    }

    public static function format_date(string|null $date_mysql)
    {
        if (empty($date_mysql) || $date_mysql === NULL) return "-";
        $date = new DateTime($date_mysql);
        return $date->format("d/m/Y");
    }

    public static function format_datetime(string|null $date_mysql)
    {
        if (empty($date_mysql) || $date_mysql === NULL) return "-";
        $date = new DateTime($date_mysql);
        return $date->format("d/m/Y, H:i:s");
    }

    public static function format_duration(int $seconds, bool $print_seconds = true): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return $print_seconds ? sprintf('%d:%02d:%02d', $hours, $minutes, $secs) : sprintf('%d:%02d', $hours, $minutes);
    }

    public static function price_to_db(float $price): int
    {
        return (int) ($price * 100);
    }

    public static function format_price(int|float $price): string
    {
        if (is_float($price)) {
            $price = round($price, 2, PHP_ROUND_HALF_UP);
        }
        return MoneyUtils::format_price_int((int)$price, "CHF");
    }

    public static function str_replace(array $kv, string $str, bool $escape_html_entities = false)
    {
        if($escape_html_entities) {
            foreach($kv as $key => $value) {
                $kv[$key] = htmlentities($value);
            }
        }
        return str_replace(array_keys($kv), array_values($kv), $str);
    }

    public static function print_status_icon(string | int $status)
    {
        if ($status === "OK" || $status == 0) {
            echo "<i class='fa-solid fa-check-circle text-success'></i>";
        } else if ($status === "WARN" || $status == 1) {
            echo '<i class="fa-solid fa-triangle-exclamation text-warning"></i>';
        } else if ($status === "ERROR" || $status == 2) {
            echo "<i class='fa-solid fa-times-circle text-danger'></i>";
        } else return;
    }


    public static function compute_discounted_price(int|null $subtotal, float|null $discount, string $discountType)
    {
        if($subtotal === null) return 0;
        if ($discountType === "CHF") {
            return $subtotal - $discount * 100;
        } else {
            return $subtotal * (1 - $discount / 100);
        }
    }

    public static function format_pos(string $text): string
    {
        return self::str_replace([
            "à" => "a'",
            "è" => "e'",
            "ì" => "i'",
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

    public static function uuidv4(): string
    {
        $data = random_bytes(16);
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function roundToQuarterHour($timestamp)
    {
        // Estrarre i minuti dal timestamp
        $minutes = date('i', $timestamp);
        $seconds = date('s', $timestamp);

        // Calcolare il numero di quarti d'ora
        $quarter = floor($minutes / 15);

        // Determinare la soglia per arrotondare verso l'alto
        $threshold = 7.5; // Minuti per decidere l'arrotondamento

        // Calcolare quanti minuti sono passati dall'inizio dell'ultimo quarto d'ora
        $minutesInQuarter = $minutes % 15 + ($seconds > 0 ? 1 : 0);

        if ($minutesInQuarter > $threshold) {
            // Arrotondare al quarto d'ora successivo
            $newMinutes = ($quarter + 1) * 15;
        } else {
            // Arrotondare al quarto d'ora precedente
            $newMinutes = $quarter * 15;
        }

        // Creare il nuovo timestamp arrotondato
        return mktime(date('H', $timestamp), $newMinutes, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp));
    }

    public static function get_mpdf(array $config = [], $debug = false)
    {
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf(array_replace_recursive([
            "format" => [148, 210],
            "default_font_size" => 7,
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . "/../../assets/fonts",
            ]),
            'fontdata' => $fontData + [ // lowercase letters only in font key
                'quiche' => [
                    'R' => 'QuicheDisplay-Light.ttf',
                    'I' => 'QuicheDisplay-LightItalic.ttf',
                ],
                'times' => [
                    'R' => 'times.ttf',
                    'B' => 'timesbd.ttf',
                    'I' => 'timesi.ttf',
                    'BI' => 'timesbi.ttf',
                ],
            ],
            "default_font" => 'times'

        ], $config));
        $mpdf->setAutoBottomMargin = 'stretch';
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->charset_in = 'UTF-8';
        $mpdf->allow_charset_conversion = true;
        $mpdf->dpi = 72;
        $mpdf->debug = $debug;
        return $mpdf;
    }

    public static function create_toast(string $title, string $message, string $type = "INFO", int $timeout = 3000) {
        $type = trim(strtoupper($type));
        $typeMapping = [
            "SUCCESS" => 1,
            "DANGER" => 2,
            "WARNING" => 3,
            "INFO" => 4,
        ];
        
        if(!array_key_exists($type, $typeMapping)) {
            $type = "INFO";
        }
        $json = json_encode([
            "title" => $title,
            "message" => $message,
            "type" => $typeMapping[$type],
            "timeout" => $timeout
        ]);
        echo <<<EOD
            <script>
                setToast($json);
            </script>
        EOD;
    }
    

    public static function get_days_in_month($month, $year)
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public static function get_next_closing_datetime() {
        $currentDay = date('w'); // 0 (Sunday) to 6 (Saturday)
        $addedDays = 0;
        $nextDay = ($currentDay + 1) % 7; // Get the next day, wrapping around to Sunday if needed
        $addedDays = 1; // Start with one day added
        while(!isset($GLOBALS['CONFIG']['OPENING_TIMES'][$nextDay]) || empty($GLOBALS['CONFIG']['OPENING_TIMES'][$nextDay])) {
            $nextDay = ($nextDay + 1) % 7; // Skip to the next day if the current one is closed
            $addedDays++;
        }
        $nextClosingTime = end($GLOBALS['CONFIG']['OPENING_TIMES'][$nextDay]);
        $nextClosingDateTime = new DateTime();
        $nextClosingDateTime->modify("+$addedDays day");
        $nextClosingDateTime->setTime(
            (int) explode(':', $nextClosingTime)[0], // Hour
            (int) explode(':', $nextClosingTime)[1] // Minute
        );
        return $nextClosingDateTime->format('Y-m-d H:i:s');
    }


}
