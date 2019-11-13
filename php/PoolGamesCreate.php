<?php
require __DIR__.'/DBclass.php';

echo "Please wait, generating game pools....";
set_time_limit(0);

$dbObj = new DB();
$db = $dbObj->getPDO(); 
//-------------------------------     1           2         3            13                   
$sql = "insert into pool_games(organiser, home_team, away_team, date, round, status, game_id";
$sql .= ",scheme, bet_type, entry_cost, entry_quorum, pool_prize, payout";
$sql .= ",home_odd, away_odd, odd_date, created)";
$sql .= " values (?,?,?,   ?,?,?,   ?,?,?,   ?,?,?,   ?,?,?, ?, now())";
$pginsert = $db->prepare($sql);

$sql  = "Select game_id, scheme from pool_games ";
$sql .= " where game_id=? and scheme=? ";
$pgselect = $db->prepare($sql);
// --------------------------------------------------
$sql = "select scheme, pool_type, entry_cost, entry_quorum, pool_prize, payout, odd_date, home_odd, away_odd from pool ";
$pselect = $db->prepare($sql);  $pselect->execute(); 
$pools = $pselect->fetchAll(PDO::FETCH_ASSOC);
//------------------------------------
$sql  = "Select organiser, date, home_team, away_team, round, status, id from game where status='open' ";
$gselect = $db->prepare($sql);  $gselect->execute();
$rowCount=$gselect->rowCount();
print("<br> Number of games: $rowCount <br>");

$today = date("Y-m-d H:i:s"); 
print("Time start: $today <br>");

$current=0;
//---------------------------------------------------
while ($game = $gselect->fetch(PDO::FETCH_ASSOC)) {
	$current++;
	outputProgress($current, $rowCount);
	
	foreach ($pools as $pool) {
		$pgselect->execute([ $game['id'], $pool['scheme'] ]);
    if ($pgselect->rowCount() == 0) {   // not found (no duplicates)
			$pginsert->execute([ $game['organiser'], $game['home_team'], $game['away_team'], $game['date'], $game['round'], $game['status'], $game['id']
  	         ,$pool['scheme'], $pool['pool_type'], $pool['entry_cost'], $pool['entry_quorum'],$pool['pool_prize'], $pool['payout']
  	         ,$pool['home_odd'], $pool['away_odd'], $pool['odd_date'] ]);
    };			
	}
};
$today = date("Y-m-d H:i:s"); 
print("Time end: $today <br>");
echo "done";

$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
echo "<BR><a href='$url'>back</a>";


function outputProgress($current, $total) {
    echo "<span style='position: absolute;z-index:$current;background:#FFF;'>" . round($current / $total * 100) . "% (".$current.") </span>";
    myFlush();
//    sleep(1);      // sleep 1 second so we can see the delay
}

/**
 * Flush output buffer
 */
function myFlush() {
    echo(str_repeat(' ', 256));     // this is for the buffer, achieve the minumum size in order to flush data
    if (@ob_get_contents()) {
        @ob_end_flush();
    }
    flush();    // send output to browser immediately
}

?>


