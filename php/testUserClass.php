<?php
require_once('User.php');

$obj = new User();
$ret = $obj->getUsers();

print_r($ret);

?>
