<?php
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM clockings WHERE username = ? AND DATE(datetime) = CURDATE() ORDER BY datetime";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([Auth::get_username()]);
$clockings = $stmt->fetchAll();

$nextType = (count($clockings) % 2 == 0) ? 'in' : 'out';

$workedToday = 0;
for ($i = 0; $i < count($clockings); $i += 2) {
  $in = new DateTimeImmutable($clockings[$i]["datetime"]);
  if($i + 1 >= count($clockings)) {
    break;
  }
  $out = new DateTimeImmutable($clockings[$i + 1]["datetime"]);
  $workedToday += $out->getTimestamp() - $in->getTimestamp();
}

$isWorking = count($clockings) % 2 == 1;


?>



<h1>Home</h1>
<p>Scegliere una funzione dal menu in alto per iniziare.</p>

<div class="card">
  <div class="card-header d-flex align-items-center">
    <img width="72px" height="64px" src="data:image/png;base64,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" alt="" class="me-2">
    <h2 class="mb-0">Timbratura &mdash; <?php echo date("d/m/Y") ?></h2>
  </div>
  <div class="card-body">
  
    <?php if ($nextType == 'in'): ?>
      <h3 class="card-title">Timbra entrata - Utente <i><?php echo Auth::get_fullname(); ?></i> </h3>
      <form action="actions/clockings/add.php" method="POST">
        <input type="hidden" name="type" value="in">
        <button type="submit" class="btn btn-success">Timbra entrata</button>
      </form>
    <?php else: ?>
      <h3 class="card-title">Timbra uscita - Utente <?php echo Auth::get_fullname(); ?> </h3>
      <p>Entrata alle: <?php echo (new DateTimeImmutable($clockings[count($clockings) - 1]["datetime"]))->format("H:i:s") ?> </p>
      <form action="actions/clockings/add.php" method="POST">
        <input type="hidden" name="type" value="out">
        <button type="submit" class="btn btn-danger">Timbra uscita</button>
      </form>
    <?php endif; ?>
    <br>
      <h3>Timbrature di oggi per <?php echo Auth::get_fullname(); ?></h3>
      <div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Ora</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
          <?php
            foreach($clockings as $clocking) {
              $trClass = ($clocking["type"] == "in") ? "table-success" : "table-danger";
              $prettyType = ($clocking["type"] == "in") ? "Entrata" : "Uscita";
              echo "<tr class='$trClass'>";
              echo "<td>" . (new DateTimeImmutable($clocking["datetime"]))->format("H:i:s") . "</td>";
              echo "<td>" . $prettyType . "</td>";
              echo "</tr>";
            }
          ?>
        </tbody>
        </table>
        </div>
        <p class="lead">
          Totale ore: <?php echo gmdate("H:i:s", $workedToday) ?>
          <?php if ($isWorking): ?>
             <span class="badge bg-success">In corso</span>
            <?php endif; ?>
        </p>
  </div>
</div>
