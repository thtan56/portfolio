<?php
//require_once('configLog.php');
require_once('Contract.php');

$data = file_get_contents('php://input');
$json = json_decode($data);

//$logger = getLogger();
$op = $json->{'op'};
//$logger->info('1) apiGame.php', array('op' => $op));
//$logger->info('2) apiGame.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getContracts":
      $code = -1;
      $obj = new Contract();
      $ret = $obj->getContracts();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getGroupContracts":
      $code = -1;
      $obj = new Contract();
      $ret = $obj->getGroupContracts($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getTickerContracts":
      $code = -1;
      $obj = new Contract();
      $ret = $obj->getTickerContracts($json->{'ticker'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;            
    case "getContract":
      $code = -1;
      $obj = new Contract();
      $ret = $obj->getContract($json->{'id'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
    case "save":
      $id = $json->{'data'}->{'id'};  
      $obj = new Contract();
      if (empty($id) || $id=="") {
         $code = $obj->insertContract($json);
      } else {
         $code = $obj->updateContract( $json);     
      };                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "delete":
      $code = -1;
      $obj = new Contract();
      $obj->deleteContract($json->{'id'});            
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
