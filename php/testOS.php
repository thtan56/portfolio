<?php
$system=getenv('os');
$system=preg_match('/Windows/', $system) ? "local" : "remote";
?>
