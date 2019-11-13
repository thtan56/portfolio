<?php
//require_once('configLog.php');
require_once('Portfolio.php');

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};
//$logger = getLogger();
//$logger->info('1) apiGame.php', array('op' => $op));
//$logger->info('2) apiGame.php', array('json' => $json));

if(isset($op)){
  switch($op){
    case "getGames":
      $code = -1;
      $obj = new Portfolio();
      $ret = $obj->getGames();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getGame":
      $code = -1;
      $obj = new Portofolio();
      $ret = $obj->getGame($json->{'id'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;     
    case "getGameSummary":
      $code = -1;
      $obj = new Game();
      $ret = $obj->getGameSummary();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;   
    case "getGameLeaders":
      $code = -1;
      $obj = new Game();
      $ret = $obj->getGameLeaders();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;  
    case "getGameWeeks":
      $code = -1;
      $obj = new Game();
      $ret = $obj->getGameWeeks();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getWeek1Tickets":      // testing only
      $code = -1;
      $obj = new Game();
      $ret = $obj->getWeek1Tickets();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;              
    case "getWeek2Games":      // testing only
      $code = -1;
      $obj = new Game();
      $ret = $obj->getWeek2Games();
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;              
    case "getDayGames":
      $code = -1;
      $obj = new Game();
      $ret = $obj->getDayGames($json->{'key'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
    case "getOrgGames":
      $code = -1;
      $obj = new Game();
      $ret = $obj->getOrgGames($json->{'id'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
//      $logger->info('3) getOrgGame.php', array('data' => $ret));
      break;
     case "getOrgRndGames":
      $code = -1;
      $obj = new Game();
      $ret = $obj->getOrgRndGames($json);  // organiser, round
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;     
    case "getOrgJGames":    // date, json(g1, g2, etc)
      $code = -1;
      $obj = new Game();
      $ret = $obj->getOrgJGames($json->{'id'});
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;      
    case "save":
      $id = $json->{'data'}->{'id'};
   
      $obj = new Game();
      if (empty($id) || $id=="") {
// $logger->info('3) apiGame: save-insert', array('json' => $json));   
         $code = $obj->insertGame($json);
      } else {
// $logger->info('4) apiGame: save-update', array('json' => $json)); 
         $code = $obj->updateGame( $json);     
      };                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "saveResult":
      $obj = new Game();
      $code = $obj->updateGameResult( $json);                                  
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;     
    case "csvinsertGames": 
  //    $logger->info('3) csvinsertGame', array('json' => $json));  
      $obj = new Game();
      $code = $obj->csvinsertGames($json->{'orgid'});
      $resp = array('code' => $code, 'msg' => $obj->getMsg()); 
      break; 
    case "delete":
      $code = -1;
      $obj = new Game();
      $obj->deleteGame($json->{'id'});            
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