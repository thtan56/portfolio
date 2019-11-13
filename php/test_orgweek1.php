<?php
//require_once('User.php');
require_once('Period.php');

$obj = new Period();

$data = '{"data": {"organisers": "NBA,NBL,NFL,AFL", "today": "2018/11/10"} }';
$json = json_decode($data);
//
$organisers = $json->{'data'}->{'organisers'}; 
$today = $json->{'data'}->{'today'}; 
$array = explode(",", $organisers);
$results=[];
foreach($array as $org) {
	$data2 = '{"data": {"organiser": "'.$org.'", "today": "2018/11/10"} }';
	$json2 = json_decode($data2);
	$ret = $obj->getOrgCurrentRound($json2); // data.organiser, data.today
	if (count($ret) <> 0) {
		$orgweek = $org.":".$ret[0]['round'];
    array_push($results, $orgweek);
	};
};  
print_r($results);
?>
