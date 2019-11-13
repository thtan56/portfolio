<?php
require_once('Game.php');
if(isset($_POST["organiser"])) {
    $obj = new Game();
    $code = $obj->csvinsertGame($_POST);
};
?>