<?php
require_once "DBConnection.php";
class InternalNumbers
{

    public static function get_random_number(int $digits) {
        return rand(pow(10, $digits - 1) , pow(10, $digits) -1);
    }
    public static function get_customer_number()
    {
        do {
            $candidate = self::get_random_number(12);
            $dbconnection = DBConnection::get_db_connection();
            $sql = "SELECT * FROM customers WHERE customer_number = ?";
            $stmt = $dbconnection->prepare($sql);
            $stmt->execute([$candidate]);
        } while ($stmt->rowCount() > 0);

        return $candidate;
    }

    public static function get_product_number()
    {
        do {
            $candidate = self::get_random_number(8);
            $dbconnection = DBConnection::get_db_connection();
            $sql = "SELECT * FROM products WHERE product_id = ?";
            $stmt = $dbconnection->prepare($sql);
            $stmt->execute([$candidate]);
        } while ($stmt->rowCount() > 0);

        return $candidate;
    }

    public static function get_sku(int $product_id, int $variant_id) {
        return $product_id . "-" . $variant_id;
    }
}
