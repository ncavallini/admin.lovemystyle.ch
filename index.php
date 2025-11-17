<?php
error_reporting(E_ALL );
ini_set('display_errors', '1');
require_once __DIR__ . "/inc/inc.php";
require_once __DIR__ . "/components/head.php";
require_once __DIR__ . "/components/header.php";

?>

<meta name="csrf-token" content="<?php echo htmlspecialchars(CSRF::generateToken(), ENT_QUOTES, 'UTF-8'); ?>">

<?php 

$page = $_GET['page'] ?? "home";

// SECURITY: Validate page parameter to prevent path traversal
if (strpos($page, '..') !== false || strpos($page, '/') !== false || strpos($page, '\\') !== false) {
    Utils::print_error("Invalid page request.");
    goto footer;
}

if (Auth::is_logged()) {
    require_once __DIR__ . "/components/nav_user.php";
} else {
    require_once __DIR__ . "/components/nav_minimal.php";

}

?>
<br>
<main id="main" class="container-fluid">
        <button class="btn btn-sm btn-outline-secondary text-align-left" onclick="window.history.go(-1)"><i class="fa fa-arrow-left"></i></button>
        <button class="btn btn-sm btn-outline-secondary text-align-right" onclick="window.history.go(1)"><i class="fa fa-arrow-right"></i></button>
         <button class="btn btn-sm btn-outline-secondary text-align-right" onclick="window.location.reload()"><i class="fa fa-rotate-right"></i></button>
        <p>&nbsp;</p>
   <?php
    $pagePath = __DIR__ . "/pages/" . str_replace("_", "/", $page) . ".php";

    // SECURITY: Verify the resolved path is within pages directory
    $realPagesDir = realpath(__DIR__ . "/pages");
    $realPagePath = realpath($pagePath);

    // Check if file exists and is within pages directory
    if ($realPagePath === false || !file_exists($realPagePath)) {
        Utils::print_error("La pagina richiesta non esiste.");
        goto footer;
    }

    if (strpos($realPagePath, $realPagesDir) !== 0) {
        Utils::print_error("Invalid page request.");
        goto footer;
    }

    if (!Auth::is_page_allowed($page)) {
        Utils::redirect("index.php?page=login&returnTo=" . urlencode("index.php?page=$page"));
    }

    require_once $realPagePath;
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
<?php if(Auth::is_logged()): ?>
<script src="/inc/pollPosStatus.js"></script>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toaster/5.1.0/js/bootstrap-toaster.min.js" integrity="sha512-LKHDVlxKQ+ChADdnDsXJYU7LaUdGJk1X+Ab2rbFU11cqm+vhp2PGOWQIrl6K1NRZxHAdwPOYLPINPvUIEyBtVQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) return;

  document.querySelectorAll('form[method="post"]').forEach(form => {
    // Skip forms where you explicitly don't want CSRF (if any)
    if (form.hasAttribute('data-no-csrf')) return;

    // Avoid duplicate field
    if (form.querySelector('input[name="csrf_token"]')) return;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'csrf_token';
    input.value = token;
    form.appendChild(input);
  });
});
</script>