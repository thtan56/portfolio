<?php
$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$sql = "SELECT stocks.id as id, stocks.ticker as ticker, sector, pe, eps as earning, dps as dividend,".
       " lastprice, retavg, retsd, sharpe, maxdrawdown ".
	" FROM stocks, sharpes ".
    " where stocks.ticker = sharpes.ticker ".
    " AND stocks.favorite ='".$_GET['favorite']."'";
$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data[] = $rows;
}
mysqli_close($conn);
echo json_encode($data);
?>
