<?php
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/DBConnection.php";
global $dbconnection;
$dbconnection = DBConnection::get_db_connection();

use Firebase\JWT\JWT;

class LoyaltyCard
{
    private string $customer_id;

    public function __construct(string $customer_id)
    {
        $this->customer_id = $customer_id;
    }

    public function get_google_pass_link()
    {
        global $dbconnection;

        $sql = "SELECT * FROM customers WHERE customer_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$this->customer_id]);
        $customer = $stmt->fetch();
        if (!$customer) {
            Utils::print_error("Cliente non trovato.", true);
            die;
        }




        $serviceAccount = file_get_contents(__DIR__ . "/../../private/google_wallet_service_account.json");
        $serviceAccount = json_decode($serviceAccount, true);
        $payload = [
            "iss" => $serviceAccount['client_email'],
            "aud" => "google",
            "typ" => "savetowallet",
            "iat" => time(),
            "exp" => time() + 3600,
            "payload" => [
                "genericObjects" => [[
                    "id" => "3388000000022888169.pass_" . uniqid(),
                    "classId" => "3388000000022888169.lovemystyle_generic_1",
                    "state" => "active",
                    "cardTitle" => [
                        "defaultValue" => [
                            "language" => "it-CH",
                            "value" => "Carta Fedeltà Love My Style"
                        ]
                    ],
                    "header" => [
                        "defaultValue" => [
                            "language" => "it-CH",
                            "value" => "Love My Style"
                        ]
                    ],
                    // ✅ This places the customer's full name directly on the pass!
                    "subheader" => [
                        "defaultValue" => [
                            "language" => "it-CH",
                            "value" => $customer['first_name'] . " " . $customer['last_name'],
                        ]
                    ],
                    "barcode" => [
                        "type" => "CODE_128",
                        "value" => $customer['customer_number'],
                        "alternateText" => $customer['customer_number']
                    ],

                    "logo" => [
                        "sourceUri" => [
                            "uri" => "https://admin.lovemystyle.ch/assets/logo/logo_color_google_wallet.png"
                        ],
                        "contentDescription" => [
                            "defaultValue" => [
                                "language" => "it-CH",
                                "value" => "Love My Style Logo"
                            ]
                        ]
                    ],

                    "hexBackgroundColor" => "#c3d7ee",
                    "heroImage" => [
                        "sourceUri" => [
                            "uri" => "https://admin.lovemystyle.ch/assets/logo/google_hero.png"
                        ]
                    ]
                ]]
            ]
        ];

        // Generate JWT
        $jwt = JWT::encode($payload, $serviceAccount['private_key'], 'RS256');

        // Generate Google Wallet pass URL


        $jwt = JWT::encode($payload, $serviceAccount['private_key'], 'RS256');

        return "https://pay.google.com/gp/v/save/" . $jwt;
    }
}
