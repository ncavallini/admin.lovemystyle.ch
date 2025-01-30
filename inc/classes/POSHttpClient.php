<?php
use GuzzleHttp\Client;
    class POSHttpClient {
        private static GuzzleHttp\Client | null $client = null;

        public static function get_http_client() {
            if(self::$client) {
                return self::$client;
            }
            self::$client = new Client(['base_uri'=> $GLOBALS['CONFIG']['POS_MIDDLEWARE_URL'], "timeout" => 2.0]);
            return self::$client;
        }
    }
?>