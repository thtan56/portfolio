<?php
//require_once('configLog.php');
require_once('Stock.php');

$data = file_get_contents('php://input');
$json = json_decode($data);

//$logger = getLogger();
$op = $json->{'op'};
//$logger->info('1) apiStock.php', array('op' => $op));
//$logger->info('2) apiStock.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getStocks":
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getStocks();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getStock":
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getStocks('a.ticker', $json->{'ticker'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
    case "getGroupStocks":  
      //$logger->info('3) apiStock.php-getGroupStock');
      $uom = $json->{'data'}->{'uom'};  // sector, favorite, investor, dividend
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getStocks($uom, $json->{'data'}->{'groupName'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getGroupTickers":  
      $uom = $json->{'data'}->{'uom'};  // sector, favorite
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getGroupTickers($uom, $json->{'data'}->{'groupName'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getSharpeGroups":  
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getSharpeGroups();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    case "getReturnGroups":  
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getReturnGroups();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getEpsGroups":  
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getEpsGroups();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;             
    case "getSharpeStocks":  
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getSharpeStocks($json->{'data'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getCategoryStocks":    // dividend, return, eps, sector 
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getCategoryStocks($json->{'data'}->{'uom'}, $json->{'data'}->{'groupName'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;                      
    case "getDividendGroups":  
      $code = -1;
      $obj = new Stock();
      $ret = $obj->getDividendGroups();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;                       
    case "save":
      $id = $json->{'data'}->{'id'};  
      $obj = new Stock();
      if (empty($id) || $id=="") {
         $code = $obj->insertStock($json);
      } else {
         $code = $obj->updateStock( $json);     
      };                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "delete":
      $code = -1;
      $obj = new Stock();
      $obj->deleteStock($json->{'id'});            
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
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
