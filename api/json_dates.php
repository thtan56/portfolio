<?php
// previous - json_fav_data.php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "cancer56", "test");

$sql = "SELECT  date, count(*) ".
	" FROM daily_prices ".
    " GROUP BY date ".
    " HAVING year(date) > 2017 and month(date) > 3 " .
    " ORDER by date ";

$results = array();
$results[0]="ticker";
$i=1;
$rs = mysqli_query($conn, $sql);
while($rows = mysqli_fetch_array($rs)) {
	$results[$i++]=$rows[0];
}
mysqli_close($conn);
echo json_encode($results, JSON_NUMERIC_CHECK);