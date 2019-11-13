<?php
require_once('Organiser.php');
$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};
if(isset($op)){
  switch($op){
    case "getOrganiser":
      $code = -1;
      $obj = new Organiser();
      $ret = $obj->getOrganiser($json->{'orgid'});            
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