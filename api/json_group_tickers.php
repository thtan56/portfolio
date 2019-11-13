<?php
header('Content-Type: application/json');

switch ($_GET['type']) {
	case 'F': $cond = "stocks.favorite='".$_GET['groupid']."'"; break;
	case 'S': $cond = "stocks.sector='"    .$_GET['groupid']."'"; break;
}

$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$sql = "SELECT  ticker, count(*) as num_of_recs ".
	" FROM stocks".
    " where  ". $cond .
    " group by ticker ";
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