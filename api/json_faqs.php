<?php
require_once('../php/configDb.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$condition = (isset($_GET['id'])) ? " where id=".$_GET['id'] : "";

$sql = "SELECT  * FROM faqs ". $condition;
$pdo = getPdoConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = null;
echo json_encode($arr, TRUE);
?>