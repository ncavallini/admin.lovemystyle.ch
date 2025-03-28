<?php
class DYMOUtils {
    private static ?GuzzleHttp\Client $httpClient = null;

    private static function getClient(): GuzzleHttp\Client {
        if (self::$httpClient === null) {
            self::$httpClient = POSHttpClient::get_http_client();
        }
        return self::$httpClient;
    }

    public static function preview(string $xml): string {
        $base64 = self::getClient()->request("POST", "/label/preview", [
            "json" => ["xml" => $xml]
        ])->getBody()->getContents();

        if (!base64_decode($base64, true)) {
            throw new Exception("La risposta non è un'immagine base64 valida: " . substr($base64, 0, 200));
        }

        return "<img style='border-style: solid' src='data:image/png;base64,$base64'>";
    }

    public static function print(string $printerName, string $xml, int $copies = 1): void {
        for ($i = 1; $i <= $copies; $i++) {
            self::getClient()->post("/label/print", [
                "json" => [
                    "xml" => $xml,
                    "printerName" => $printerName,
                ]
            ]);
        }
    }

    public static function download(string $xml, string $name): void {
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$name.dymo");
        header("Content-Length: " . strlen($xml));
        echo $xml;
    }
}
?>
