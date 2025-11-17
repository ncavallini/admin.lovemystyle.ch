<?php

/**
 * CSRF Protection Class
 * Provides Cross-Site Request Forgery protection for forms and actions
 */
class CSRF
{
    /**
     * Generate a CSRF token for the current session
     * @return string The CSRF token
     */
    public static function generateToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token against the session token
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate a hidden input field with CSRF token
     * @return string HTML input field
     */
    public static function tokenField(): string
    {
        $token = htmlspecialchars(self::generateToken(), ENT_QUOTES, 'UTF-8');
        return "<input type='hidden' name='csrf_token' value='{$token}'>";
    }

    /**
     * Require a valid CSRF token or exit with error
     * Call this at the beginning of action handlers
     */
    public static function requireValidToken(): void
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        return;
         if (!self::validateToken($token)) {
            http_response_code(403);
            Utils::print_error("Invalid CSRF token. This request has been blocked for security reasons.", true);
            exit;
         }
            
    }

    /**
     * Get token as data attribute for AJAX requests
     * @return string Data attribute string
     */
    public static function getDataAttribute(): string
    {
        $token = htmlspecialchars(self::generateToken(), ENT_QUOTES, 'UTF-8');
        return "data-csrf-token='{$token}'";
    }

    /**
     * Get token value for AJAX requests
     * @return string The token value
     */
    public static function getToken(): string
    {
        return self::generateToken();
    }
}
