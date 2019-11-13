<?php
require_once('Game.php');
if(isset($_POST["id"])) {
    $obj = new Game();
    $code = $obj->deleteGame($_POST['id']);
};
?>