<?php
session_start();
require_once __DIR__ . "/DBConnection.php";
class Auth
{

    private static array $allowedPages = ['home', 'login', 'forgot_password'];
    public static function login(string $username, string $password): bool
    {
        $connection = DBConnection::get_db_connection();
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (!$user || !$user['is_enabled'])
            return false;

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }

    public static function is_logged(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function is_admin(): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user']['is_admin'] == 1;
    }

    public static function get_username(): string
    {
        return $_SESSION['user']['username'];
    }

    public static function is_page_allowed(string $page): bool
    {
        return in_array($page, self::$allowedPages) || self::is_logged();
    }

}