<?php
//require_once('configLog.php');
require_once('Request.php');
$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'}; 

// $logger = getLogger();
//$logger->info('1) apiRequest.php', array('json' => $json));
//$logger->info('2) getUserRequests', array('op' => $op));
if(isset($op)){
  switch($op){
    case "getUserRequests":
      $code = -1;
      $obj = new Request();
      $ret = $obj->getUserRequests($json);
   // $logger->info('4) getUserRequests', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    case "getAllRequests":
      $code = -1;
      $obj = new Request();
      $ret = $obj->getAllRequests();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;       
     case "save":
      $id = $json->{'data'}->{'id'};
      $obj = new Request();
      $code = (empty($id) || $id=="") ? $obj->insertRequest($json) : $obj->updateRequest( $json);                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "insert":
      $obj = new Request();
      $ret = $obj->insertRequest2($json);  
      $msg = $obj->getMsg();                      
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret);
      break; 
    case "deleteUpdate":
      $code = -1;
      $obj = new Request();
      $obj->deleteUpdate($json);            
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
      break;          
    case "delete":
      $code = -1;
      $obj = new Request();
      $obj->deleteRequest($json->{'id'});            
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
