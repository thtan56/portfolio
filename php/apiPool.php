<?php
//require_once('configLog.php');
require_once('Pool.php');

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};
//$logger = getLogger();
//$logger->info('1) apiPool.php', array('op' => $op));

if(isset($op)){
  switch($op){
    case "getPools":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getPools($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret);
      break;     
    case "getPool":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getPool($json->{'pool_id'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret);
      break;      
    case "getOrgPools":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getOrgPools($json->{'organiser'}); 
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;   
    case "getOrgsPools":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getOrgsPools($json);    // selected organisers, round
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getOrgDatePools":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getOrgDatePools($json);    // selected organisers, today
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;       
    case "getWeekPools":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getWeekPools($json->{'date'});
 //$logger->info('3) getWeekPools.php', array('ret' => $ret));   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;         
    case "updateCount":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->updateCount($json->{'pid'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "reverseCount":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->reverseCount($json->{'pid'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;             
    case "save":
//      $logger->info('3) save', array('json' => $json));
      $id = $json->{'data'}->{'id'};
      $obj = new Pool();
      $code = (empty($id) || $id=="") ? $obj->insertPool($json) : $obj->updatePool( $json);                     
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;       
    case "autoGenPool":
      $obj = new Pool();
      $code = $obj->autoGenPool();                     
      $resp = array('code' => $code, 'msg' => $obj->getMsg());  
      break;
    case "updatePoolWinners":
      $code = -1;
      $obj = new Pool();
      $obj->updatePoolWinners($json);
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
      break;      
    case "getPoolGames":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getPoolGames($json->{'pid'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    case "getPoolTickets":
      $code = -1;
      $obj = new Pool();
      $ret = $obj->getPoolTickets($json->{'pid'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
    case "delete":
      $code = -1;
      $obj = new Pool();
      $obj->deletePool($json->{'id'});            
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
