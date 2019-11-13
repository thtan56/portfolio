<?php
require_once('Event.php');
//require_once('configLog.php');
//$logger = getLogger();

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'}; 

//$logger->info('1) apiUser.php', array('op' => $op));
//$logger->info('2) apiEvent.php', array('json' => $json));
//---- start logging from here ----------------------------- 

if(isset($op)){
  switch($op){
    case "getEvents":
      $code = -1;
      $obj = new Event();
      $ret = $obj->getEvents($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getOrgEvents":
      $code = -1;
      $obj = new Event();
      $ret = $obj->getOrgEvents($json->{'id'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
//      $logger->info('3) getOrgGame.php', array('data' => $ret));
      break;     
     case "save":
//      $logger->info('3) save', array('data' => $json));
      $id = $json->{'data'}->{'id'};
      $obj = new Event();
      $code = (empty($id) || $id=="") ? $obj->insertEvent($json) : $obj->updateEvent( $json);                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "delete":
      $code = -1;
      $obj = new Event();
      $obj->deleteEvent($json->{'id'});            
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
