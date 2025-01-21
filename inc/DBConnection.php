<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/config.php";
$CONFIG = $GLOBALS['CONFIG'];
class DBConnection
{
    private static ?PDO $connection = null;
    private static function get_dsn(): string
    {
        global $CONFIG;
        return "mysql:host={$CONFIG['DB_HOSTNAME']};port={$CONFIG['DB_PORT']};dbname={$CONFIG['DB_NAME']}";
    }

    public static function get_db_connection(): PDO
    {
        global $CONFIG;
        if (self::$connection != null)
            return self::$connection;
        self::$connection = new PDO(self::get_dsn(), $CONFIG['DB_USERNAME'], $CONFIG['DB_PASSWORD'], [
            //PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return self::$connection;
    }


}