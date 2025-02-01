<?php
    require_once __DIR__ . "/../actions_init.php";
    $productId = $_POST['product_id'] ?? "";
    $variantId = $_POST['variant_id'] ?? "";

    $label = Label::get_from_variant($productId, $variantId);
    $label->print($_POST["printer"], $_POST["copies"]);
    header("Location: /index.php?page=variants_label&product_id=$productId&variant_id=$variantId");
?>
