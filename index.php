<?php
require_once __DIR__ . "/components/head.php";
require_once __DIR__ . "/components/header.php";
require_once __DIR__ . "/inc/inc.php";



$page = $_GET['page'] ?? "home";

if (Auth::is_logged()) {
    require_once __DIR__ . "/components/nav_user.php";
} else {
    require_once __DIR__ . "/components/nav_minimal.php";

}

?>
<br>
<main id="main" class="container-fluid">
    <?php
    $pagePath = __DIR__ . "/pages/" . str_replace("_", "/", $page) . ".php";
    if (!file_exists($pagePath)) {
        Utils::print_error("La pagina richiesta non esiste.");
        goto footer;
    }

    if (!Auth::is_page_allowed($page)) {
        Utils::redirect("index.php?page=login&returnTo=" . urlencode("index.php?page=$page"));
    }

    require_once $pagePath;
    ?>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</main>



<?php
footer:
if(Auth::is_logged()) {
    require_once __DIR__ . "/components/footer.php";
}
?>

<script>
    document.body.setAttribute("data-pos-url", "<?php echo htmlspecialchars($GLOBALS['CONFIG']['POS_MIDDLEWARE_URL'], ENT_QUOTES, 'UTF-8'); ?>")
    JsBarcode(".barcode").init();
</script>
<?php if(false && Auth::is_logged()): ?>
<script src="/inc/pollPosStatus.js"></script>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toaster/5.1.0/js/bootstrap-toaster.min.js" integrity="sha512-LKHDVlxKQ+ChADdnDsXJYU7LaUdGJk1X+Ab2rbFU11cqm+vhp2PGOWQIrl6K1NRZxHAdwPOYLPINPvUIEyBtVQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>