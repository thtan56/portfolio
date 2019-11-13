<?php
require_once('../php/configDb.php');
//require_once('../php/configLog.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$data = file_get_contents('php://input');
$json = json_decode($data);


//$logger = getLogger();
//$logger->info('1) json_basketball_matches.php', array('json' => $json));

$pdo = getPdoConnection();
$stmt = $pdo->prepare('SELECT * FROM bet_pool where id=?');
$stmt->execute([ $json->{'data'}->{'pool_id'} ]);     // get games from bet_pool (use bet)
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = null;
echo json_encode($arr,TRUE);
?>
