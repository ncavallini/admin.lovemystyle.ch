<?php
require_once __DIR__ . "/../../inc/inc.php";

$username = $_POST['username'];
$password = $_POST['password'];

if(Auth::login($username, $password)) {
    $returnTo = isset($_GET['returnTo']) ? urldecode($_GET['returnTo']) : "index.php";
    header("Location: /../../index.php?page=home");
}
else {

    header("Location: /../../index.php?page=login&error=1");
}
?>