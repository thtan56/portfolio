<?php
// require_once('configLog.php');
require_once('Financial.php');

// $logger = getLogger();
$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

if(isset($op)){
  switch($op){  
    case "getEps":     // for a ticker
      $code = -1;
      $obj = new Financial();
      $ret = $obj->getEps($json->{'ticker'});
      $msg = $obj->getMsg();
      // $logger->info('11) Financial.php:getEps', array('msg' => ['msg'=>$msg]));         
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getGroupStocks":  
      $uom = $json->{'data'}->{'uom'};  // sector, favorite, investor
      $code = -1;
      $obj = new Financial();
      $ret = $obj->getStocks($uom, $json->{'data'}->{'groupName'});
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
