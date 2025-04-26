<?php
use GuzzleHttp\Client;
    class POSHttpClient {
        private static GuzzleHttp\Client | null $client = null;

        public static function get_http_client() {
            if(self::$client) {
                return self::$client;
            }
            self::$client = new Client(['base_uri'=> $GLOBALS['CONFIG']['POS_MIDDLEWARE_URL'], "timeout" => 10.0, "headers" => [
                "x-api-key" => $GLOBALS['CONFIG']['POS_MIDDLEWARE_API_KEY']
            ]]);
            return self::$client;
        }
    }
?>