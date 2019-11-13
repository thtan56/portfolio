<?php
require_once('../php/configDb.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$data = file_get_contents('php://input');
$json = json_decode($data);

$sql = "update bet set organiser=?, venue=?,  game_name=?, game_date=?, game_winner=?, home_score=?, away_score=?";
$sql .= ",bet_type=?,pool_id=?,bet_odd=?, bet_winner=?, username=?, bet_amount=?, bet_score1=?, week_no=week(game_date) ";
$sql .= "where id=?";

$pdo = getPdoConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute([
            $json->{'data'}->{'organiser'}         ,$json->{'data'}->{'venue'}
            ,$json->{'data'}->{'game_name'}        ,$json->{'data'}->{'game_date'}
            ,$json->{'data'}->{'game_winner'}      ,$json->{'data'}->{'home_score'}
            ,$json->{'data'}->{'away_score'}       ,$json->{'data'}->{'bet_type'}
            ,$json->{'data'}->{'pool_id'}          ,$json->{'data'}->{'bet_odd'}
            ,$json->{'data'}->{'bet_winner'}       ,$json->{'data'}->{'username'}
            ,$json->{'data'}->{'bet_amount'}       ,$json->{'data'}->{'bet_score1'}
            ,$json->{'data'}->{'id'} ]);
$stmt = null;
?>
