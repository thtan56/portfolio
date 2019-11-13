<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');              
require_once('./configDb.php');
//require_once('./configLog.php');
//$logger = getLogger();
$sql =  "select * from sharpe_filter_list"; 
//$logger->info('1) json_mybet.php', array('sql' => $sql));
$pdo = getPdoConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
//$stmt = null;
echo json_encode($arr, TRUE);   // array instead of object
?>
