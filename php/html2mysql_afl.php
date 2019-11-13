<?php
//set_time_limit(60);   no timeout
$URL='https://www.footywire.com/afl/footy/ft_match_list';

//$conn = mysqli_connect("localhost", "root", "cancer56", "test");
//$sql = "SELECT  ticker2 as ticker, count(*) as num_of_recs FROM stocks";
//$rs = mysqli_query($conn, $sql);

//while( $rows = mysqli_fetch_assoc($rs) ) {
	$rows = getRows($URL);        // 1) get data from internet

echo "show results of ". $URL;
print_r($rows);

//	insertRecords($conn, $rows);					 // 2) update a ticker to fintable

//	mysqli_query($conn, "UPDATE stocks set updated_at = now() where ticker2 ='".$ticker."'"); // 3) acknowledge update with timestamp to stocks
//	echo "Completed insertion for ".$ticker."<br>";      // send to console.log ?????
//}
//mysqli_close($conn);

echo json_encode(array("Insertion completed for ".$URL));

//========================================	
function getYear($strDate) {
	$objDate=DateTime::createFromFormat('Y-m-d', $strDate);
	return $objDate->format("Y");
}

function getRows($url) {
	$htmlContent = file_get_contents($url);
echo "look for table";
    // 2) get tables
	$regex = '/<table>(.*)<\/table>/';
	preg_match_all($regex,$htmlContent,$match);
	print_r($match);
die();

	$pattern2='/<\/table>/';
	$array=preg_split($pattern2, $htmlContent, -1, PREG_SPLIT_NO_EMPTY);
	for ($i = 7; $i < count($array); $i++) {
		echo "****".$i."***<BR>";
		print_r($array[$i]);
	}
die();
/*
echo "5)***";
print_r($results[5]);
echo "8)***";
$result8=$results[8];
echo "8) more ";
$resultx=parseTable($result8);
print_r($resultx);
*/

echo "ALL)";
print_r($results);
die();



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

function insertRecords($conn, $rows) {
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
