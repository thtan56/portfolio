<?php
require __DIR__ . '/../vendor/autoload.php';   // goto parent first
use Monolog\Logger;                     // load Monolog library
use Monolog\Handler\StreamHandler;
use Monolog\Handler\LogmaticHandler;
use Monolog\Formatter\JsonFormatter; 

function getLogger() {
	$logger = new Monolog\Logger('channel_name');       // create a log channel
	$formatter = new JsonFormatter();       // create a Json formatter
	$stream = new StreamHandler(__DIR__.'/application-json.log', Logger::DEBUG);    // create a handler
	$stream->setFormatter($formatter);
	$logger->pushHandler($stream);      // bind
	return $logger;
}
?>