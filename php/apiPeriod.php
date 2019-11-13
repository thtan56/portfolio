<?php
// require_once('configLog.php');
require_once('Period.php');
$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

// $logger = getLogger();
// $logger->info('1) apiPeriod.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getPeriods":
      $code = -1;
      $obj = new Period();
      $ret = $obj->getPeriods();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getOrgPeriods":
      $orgid = $json->{'organiser'};
      $code = -1;
      $obj = new Period();
      $ret = $obj->getPeriods($orgid);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
     case "getRounds":
      $orgid = $json->{'organiser'};
      $code = -1;
      $obj = new Period();
      $ret = $obj->getRounds($orgid);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getOrgCurrentRound":     // organiser, today
      $code = -1;
      $obj = new Period();
      $ret = $obj->getOrgCurrentRound($json);
 //     $logger->info('3) getOrgCurrentRound', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getOrgsCurrentRound":     // organisers, today
      // $organisers = $json->{'data'}->{'organisers'};
      // $logger->info('2) apiPeriod.php', array('organisers' => $organisers));
      $code = -1;
      $obj = new Period();
      $ret = $obj->getOrgsCurrentRound($json);
 //     $logger->info('3) getOrgCurrentRound', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getOrgWeekPeriod":     // organiser, round
      $code = -1;
      $obj = new Period();
      $ret = $obj->getOrgWeekPeriod($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getOrgsWeeks":     // organisers, today
      $code = -1;
      $obj = new Period();
      $ret = $obj->getOrgsWeeks($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
     case "autoGenPeriod":
      $code = -1;
      $obj = new Period();
      $obj->autoGenPeriod($json);                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
     case "save":
      $id = $json->{'data'}->{'id'};
      $obj = new Period();
      $code = (empty($id) || $id=="" || $id="0") ? $obj->insertjPeriod($json) : $obj->updatejPeriod( $json);                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;      
    case "delete":
      $code = -1;
      $obj = new Period();
      $obj->deletePeriod($json->{'id'});            
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