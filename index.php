<?php
require_once __DIR__ . "/components/head.php";
require_once __DIR__ . "/components/header.php";
require_once __DIR__ . "/inc/inc.php";


$page = $_GET['page'] ?? "home";
require_once __DIR__ . "/components/nav.php";
?>
<br>
<main id="main" class="container-fluid">
    <?php
    if (!file_exists(__DIR__ . "/pages/$page.php")) {
        Utils::print_error("La pagina richiesta non esiste.");
        goto footer;
    }

    if (!Auth::is_page_allowed($page)) {
        Utils::redirect("index.php?page=login&returnTo=" . urlencode("index.php?page=$page"));
    }

    require_once __DIR__ . "/pages/$page.php";
    ?>
</main>

<?php
footer:
require_once __DIR__ . "/components/footer.php";
?>