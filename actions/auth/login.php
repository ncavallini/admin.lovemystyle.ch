<?php
require_once __DIR__ . "/../../inc/inc.php";

$username = $_POST['username'];
$password = $_POST['password'];

if (Auth::login($username, $password)) {
    $returnTo = isset($_GET['returnTo']) ? urldecode($_GET['returnTo']) : "index.php";

    $sql = "SELECT needs_password_change FROM users WHERE username = :username";
    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user && $user['needs_password_change']) {
        header("Location: /../../index.php?page=users_reset-password&username=" . urlencode($username) . "&returnTo=" . urlencode($returnTo));
        die;
    }

    header("Location: /../../index.php?page=home");
} else {

    header("Location: /../../index.php?page=login&error=1");
}
?>