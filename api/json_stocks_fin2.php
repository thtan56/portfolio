<?php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "cancer56", "test");
mysqli_set_charset($conn, 'utf8');

$sql = "SELECT  stocks.id as id, stocks.ticker2 as ticker, concat(fintable.year,'q',fintable.quarter) as qtr, fintable.eps as eps, ".
	" fintable.dividend as dividend, fintable.profit_margin as pm, ".
	" fintable.unit_price as price, fintable.date_announced as date ".
	" FROM stocks ". 
	" INNER JOIN fintable ON stocks.ticker2 = fintable.ticker ".
    " WHERE stocks.favorite='F1' " .
    " ORDER BY stocks.ticker2, concat(fintable.year,'q',fintable.quarter) ";

$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data_points = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data_points[] = $rows;
}
$last=round($resultset->num_rows / 10);
	$data_points=array(
			"total"=> $resultset->num_rows,
			"per_page"=> 10,
			"current_page"=> 1,
			"last_page"=> $last,
			"next_page_url"=>"json_stocks_fin2.php?page=2",
			"prev_page_url"=>null,
			"from"=> 1,
			"to"=> 15,
		"data"=>$data_points);    // new additions for latest vue-table2 
	
	$x1=json_encode($data_points, JSON_PRETTY_PRINT);
//	$pattern = '/(\t|\n|\r)/m';        // \s = white space (include space???); /m = multi-lines
    $pattern = '/\s+/';
    $replace = ' ';
    $removedws = preg_replace( $pattern, $replace,$x1);
//  	print_r($removedws);
//	die();
//$results=json_encode($data_points, JSON_PRETTY_PRINT); JSON_NUMERIC_CHECK
//echo($results);
//die();

mysqli_close($conn);
echo $removedws;
?>
