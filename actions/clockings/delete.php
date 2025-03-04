<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
Auth::require_owner();
$clocking_id = $_GET['clocking_id'] ?? "";
$sql = "DELETE FROM clockings WHERE clocking_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$clocking_id]);
?>
<script>
    window.history.go(-1);  
</script>