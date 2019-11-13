<?php
require_once('../php/configDb.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$data = file_get_contents('php://input');
$json = json_decode($data);

$pdo = getPdoConnection();
$stmt = $pdo->prepare('update game set status=?, odd=? where id=?');
$stmt->execute([
				$json->{'data'}->{'status'}	
				,$json->{'data'}->{'odd'}
				,$json->{'data'}->{'id'}
				]);
$stmt = null;
?>