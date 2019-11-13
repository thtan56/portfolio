<?php

$conn = mysqli_connect("localhost", "root", "cancer56", "test");

$condition  = "1";
if(isset($_GET['stockid'])){
	$condition  = " id=".$_GET['stockid'];
}
$userData = mysqli_query($conn,"select * from stocks WHERE favorite=1 and ".$condition );
$response = array();
while($row = mysqli_fetch_assoc($userData)){
    $response[] = $row;
}
echo json_encode($response, JSON_NUMERIC_CHECK);
exit;