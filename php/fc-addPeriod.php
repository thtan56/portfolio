<?php
require_once('Period.php');
if(isset($_POST["title"])) {
    $obj = new Period();
    $code = $obj->insertPeriod($_POST);
};
?>