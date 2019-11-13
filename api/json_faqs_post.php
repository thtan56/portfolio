<?php
require_once('../php/configDb.php');
//require_once('../php/configLog.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

//$logger = getLogger();
$data = file_get_contents('php://input');
$data_array = json_decode($data, true);     // convert json string(/question/...) into php array format

$json_data = $data_array['data'];           // get data by extracting from array['data']
$json_id = $data_array['id'];

$encoded = json_encode($json_data);					// convert back to json string (for update to json field in mysql)

$pdo = getPdoConnection();
$stmt = $pdo->prepare('update faqs set notes=? where id=?');
$stmt->execute([ $encoded, $json_id ]);
$stmt = null;
?>