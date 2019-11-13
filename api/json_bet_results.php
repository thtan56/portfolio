<?php
require_once('../php/configDb.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
//------------------------------------
function getOdd($row){
	$odd=0;
	switch ($row['bet_type']) {
		case "over" : $odd = $row['home_odd']; break;
		case "under": $odd = $row['home_odd']; break;
	  default:
			if        ($row['home_score'] > $row['away_score']) {	$odd = $row['home_odd'];
			} else if ($row['away_score'] > $row['home_score']) { $odd = $row['away_odd']; };
	};
  return $odd;
};
function getPoolPrize(PDO $pdo, $pool_id) {
	$sql30  = "select * from bet_pool where id=? ";
	$selectpool = $pdo->prepare($sql30);
	$selectpool->execute([$pool_id]);
	$pools = $selectpool->fetchAll(PDO::FETCH_ASSOC);
	return $pools[0]['pool_prize'];
};
//----------------------------------------------------
$pdo = getPdoConnection();

$sql10  = "select organiser, home_team, away_team, date ";
$sql10 .= ",home_score, away_score, id ";
$sql10 .= " from game where home_score > 0";
$sql11  = "update game set game_winner=? where id=?";

$sql12  = "update bet set game_winner=?, home_score=?, away_score=? ";
$sql12 .= " where organiser=? and home_team=? and away_team=? and game_date=?";

$updategame = $pdo->prepare($sql11);
$updatebet = $pdo->prepare($sql12);
$selectgame = $pdo->prepare($sql10);
//===================
// stage 1 game => bet
$selectgame->execute();
$gamearr = $selectgame->fetchAll(PDO::FETCH_ASSOC);
$count1=0;
foreach ($gamearr as $row) {
	if        ($row['home_score'] > $row['away_score']) {	$winner = $row['home_team'];
	} else if ($row['away_score'] > $row['home_score']) { $winner = $row['away_team'];
	};
	//---------------------------
	$updategame->execute([ $winner, $row['id'] ]);  // game id
	$updatebet->execute([ $winner, 
		$row['home_score'], $row['away_score'],
		$row['organiser'], $row['home_team'], $row['away_team'], $row['date'] ]);
	$count1++;
};
//=================================
// stage 2 - compute profit
$sql21  = "select home_score, away_score, home_odd, away_odd, bet_type, bet_amount, bet_score1";
$sql21 .= ",bet_winner, game_winner, pool_id, id from bet where home_score > 0 ";
$sql22  = "update bet set bet_score=? where id=? ";
$selectbet = $pdo->prepare($sql21);
$updatebet = $pdo->prepare($sql22);
$selectbet->execute();
$betarr = $selectbet->fetchAll(PDO::FETCH_ASSOC);
$count2=0;
foreach ($betarr as $row) {
	$totalscore=$row['home_score'] + $row['away_score'];
	$profit=0;
	
	$odd=getOdd($row);
	//-------------------------	
	switch ($row['bet_type']) {
		case "over" :
			if ($row['bet_score1'] > $totalscore) {
					$profit = $row['bet_amount'] *  $odd;
			};
			break;
		case "under" :
			if ($row['bet_score1'] < $totalscore) {
					$profit = $row['bet_amount'] *  $odd;
			};
			break;
		case "odd" :
			if ($row['bet_winner'] == $row['game_winner'] ) {
				$profit = $row['bet_amount'] * $odd;
			};
			break;
		case "head2head":
			if ($row['bet_winner'] == $row['game_winner'] ) {
				$profit = getPoolPrize($pdo, $row['pool_id']);
			};
			break;
		case "standard":
			if ($row['bet_winner'] == $row['game_winner'] ) {
				$profit = getPoolPrize($pdo, $row['pool_id']);
			};
		  break;
		default: break;
	};	
	$updatebet->execute([ $profit, $row['id'] ]);   // bet id
	$count2++;
};
$results=array( 'status'=> "json_bet_results updates winner successfully",
								'tables' => ["game","bet"],
							  'counts' => [$count1, $count2] );
echo json_encode($results, JSON_PRETTY_PRINT);
?>