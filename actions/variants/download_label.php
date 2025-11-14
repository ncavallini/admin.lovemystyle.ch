<?php
    require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

    $productId = $_GET['product_id'] ?? "";
    $variantId = $_GET['variant_id'] ?? "";

    $label = ProductTagLabel::get_from_variant($productId, $variantId);
    $label->download();
?>

<script>
    window.close();
</script>
