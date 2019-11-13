<?php

	$ticker = $_GET['ticker'];


$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$sql = "SELECT  ticker2, updated_at, date(updated_at) FROM stocks";
		"where ticker2='KUCHAI'".
$cond2= " date(updated_at)<>date(now()) or updated_at is null";
$rs = mysqli_query($conn, $sql);
$rows = mysqli_fetch_assoc($rs);
print_r($rows);
die();


	$url="http://stock.osfvad.com/stock_financial_summary.php?stockname=".urlencode($ticker);
	$conn = mysqli_connect("localhost", "root", "cancer56", "test");
	$rows = getRows($url);
//	print_r($rows);
//	die();
	insertRecords($conn, $rows);
	echo "Completed insertion for ".$ticker."<br>";
die();
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
// skip insertion if record found
function insertRecords($conn, $rows) {
	$sql2 ="INSERT INTO fintable(ticker, stock_name, date_announced, year_end, year, quarter, ". 
			" revenue, eps, dividend, unit_price, profit_margin) VALUE (?,?,?,?,?,?,?,?,?,?,?)";			
	$stmt2 = $conn->prepare($sql2);

	$sql1  = "SELECT ticker, year, quarter from fintable where ticker = ? and year = ? and quarter = ? ";
	$stmt1 = $conn->prepare($sql1); 

	$qtrs = array("1","2","3","4");
	foreach($rows as $row) {
		$fields=preg_split('/;/', $row, -1, PREG_SPLIT_NO_EMPTY);     // explode into array		
		$year= getYear($fields[4]);
		echo $fields[1].">".$year.">".$fields[5]."<br>";		
		if (in_array($fields[5], $qtrs )) {
			$stmt1->bind_param("sii", $fields[1], $year, $fields[5]);
			$stmt1->execute();		
			$rs1 = $stmt1->get_result();
			if ($rs1->num_rows == 0)	{       // if not found => insert record
				$stmt2->bind_param("ssssiiddddd", $fields[1], $fields[2], $fields[3], $fields[4], $year, $fields[5],
						$fields[6],  $fields[10], $fields[11], $fields[21], $fields[22]);
				$stmt2->execute();
			}
		} else {
			echo "skip >".$fields[1].">".$year.">".$fields[5]."<br>";	
		}
	}
	echo "end of insert";
	$stmt1->close();
	$stmt2->close();
}
