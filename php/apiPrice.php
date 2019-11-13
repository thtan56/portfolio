<?php
require_once('Price.php');

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

if(isset($op)){
  switch($op){  
    case "getPrices":     // for a ticker
      $code = -1;
      $obj = new Price();
      $ret = $obj->getPrices($json->{'ticker'}, $json->{'period'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getGroupPrices":  
      $code = -1;
      $obj = new Price();
      $ret = $obj->getGroupPrices($json->{'tickers'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    case "getPeriodPrices":    // by period, uom, groupid
      $period = $json->{'data'}->{'period'};  // 
      $uom = $json->{'data'}->{'uom'};  // sector, favorite
      $code = -1;
      $obj = new Price();
      $ret = $obj->getPeriodPrices($period, $uom, $json->{'data'}->{'groupName'});
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
