<?php
require_once('../php/configDb.php');
//require_once('../php/configLog.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

//$logger = getLogger();
//$logger->info('_POST:', $_POST);

$data = file_get_contents('php://input');
$json = json_decode($data);

$pdo = getPdoConnection();
$stmt = $pdo->prepare('INSERT INTO USERS (username, email) VALUES (?, ?)');
$stmt->execute([ $_POST['username'], $_POST['email']	]);
$stmt = null;
$responses=array("message"=>"success");
echo json_encode($responses, JSON_PRETTY_PRINT);
?>
