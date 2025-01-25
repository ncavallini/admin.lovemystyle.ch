<?php
$productId = $_GET['product_id'] ?? "";
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM product_variants WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$productId]);

if(!$res) {
    Utils::print_error("Prodotto non trovato.");
    goto end;
}
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$productId]);
$product = $stmt->fetch();

?>
<h1>Varianti &mdash; <small><?php echo $product['name'] . " (<span class='tt'>" . $productId . "</span>)" ?></small> </h1>
<br>
<a href="/index.php?page=suppliers_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<?php
    if(count($variants) == 0) {
        echo "<div class='alert alert-primary' role='alert'>Nessuna variante trovata. Creane una con il pulsante + sopra.</div>";
        
    }
?>
<?php
    foreach ($variants as $variant) {
        echo <<<EOD
<div class="card">
  <div class="card-header card-title h5">
    Featured
  </div>
  <div class="card-body">
    <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>
EOD;
    }
?>

<?php end: ?>