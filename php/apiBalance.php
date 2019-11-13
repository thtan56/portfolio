<?php
// require_once('configLog.php');
require_once('Balance.php');

// $logger = getLogger();
$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};
// $logger->info('1) apiBalance.php', array('json' => $json));

if(isset($op)){
  switch($op){  
    case "getBalances":  
      $code = -1;
      $obj = new Balance();
      $ret = $obj->getBalances();
      // $logger->info('2) getDailyBalance.php', array('ret' => $ret));      
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    default:
      $ret = -999;
      $resp = array('code' => $ret, 'msg' => 'invalid operation');
      echo json_encode($resp);
      break;
  }
} else {
  $ret = -999;
  $resp = array('code' => $ret, 'msg' => 'invalid operation');
};
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
echo json_encode($resp);
