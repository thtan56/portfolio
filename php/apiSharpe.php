<?php
require_once('Sharpe.php');

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

if(isset($op)){
  switch($op){
    case "getReturns":
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getReturns();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getFavoriteReturns":
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getReturns('favorite', $json->{'favorite'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getTickerReturn":
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getReturns('ticker', $json->{'ticker'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;     
      case "getSharpes":
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getSharpes();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getFavoriteSharpes":
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getSharpes('favorite', $json->{'favorite'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    case "getSharpeHistory":     // 20/8/19
      $uom = $json->{'data'}->{'uom'};  // sector, favorite, investor, dividend
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getSharpeHistory($uom, $json->{'data'}->{'groupName'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;           
    case "getTickerSharpe":
      $period = $json->{'data'}->{'period'};
      $ticker = $json->{'data'}->{'ticker'};      
      $code = -1;
      $obj = new Sharpe();
      $ret = $obj->getTickerSharpes($ticker, $period);
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
