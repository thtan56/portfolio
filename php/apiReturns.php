<?php
// require_once('configLog.php');
require_once('Returns.php');

// $logger = getLogger();

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

// $logger->info('1) apiReturns.php', array('json' => $json));

if(isset($op)){
  switch($op){  
    case "getReturns":  
      $period = $json->{'data'}->{'period'};
      $ticker = $json->{'data'}->{'ticker'};      
      $code = -1;
      $obj = new Returns();
      $ret = $obj->getReturns($ticker, $period);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getGroupReturns":
      // $logger->info('2) getGroupReturns.php', array('json' => $json));    
      $uom    = $json->{'data'}->{'uom'};
      $gid    = $json->{'data'}->{'gid'};   // sectors, favorites, indices    
      $period = $json->{'data'}->{'period'};
      $code = -1;
      $obj = new Returns();
      $ret = $obj->getGroupReturns($uom, $gid, $period);
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