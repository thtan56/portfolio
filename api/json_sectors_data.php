<?php
$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$select = 'select sector, count(*) from stocks group by sector having sector <> "" ';
$sectorData = mysqli_query($conn,$select );
$response = array();
while($row = mysqli_fetch_assoc($sectorData)) { $response[] = $row; }
echo json_encode($response, JSON_NUMERIC_CHECK);
exit;
