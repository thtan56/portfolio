<?php
set_time_limit(360);   // no timeout

$URL="http://stock.osfvad.com/stock_financial_summary.php?stockname=";
$conn = mysqli_connect("localhost", "root", "cancer56", "test");

switch( $_GET['type'] ){
	case "F": $entity = "stocks.favorite"; break;
	case "S": $entity = "stocks.sector"; break;
	case "T": $entity = "stocks.ticker"; break;
	default:  $entity = ""; exit;
};
$condition = $entity . "='".$_GET['groupid']."'";

$cond2= " ( date(updated_at)<>date(now()) or updated_at is null )";   // skip if already updated today
//$condition2 = "updated_at";
$sql = "SELECT  ticker, count(*) as num_of_recs FROM stocks".
    " where  ". $condition . " AND " . $cond2 .
     " group by ticker ";

$rs = mysqli_query($conn, $sql);
while( $rows = mysqli_fetch_assoc($rs) ) {
	$ticker = $rows['ticker'];
	$pieces = explode(".", $ticker);
	$ticker2 = $pieces[0];  // without .KL <************************************************************

	$rows = getRows($URL.urlencode($ticker2));        // 1) get data from internet
	insertRecords($conn, $rows);					 // 2) update a ticker to fintable

	mysqli_query($conn, "UPDATE stocks set updated_at = now() where ticker ='".$ticker."'"); // 3) acknowledge update with timestamp to stocks
	echo "Completed insertion for ".$ticker."<br>";      // send to console.log ?????
}
$sql9="UPDATE sectors set e_updated = now(), e_update=1 where name ='".$_GET['groupid']."'";
mysqli_query($conn, $sql9);

mysqli_close($conn);
echo json_encode(array("Insertion completed:".$condition));

//========================================	
function getYear($strDate) {
	$objDate=DateTime::createFromFormat('Y-m-d', $strDate);
	return $objDate->format("Y");
}
function getRows($url) {
	$htmlContent = file_get_contents($url);
	// 1) remove tab, newline, cr (otherwise some records missing
	$pattern = '/(\t|\n|\r)/m';        // \s = white space (include space???); /m = multi-lines
    $replace = '';
    $removedWhitespace = preg_replace( $pattern, $replace,$htmlContent);
    // 2) get rows
	$regex = '/<tr><td>(.*)<\/td><\/tr>/';
	preg_match_all($regex,$removedWhitespace,$match);
	$rows= $match[0][0];
//	print_r($rows);
//	die();
	// 3) replaces header, footer with blank & td,td with ;
	$pattern=array('/<td>RM /','/<\/td><td>/', '/(<tr><td>|<\/td><\/tr>|cents|%|,)/', '/&amp;/');
	$row2s=preg_replace($pattern, array('<td>',';','','&'), $rows);
	// 4) transform to array (explode)
	$pattern2='/<\/tr>/';
	$results=preg_split($pattern2, $row2s, -1, PREG_SPLIT_NO_EMPTY);
	return $results;	
}

function insertRecords($conn, $rows) {   // ticker w/o .KL
	$sql2 ="INSERT INTO fintable(ticker, stock_name, date_announced, year_end, year, quarter, ". 
			" revenue, eps, dividend, unit_price, profit_margin) VALUE (?,?,?,?,?,?,?,?,?,?,?)";			
	$stmt2 = $conn->prepare($sql2);

	$sql1  = "SELECT ticker, year, quarter from fintable where ticker = ? and year = ? and quarter = ? ";
	$stmt1 = $conn->prepare($sql1); 
    
   	$qtrs = array("1","2","3","4");
	foreach($rows as $row) {       // $rows = internet data
		$fields=preg_split('/;/', $row, -1, PREG_SPLIT_NO_EMPTY);     // 1) explode into array		
		$year= getYear($fields[4]);
		if (in_array($fields[5], $qtrs )) {     // skip if quarter is not 1,2,3,4
			$stmt1->bind_param("sii", $fields[1], $year, $fields[5]);     // 2) non-existent ticker (+year,qtr) in fintable then do insertion
			$stmt1->execute();		
			$rs1 = $stmt1->get_result();
			if ($rs1->num_rows == 0)	{       // if not found => insert record
				$stmt2->bind_param("ssssiiddddd", $fields[1], $fields[2], $fields[3], $fields[4], $year, $fields[5],
							$fields[6],  $fields[10], $fields[11], $fields[19], $fields[22]);
		//                                                                      PM			
				$stmt2->execute();
			}
		}
	}
	$stmt1->close();
	$stmt2->close();
} 
?>
