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
</main>

<?php
footer:
require_once __DIR__ . "/components/footer.php";
?>

<script>
    JsBarcode(".barcode").init();
</script>