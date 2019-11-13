<?php
// require_once('configLog.php');
require_once('Ticket.php');

$data = file_get_contents('php://input');
$json = json_decode($data);

// $logger = getLogger();
$op = $json->{'op'};
// $logger->info('1) apiTicket.php', array('op' => $op));
//$logger->info('2) apiTicket.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getTickets":
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getTickets();
//      $logger->info('4) getTickets', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getUserTickets":
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getTickets($json->{'username'});
//      $logger->info('5) getUserTickets', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
    //**********************************************  
    case "getOrgWeekTickets":   // apiUser.html
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getOrgWeekTickets();   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;        
    case "getPoolUserCount":   // PoolComponent.vue
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getPoolUserCount();   
      // $logger->info('4) getPoolUserCount', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;        
    case "getUserTickets2":   // orgweek, username
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getUserTickets2($json);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getUserTickets2b":   // username
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getUserTickets2b($json);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;              
    //************************************************  
    case "getTicketStatus":     // orgweek, pid
   //$logger->info('3) getTicketStatus', array('json' => $json));
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getTicketStatus($json);
   //$logger->info('4) getTicketStatus', array('ret' => $ret));     
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;   
    case "getTicketStatus2":     // organiser, round
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getTicketStatus2($json);
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;              
    case "getOrgRndTickets":
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getOrgRndTickets($json);  // organiser, round, username
//      $logger->info('6) getOrgRndTickets', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getOrgRndTicketsByPid":  
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getOrgRndTicketsByPid($json);  // organiser, round, pool#
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getOrgRndPlTicketsByUid":  
      $code = -1;
      $obj = new Ticket();
      $ret = $obj->getOrgRndPlTicketsByUid($json);  // organiser, round, pool#
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;                      
    case "save":
//      $logger->info('3) save', array('json' => $json));
      $id = $json->{'data'}->{'id'};
      $obj = new Ticket();
      $code = (empty($id) || $id=="") ? $obj->insertTicket($json) : $obj->updateTicket( $json);                     
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;   
    case "insertUpdate":     // buy Ticket
// $logger->info('3) insertUpdate', array('json' => $json));   
      $obj = new Ticket();
      $ret = $obj->insertUpdate($json);                     
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;            // return new tid;
    case "delete":
      $code = -1;
      $obj = new Ticket();
      $obj->deleteTicket($json->{'id'});            
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
      break;
    case "deleteUpdate":
      $code = -1;
      $obj = new Ticket();
      $obj->deleteUpdate($json);            
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
