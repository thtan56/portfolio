<?php
// Connecting, selecting database
$user="uorfkbdhshqhlv";
$password="4c6f9e3adecae17f2f8b3ac2351f75a5effe164dd867f176d6c9e6be90400050";
$host="ec2-54-235-242-63.compute-1.amazonaws.com";
$port="5432";
$dbname="dc1cog334s79lk";
$dsn="pgsql:host=".$host.";port=".$port.";dbname=".$dbname.";user=".$user.";password=".$password.";sslmode=require";
echo $dsn;

//pgsql:host=localhost;port=5432;dbname=test;user=postgres;password=cancer56";
try {
	$pdo = new PDO($dsn);
	echo '<br>connected';
}
catch(PDOException $e) {
	echo 'Error: '. $e->getMessage();
};
$sql='SELECT  * FROM users';     // cannot use 'user', conflict with system table
//$sql = 'select * from information_schema.tables';

$result = $pdo->query($sql);
echo "<br>selected<br>";
// Printing results in HTML
//echo "<table>\n";
while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
print_r($row);
echo "<br>";
//	echo "\t<tr>\n";
//	echo "<td>".$row['user']."</td></tr>";
}
// echo "</table>\n";
?>