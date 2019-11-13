<?php
require __DIR__.'/DBclass.php';

$dbObj = new DB();
$db = $dbObj->getPDO(); 

//$sql = "SELECT id, name, today, nav, cash, adjustment FROM daily_balance ";
$sql =  "select * from my_daily_profit";     // view
$stmt = $db->prepare($sql);
$stmt->execute();
$resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = null;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
echo json_encode($resp);
?>