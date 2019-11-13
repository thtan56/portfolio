<?php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "cancer56", "test");

$sql = "SELECT  stocks.ticker2 as ticker, stocks.lastprice as price, stocks.sector as sector, stocks.favorite as favorite, ".
	" sharpes.sharpe as sharpe, sharpes.maxdrawdown as mdd, sharpes.close as close, ".
	" sharpes.retavg as retavg, sharpes.retsd as sd ".
	" FROM stocks ". 
	" INNER JOIN sharpes ON stocks.ticker = sharpes.ticker ".
    " ORDER BY stocks.ticker2" ;

$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data_points = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data_points[] = $rows;
}
mysqli_close($conn);
echo json_encode($data_points, JSON_NUMERIC_CHECK);
?>
