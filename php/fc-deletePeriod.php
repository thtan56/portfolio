<?php
require_once('Period.php');
if(isset($_POST["id"])) {
    $obj = new Period();
    $code = $obj->deletePeriod($_POST['id']);
};
?>