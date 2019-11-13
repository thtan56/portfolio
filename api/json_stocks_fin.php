<?php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "cancer56", "test");
mysqli_set_charset($conn, 'utf8');

switch ($_GET['type']) {
	case 'F': $cond = "stocks.favorite='".$_GET['groupid']."'"; break;
	case 'S': $cond = "stocks.sector='"    .$_GET['groupid']."'"; break;
    case 'T': $cond = "stocks.ticker='"   .$_GET['groupid']."'"; break;
}

//$cond_period =" and year(fintable.date_announced) > 2017 and month(fintable.date_announced) > 3 ";
$sql = "SELECT  stocks.ticker as ticker, ".
	" concat(fintable.year,'q',fintable.quarter) as qtr,".
    " stocks.sector as sector, stocks.favorite as favorite, ".
    " fintable.eps as eps, ".
	" fintable.dividend as dividend, fintable.profit_margin as pm, ".
	" fintable.unit_price as price, fintable.date_announced as date ".
	" FROM stocks ". 
	" INNER JOIN fintable ON stocks.ticker = concat(fintable.ticker,'.KL') ".
    " WHERE ". $cond . 
    " ORDER BY stocks.ticker, concat(fintable.year,'q',fintable.quarter) ";
$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data_points = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data_points[] = $rows;
}
if (isset($_GET['version'])) {     // for vue-table2
	$last=round($resultset->num_rows / 10);
	$data_points=array(	"pagination"=>[
							"total"=> $resultset->num_rows,
							"per_page"=> 15,
							"current_page"=> 1,
							"last_page"=> $last,
							"next_page_url"=>"",
							"prev_page_url"=>null,
							"from"=> 1,
							"to"=> 15],
 						"data"=>$data_points);    // new additions for latest vue-table2 
};
//$results=json_encode($data_points, JSON_PRETTY_PRINT); JSON_NUMERIC_CHECK
//echo($results);
//die();

mysqli_close($conn);
echo json_encode($data_points, JSON_PRETTY_PRINT);
?>