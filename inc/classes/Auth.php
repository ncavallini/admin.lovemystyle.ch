<?php
session_start(['cookie_lifetime' => 43200,'cookie_secure' => true,'cookie_httponly' => true]);
require_once __DIR__ . "/DBConnection.php";
class Auth
{

    private static array $allowedPages = ['login', 'forgot_password'];
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
            $sql = "UPDATE users SET last_login_at = NOW() WHERE username = ?";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$username]);

            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            $_SESSION['user'] = $user;

            return true;
        }
        return false;
    }

    public static function sign_up(string $username, string $password, bool $is_admin = false): bool
    {
        $connection = DBConnection::get_db_connection();
        $sql = "INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT), $is_admin]);
        return $stmt->rowCount() == 1;
    }

    public static function is_logged(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function get_role(): string {
        return $_SESSION['user']['role'];
    }

    public static function is_owner(bool $includeAdmin = false): bool {
        $isOwner = isset($_SESSION['user']) && $_SESSION['user']['role'] == "OWNER";
        $isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] == "ADMIN";
        if($includeAdmin) {
            return $isOwner || $isAdmin;
        }
        else return $isOwner;
    }

    public static function is_admin(): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] == "ADMIN";
    }

    public static function require_admin() {
        if(!self::is_admin()) {
            Utils::print_error("Non hai i permessi per visualizzare questa pagina.");
            exit();
        }
    }

    public static function require_owner(): void {
        if(!self::is_owner() && !self::is_admin()) {
            Utils::print_error("Non hai i permessi per visualizzare questa pagina.");
            exit();
        }
    }

    public static function get_username(): string
    {
        return $_SESSION['user']['username'];
    }

    public static function get_fullname(): string {
        return $_SESSION['user']['first_name'] . " " . $_SESSION['user']['last_name'];
    }

    public static function is_page_allowed(string $page): bool
    {
        return in_array($page, self::$allowedPages) || self::is_logged();
    }

    public static function get_fullname_by_username(string $username): string
    {
        $connection = DBConnection::get_db_connection();
        $sql = "SELECT first_name, last_name FROM users WHERE username = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user['first_name'] . " " . $user['last_name'];
    }

}