<?php
require_once('configLog.php');
require_once('Game.php');
$logger = getLogger();
$logger->info('1) fc-getGame.php', array('$_GET' => $_GET));

$obj = new Game();
$id=isset($_GET['orgid']) && !empty($_GET['orgid']) ? $_GET['orgid'] : "NBA";
$ret = $obj->fcgetGames($id);
$logger->info('2) fc-getGame.php', array('$ret' => $ret));
echo json_encode($ret);
?>
