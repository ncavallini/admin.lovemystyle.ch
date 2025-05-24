<h1>Ricerca rapida</h1>
<?php
    $query = $_GET['q'] ?? "";
    $query = trim($query);
    if(empty($query)) {
        Utils::print_error("Nessun termine di ricerca specificato");
        return;
    }
    $dbconnection = DBConnection::get_db_connection();

    $sql = "SELECT * FROM sales WHERE sale_id LIKE ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute(["%$query%"]);
    $sales = $stmt->fetchAll();

    $sql = "SELECT product_id, name FROM products WHERE product_id LIKE :query OR name LIKE :query UNION SELECT product_id, p.name AS name FROM product_variants v JOIN products p USING(product_id) WHERE CONCAT(product_id, '-', LPAD(variant_id, 4, '0')) LIKE :query";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([":query" => "%$query%"]);
    $products = $stmt->fetchAll();


    $uniqueProducts = [];
    foreach ($products as $item) {
        $id = $item['product_id'];
        if (!isset($uniqueProducts[$id])) {
            $uniqueProducts[$id] = $item;
        }
    }
    $products = $uniqueProducts;


    $sql = "SELECT * FROM brands WHERE name LIKE :query ORDER BY name asc";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([":query" => "%$query%"]);
    $brands = $stmt->fetchAll();

    
    $sql = "SELECT * FROM suppliers WHERE name LIKE :query ORDER BY name asc";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([":query" => "%$query%"]);
    $suppliers = $stmt->fetchAll();


    $sql = "SELECT * FROM customers WHERE first_name LIKE :query OR last_name LIKE :query OR customer_number LIKE :query ORDER BY last_name asc";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([":query" => "%$query%"]);
    $customers = $stmt->fetchAll();

    $sql = "SELECT * FROM gift_cards WHERE card_id LIKE :query ORDER BY card_id asc";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([":query" => "%$query%"]);
    $gift_cards = $stmt->fetchAll();

?>
<p>Termine di ricerca: <i><?php echo htmlspecialchars($query) ?></i> </p>

<div class="card">
  <div class="card-header">
    <p class="h5">Vendite (<?php echo count($sales)  ?>)</p>
  </div>
  <ul class="list-group list-group-flush">
    <?php foreach($sales as $sale): ?>
    <li class="list-group-item">
      <a href="index.php?page=sales_view&q=<?php echo $sale['sale_id'] ?>">
      <i class="fa-solid fa-arrow-right"></i> Vendita <span class="tt"><?php echo $sale['sale_id'] ?></span> del <?php echo Utils::format_date($sale['created_at']) ?>
      </a>
    </li>
    <?php endforeach ?>
  </ul>
</div>

<p>&nbsp;</p>

<div class="card">
  <div class="card-header">
    <p class="h5">Prodotti (<?php echo count($products)  ?>)</p>
  </div>
  <ul class="list-group list-group-flush">
    <?php foreach($products as $product): ?>
    <li class="list-group-item">
      <a href="index.php?page=products_view&q=<?php echo $product['product_id'] ?>">
      <i class="fa-solid fa-arrow-right"></i> <?php echo $product['name'] ?> (<span class="tt"><?php echo $product['product_id'] ?></span>)
      </a>
    </li>
    <?php endforeach ?>
  </ul>
</div>

<p>&nbsp;</p>

<div class="card">
  <div class="card-header">
    <p class="h5">Brand (<?php echo count($brands)  ?>)</p>
  </div>
  <ul class="list-group list-group-flush">
    <?php foreach($brands as $brand): ?>
    <li class="list-group-item">
      <a href="index.php?page=brands_view&q=<?php echo $brand['name'] ?>">
      <i class="fa-solid fa-arrow-right"></i> <?php echo $brand['name'] ?>
      </a>
    </li>
    <?php endforeach ?>
  </ul>
</div>

<p>&nbsp;</p>

<div class="card">
  <div class="card-header">
    <p class="h5">Fornitori (<?php echo count($suppliers)  ?>)</p>
  </div>
  <ul class="list-group list-group-flush">
    <?php foreach($suppliers as $supplier): ?>
    <li class="list-group-item">
      <a href="index.php?page=suppliers_view&q=<?php echo $supplier['name'] ?>">
      <i class="fa-solid fa-arrow-right"></i> <?php echo $supplier['name'] ?>
      </a>
    </li>
    <?php endforeach ?>
  </ul>
</div>

<p>&nbsp;</p>

<div class="card">
  <div class="card-header">
    <p class="h5">Clienti (<?php echo count($customers)  ?>)</p>
  </div>
  <ul class="list-group list-group-flush">
    <?php foreach($customers as $customer): ?>
    <li class="list-group-item">
      <a href="index.php?page=customers_view&q=<?php echo $customer['customer_number'] ?>">
      <i class="fa-solid fa-arrow-right"></i> <?php echo $customer["first_name"] . " " . $customer['last_name'] . ", " . Utils::format_date($customer['birth_date']) ?> (<span class="tt"><?php echo $customer["customer_number"] ?></span>)
      </a>
    </li>
    <?php endforeach ?>
  </ul>
</div>

<p>&nbsp;</p>

<div class="card">
  <div class="card-header">
    <p class="h5">Carte Regalo (<?php echo count($gift_cards)  ?>)</p>
  </div>
  <ul class="list-group list-group-flush">
    <?php foreach($gift_cards as $gift_card): ?>
    <li class="list-group-item">
      <a href="index.php?page=giftcards_view&q=<?php echo $gift_card['card_id'] ?>">
      <i class="fa-solid fa-arrow-right"></i> <?php echo $gift_card['card_id'] ?>
      </a>
    </li>
    <?php endforeach ?>
  </ul>
</div>