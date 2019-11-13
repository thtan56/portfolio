<?php
// previous - json_fav_data.php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "cancer56", "test");

if ($_GET['type']=='F') {
	$condition="stocks.favorite ='".$_GET['groupid']."'";	
} else {
	$condition="stocks.sector ='".$_GET['groupid']."'";
};

$condition2 = "year(daily_prices.date) > 2017";

$sql = "SELECT  stocks.ticker2 as ticker, daily_prices.date as date, daily_prices.close as price".
	" FROM stocks, daily_prices ".
    " where stocks.ticker = daily_prices.ticker AND ". $condition. " AND " .$condition2.
    " ORDER by stocks.ticker2, daily_prices.date ";

$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data_points = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
//    $point = array("date" => $rows['date'] , "price" => $rows['price']);
//    array_push($data_points, $point); 
	$data_points[] = $rows;
}
mysqli_close($conn);
echo json_encode($data_points, JSON_NUMERIC_CHECK);
?>