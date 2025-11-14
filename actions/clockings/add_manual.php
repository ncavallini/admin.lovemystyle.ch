<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
Auth::require_owner();
$sql = "INSERT INTO clockings VALUES(UUID(), :username, :datetime, :type)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
  'username' => $_POST['username'],
  'datetime' => $_POST['datetime'],
  'type' => $_POST['clocking_type']
]);
?>

<script>
    window.history.go(-2);
</script>
