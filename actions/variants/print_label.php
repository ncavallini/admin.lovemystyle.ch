<?php
    require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

    $productId = $_POST['product_id'] ?? "";
    $variantId = $_POST['variant_id'] ?? "";

    $label = ProductTagLabel::get_from_variant($productId, $variantId);
    $label->print($_POST["printer"], $_POST["copies"]);
    header("Location: /index.php?page=variants_label&product_id=$productId&variant_id=$variantId");
?>
