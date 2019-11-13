<?php
//require_once('configLog.php');
require_once('PoolGames.php');

$data = file_get_contents('php://input');
$json = json_decode($data);

//$logger = getLogger();
$op = $json->{'op'};
//$logger->info('1) apiGame.php', array('op' => $op));
//$logger->info('2) apiGame.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getPoolGames":
      $code = -1;
      $obj = new PoolGames();
      $ret = $obj->getPoolGames();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
     case "getGamePools":
      $code = -1;
      $obj = new PoolGames();
      $ret = $obj->getGamePools($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;         
     case "getOrgBetPools":
      $id = $json->{'id'};
      $code = -1;
      $obj = new PoolGames();
      $ret = $obj->getOrgBetPools($id);
 //     $logger->info('1) apiPool.php', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;       
    case "getWeekPools":
      $code = -1;
      $obj = new PoolGames();
      $ret = $obj->getWeekPools();
//      $logger->info('1) apiPool.php', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
     case "getWeekStats":
      $code = -1;
      $obj = new PoolGames();
      $ret = $obj->getWeekStats();
 //     $logger->info('1) apiPool.php', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getBetPools":  
      $code = -1;
      $obj = new PoolGames();
      $ret = $obj->getBetPools($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "addcount":
      $obj = new PoolGames();
      $code = $obj->updatePoolCount($json);                    
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;      
    case "save":
      $id = $json->{'data'}->{'id'};
   
      $obj = new PoolGames();
      if (empty($id) || $id=="") {
//$logger->info('3) apiGame: save-insert', array('json' => $json));   
         $code = $obj->insertPool($json);
      } else {
//$logger->info('4) apiGame: save-update', array('json' => $json)); 
         $code = $obj->updatePool( $json);     
      };                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "delete":
      $code = -1;
      $obj = new PoolGames();
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
