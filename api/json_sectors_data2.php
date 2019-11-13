<?php
require_once('../php/configDb.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$data = file_get_contents('php://input');
$json = json_decode($data);

switch ($json->{'uom'}) {
	case 'sector':  $sql = 'select sector,   count(*) from stocks group by sector   having sector   <> "" '; break;
	case 'favorite':$sql = 'select favorite, count(*) from stocks group by favorite having favorite <> "" '; break;
};

$pdo = getPdoConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = null;
echo json_encode($arr, TRUE);
?>
