<?php
require __DIR__.'/DBclass.php';

$dbObj = new DB();
$db = $dbObj->getPDO(); 

$sql  = "Select * from ticket where organiser=? and round=?";
$ticket = $db->prepare($sql);
$bok=$ticket->execute([ 'NBA', 'Week 8' ]);
$rows = $ticket->fetchAll(PDO::FETCH_ASSOC);
echo count($rows);
foreach($rows as $row) {
	print_r($row);
	echo "<br>*********";
};

//$sql =  "CALL g00_GenWinner()"; 
//$stmt = $db->prepare($sql);
//$stmt->execute();
echo "Done";

?>