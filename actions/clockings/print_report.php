<?php
mb_internal_encoding("UTF-8");

require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
// Create an instance of the class:

$username = $_GET['username'] ?? "";
$name = Auth::get_fullname_by_username($username);
$month = $_GET['month'] ?? "";
$year = $_GET['year'] ?? "";

$sql = "SELECT * FROM users WHERE username = ?"; 
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch();
$stmt->execute();

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . "/../../assets/fonts",
    ]),
    'fontdata' => $fontData + [ // lowercase letters only in font key
        'quiche' => [
            'R' => 'QuicheDisplay-Light.ttf',
            'I' => 'QuicheDisplay-LightItalic.ttf',
        ],
        "times" => [
            'R' => 'times.ttf',
            'B' => 'timesbd.ttf',
            'I' => 'timesi.ttf',
        ],
        "default_font" => "times"
    ],
    
]);

$mpdf->charset_in = "utf-8";

$mpdf->title = "Foglio ore {$name} {$month}-{$year}";

$html = file_get_contents(__DIR__ . "/../../templates/internals/clockings_report.html");

$sql = "SELECT *, date(datetime) as date FROM clockings WHERE username = :username AND MONTH(datetime) = :month AND YEAR(datetime) = :year ORDER BY datetime asc";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":username" => $username,
    ":month" => $month,
    ":year" => $year
]);

$clockings = $stmt->fetchAll();

$days = [];
foreach ($clockings as $clocking) {
    $day = date("j", strtotime($clocking['datetime']));
    $days[$day][] = $clocking;
}

$sums = [];

foreach($days as $day => $clockings) {
    $sums[$day] = 0;
    for ($i = 0; $i < count($clockings); $i += 2) {
        $in = new DateTimeImmutable($clockings[$i]["datetime"]);
        if ($i + 1 >= count($clockings)) {
            break;
        }
        $out = new DateTimeImmutable($clockings[$i + 1]["datetime"]);
        $sums[$day] += $out->getTimestamp() - $in->getTimestamp();
    }
}

$rows = "";

foreach($sums as $d => $sum) {
    $rows .= "<tr class='table-row'>";
    $rows .= "<td class='table-data'>" . Utils::format_date("{$year}-{$month}-{$d}") . "</td>";
    $rows .= "<td class='table-data text-right'>" . gmdate("H:i:s", $sum) . "</td>";
    $rows .= "</tr>";
}

$total = array_reduce($sums, function($carry, $item) {
    return $carry + $item;
}, 0);

$total = Utils::roundToQuarterHour($total);

$details = "";

if($_GET["type"] === "details") {
    $details = "<h2>Dettaglio timbrature</h2>";
    $details .= "<table class='table'>";
    $details .= "<thead class='table-head'>";
    $details .= "<tr class='table-row'>";
    $details .= "<th class='table-header'>Data</th>";
    $details .= "<th class='table-header'>Ora</th>";
    $details .= "<th class='table-header'>Tipo</th>";
    $details .= "</tr>";
    $details .= "</thead>";
    $details .= "<tbody>";

    foreach($clockings as $clocking) {
        $details .= "<tr class='table-row'>";
        $details .= "<td class='table-data'>" . Utils::format_date($clocking['date']) . "</td>";
        $details .= "<td class='table-data'>" . (new DateTimeImmutable($clocking['datetime']))->format("H:i:s") . "</td>";
        $details .= "<td class='table-data'>" . ($clocking['type'] === "in" ? "Entrata" : "Uscita") . "</td>";
        $details .= "</tr>";
    }
    $details .= "</tbody>";
    $details .= "</table>";

}


$html = Utils::str_replace([
    "%full_name" => $name,
    "%street" => $user['street'],
    "%postcode" => $user['postcode'],
    "%city" => $user['city'],
    "%country" => Country::iso2name($user['country']),
    "%title" => "Foglio ore {$name} &mdash; {$month}/{$year}",
    "%rows" => $rows,
    "%total" => gmdate("G", $total) . "h " . gmdate("i", $total) . "min.",
    "%details" => $details

], $html);

// Write some HTML code:
$mpdf->WriteHTML($html);


// Output a PDF file directly to the browser
$mpdf->Output("Foglio ore {$name} {$month}-{$year}.pdf", "I");

?>