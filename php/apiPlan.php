<?php
//require_once('configLog.php');
require_once('Plan.php');

$data = file_get_contents('php://input');
$json = json_decode($data);

//$logger = getLogger();
$op = $json->{'op'};
//$logger->info('1) apiGame.php', array('op' => $op));
//$logger->info('2) apiPlan.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getPlans":
      $code = -1;
      $obj = new Plan();
      $ret = $obj->getPlans($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret);
      break;
    case "getOrgPlans":
      $code = -1;
      $obj = new Plan();
      $ret = $obj->getOrgPlans($json->{'organiser'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;              
    case "save":
//      $logger->info('3) save', array('json' => $json));
      $id = $json->{'data'}->{'id'};
      $obj = new Plan();
      $code = (empty($id) || $id=="") ? $obj->insertPlan($json) : $obj->updatePlan( $json);                     
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;       
    case "delete":
      $code = -1;
      $obj = new Plan();
      $obj->deletePlan($json->{'id'});            
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
