<?php
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/DBConnection.php";
global $dbconnection;
$dbconnection = DBConnection::get_db_connection();

use Firebase\JWT\JWT;
use PKPass\PKPass;


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


    public function get_apple_pass() {
        $CONFIG = $GLOBALS['CONFIG'];
        $pass = new PKPass($CONFIG["PKPASS_CERTIFICATE_FILE_PATH"] , $CONFIG["PKPASS_CERTIFICATE_PASSWORD"]);

        global $dbconnection;

        $sql = "SELECT * FROM customers WHERE customer_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$this->customer_id]);
        $customer = $stmt->fetch();
        if (!$customer) {
            Utils::print_error("Cliente non trovato.", true);
            die;
        }

        $serial = strval($customer['customer_number']);
        $name = $customer['first_name'] . ' ' . $customer['last_name'];

        $data = [
            "passTypeIdentifier" => "pass.ch.lovemystyle.loyalty",
            "formatVersion" => 1,
            "organizationName" => "Love My Style",
            "teamIdentifier" => "22UG3QMHJ7",
            "serialNumber" => $serial,
            "backgroundColor" => "rgb(195,215,238)",
            "foregroundColor" => "rgb(7,29,73)",
            "logoText" => "Love My Style",
            "description" => "Love My Style",
            "locations" => [
                [
                    "latitude" => 46.481299,
                    "longitude" => 9.833591,
                ],
            ],
            "storeCard" => [
                "secondaryFields" => [
                    [
                        "key" => "name",
                        "label" => "Cliente",
                        "value" => $name,
                    ],
                ],
            ],
            "barcode" => [
                "format" => "PKBarcodeFormatCode128",
                "message" => $serial,
                "messageEncoding" => "iso-8859-1",
                "altText" => $serial,
            ],
        ];
        

    $pass->setData($data);

        
        
    // Add files to the pass package
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/icon.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/icon@2x.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/icon@3x.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/logo.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/logo@2x.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/logo@3x.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/strip.png');
    $pass->addFile(__DIR__ . '/../../assets/logo/apple/strip@2x.png');

    
    
    // Create and output the pass
    return $pass->create(false);
    }
    
}
