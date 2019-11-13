<?php
require_once('../php/configDb.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$pdo = getPdoConnection();  // old table: cash_flow
$stmt = $pdo->prepare('SELECT *  FROM request where username = ?');
$stmt->execute([ $_GET['username']	]);

$totalcash = 0;
$totalvcash = 0;
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($arr as $row) {
	$totalcash += $row['cash'];
  $totalvcash += $row['vcash'];
};
$result['data'] = $arr;
$result['totals']=["cash" => $totalcash, "vcash"=>$totalvcash];
$stmt = null;

echo json_encode($result, JSON_PRETTY_PRINT);
?>