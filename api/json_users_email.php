<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');              
require_once('../php/configDb.php');
//require_once('../php/configLog.php');
//$logger = getLogger();

$condition = (isset($_GET['email'])) ? " where email='".$_GET['email']."'" : "";

$sql = "SELECT  * FROM users ". $condition;
//$logger->info('1) json_mybet.php', array('sql' => $sql));

$pdo = getPdoConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = null;
echo json_encode($arr, TRUE);   // array instead of object
?>