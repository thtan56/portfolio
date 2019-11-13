<?php
$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$sql = "SELECT username, email, firstname, lastname, role ";
$sql .= ",address1, address2, town, postcode,country, bankbsb, bankaccount";
$sql .= " FROM users ";
$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data[] = $rows;
}
mysqli_close($conn);

$resp = array('code' => 1, 'msg' => '', 'data' => $data); 
echo json_encode($resp);    // original : json_encode($data);
?>
