<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$sql = "SELECT stocks.id as id, stocks.ticker as ticker, sector, pe, eps as earning, dps as dividend,".
       " lastprice, retavg, retsd, sharpe, maxdrawdown ".
	" FROM stocks, sharpes ".
    " where stocks.ticker = sharpes.ticker ".
    " AND stocks.sector ='".$_GET['sector']."'";
$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data[] = $rows;
}
mysqli_close($conn);
echo json_encode($data, JSON_PRETTY_PRINT);
?>
