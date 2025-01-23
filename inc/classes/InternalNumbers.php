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
}
