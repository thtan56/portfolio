<?php
//require __DIR__.'/configLog.php';
require_once('Period.php');

//$logger = getLogger();

$obj = new Period();
$ret = $obj->getPeriods();

//$logger->info('1) fc-getPeriods.php', array('data' => $ret));
echo json_encode($ret);
?>
