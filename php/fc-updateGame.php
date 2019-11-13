<?php
require_once('Game.php');
if(isset($_POST["title"])) {
    $obj = new Game();
    $code = $obj->fcupdateGame($_POST);
};
?>