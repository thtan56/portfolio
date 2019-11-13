<?php
require_once('Team.php');
$obj = new Team();
$ret = $obj->getOrgTeamNames($_GET['id']);
echo json_encode($ret);
?>