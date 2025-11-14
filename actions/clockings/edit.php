<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
Auth::require_owner();
$sql = "UPDATE clockings SET datetime = :datetime, type = :type WHERE clocking_id = :clocking_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
  'datetime' => $_POST['datetime'],
  'type' => $_POST['clocking_type'],
  'clocking_id' => $_POST['clocking_id']
]);
?>
<script>
    window.history.go(-2);
</script>
