<?php
require_once('../php/configDb.php');
//require_once('../php/configLog.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$data = file_get_contents('php://input');
$json = json_decode($data);

//$logger = getLogger();
//$logger->info('1) json_cashflow_post.php', array('json' => $json));

$xrate = $json->{'data'}->{'exchange_rate'};
$reference_number = $json->{'data'}->{'uom'};  
$vcash = 0;
$cash = 0;

switch ($json->{'data'}->{'uom'}) {
	case 'vcash':
		if ($json->{'data'}->{'buy_amount'} > 0) {					// in cash term
			$buy = $json->{'data'}->{'buy_amount'};
			$cash = -1 * $buy;
			$vcash = $buy * $xrate;
			$activity = "buy vcash";    // credit cash
			$description = "transfer to virtual bank";    // debit  vcash
		} else if ($json->{'data'}->{'sell_amount'} > 0) {     // in vcash term
			$sell = $json->{'data'}->{'sell_amount'};
			$vcash = -1 * $sell;
			$cash = $sell / $xrate;
			$activity = "sell vcash";    // debit  cash
			$description = "transfer to trust account";  // credit vcash   			
		};
		break;
	case 'cash':	
		if ($json->{'data'}->{'deposit_amount'} > 0) {     // in cash term
			$cash = $json->{'data'}->{'deposit_amount'};
			$activity = "deposit cash";    // credit cash
			$description = "transfer to trust account";    // debit  vcash		
		} else if ($json->{'data'}->{'withdraw_amount'} > 0) {     // in cash term
			$cash = -1 * $json->{'data'}->{'withdraw_amount'};
			$activity = "withdraw cash";    // credit cash
			$description = "transfer to bank";    // debit  vcash
		};
		break;
};

$pdo = getPdoConnection();
$sql = 'insert into request ';
$sql .= ' (username, activity, description, reference_number, exchange_rate, cash, vcash, created ) ';
$sql .= ' values (?,?,?,?,?,?,?,now() )';
$stmt = $pdo->prepare($sql);
$stmt->execute([ $json->{'data'}->{'username'}, $activity, $description, $reference_number, $xrate, $cash, $vcash ]);
$stmt = null;
?>
