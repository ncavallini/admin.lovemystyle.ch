<?php
//session_start();
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");
require_once __DIR__ . "/../inc/inc.php";


if (!Auth::is_logged() && !(isset($_POST['tablet']) || isset($_GET['tablet']))) {
    header("Location: /index.php?page=login");
    exit;
}


?>