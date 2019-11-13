<?php
require_once('../php/configDb.php');
//require_once('../php/configLog.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
//$logger = getLogger();

$condition = (isset($_GET['username'])) ? " where username='".$_GET['username']."'": "";
$sql = "SELECT  * FROM bet ". $condition;
//$logger->info('1) json_mybet.php', array('sql' => $sql));

$pdo = getPdoConnection();
$query = $pdo->prepare($sql);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
$pdo = null; // close connection
echo json_encode($rows, TRUE);
/*
$conn = mysqli_connect("localhost", "root", "cancer56", "test");
$sql = "SELECT *  FROM mybet where username ='".$_GET['username']."'";

$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
$data = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
	$data[] = $rows;
}
mysqli_close($conn);
echo json_encode($data);
*/
?>
