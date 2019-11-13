<?php
require_once('TicketGames.php');

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

// require_once('configLog.php');
// $logger = getLogger();
// $logger->info('1) apiTicketGames.php', array('json' => $json));

if(isset($op)){
  switch($op){  
    case "getTicketGamesByOrg":   // by ticket id
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getTicketGames("organiser", $json->{'organiser'});     // tid
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret);       
      break;
    case "getTicketGamesByUser":   // by username ?????????????
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getTicketGames("username", $json->{'username'});     
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;   
    case "getTicketGamesByTicket":   // by ticket id (redundant)
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getTicketGames("ticket_id", $json->{'id'});     // tid
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getTicketGamesByGame":  
    //$logger->info('2) apiTicketGames.php', array('json' => $json));
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getTicketGames("game_id", $json->{'gid'});     // gid
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
      //==************************************************
    case "getOrgWeek":   // apiPool.html
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getOrgWeek();   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
    case "getPoolUserGames":   // orgweek, pid, username 
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getPoolUserGames($json);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;    
    case "getPoolGames":   // orgweek, pid
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getPoolGames($json);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;   
    case "getTicketGames2":   // orgweek, gid     - gamesummary
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getTicketGames2($json);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getOrgWeekTicketGames":   // orgweek      - ticketsummary
      $orgweek = $json->{'orgweek'};
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getOrgWeekTicketGames($orgweek);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      // $logger->info('12) getOrgWeekTicketGames', array('resp' => $resp) );
      break;
      //*********************************************************             
    case "getUserTicketGames":   // apiUser.html
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getUserTicketGames($json);   
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;        
      //----------------------------------------------
    case "getGameUsers":   // by ticket id (redundant)
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getGameUsers($json);     // pid, gid
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break; 
      //----- 1) My game result --------------------- 
     case "getOrgRndUserTicketGames":
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getOrgRndUserTicketGames($json);  // organiser, round
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
      //----- 1) leadership result --------------------- 
     case "getOrgRndTicketGames":
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getOrgRndTicketGames($json);  // organiser, round
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;            
    case "getTicketGamesStatByOrg":   // by ticket id
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->getTicketGamesStat($json);     // tid
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;           
    //==========================================================        
    case "countEntry": 
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->countEntry($json);     
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "saveGame2Ticket":
      $code = -1;
      $obj = new TicketGames();
      $ret = $obj->insertGame2Ticket($json);  
//      $logger->info('6) saveGame2Tickets', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "save":          // manticketgames.js
      $id = $json->{'data'}->{'id'};
      $obj = new TicketGames();
      if (empty($id) || $id=="") {
 //$logger->info('3) apiTicketGames: save-insert', array('json' => $json));   
         $code = $obj->insertTicketGames($json);
      } else {
// $logger->info('4) apiTicketGame: save-update', array('json' => $json)); 
         $code = $obj->updateTicketGames( $json);     
      };                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;    
    case "delete":
      $code = -1;
      $obj = new TicketGames();
      $obj->deleteGame($json);            
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
