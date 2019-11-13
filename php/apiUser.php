<?php
// require __DIR__.'/configLog.php';
require_once('User.php');

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'};

// $logger = getLogger();
// $logger->info('1) apiUser.php', array('json' => $json));

//---- start logging from here ----------------------------- 
if(isset($op)){
  switch($op){
    case "getUsers":
      $code = -1;
      $obj = new User();
      $ret = $obj->getUsers();
      // $logger->info('10) apiUser.php', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "register":
      $id = $json->{'data'}->{'id'};
      $obj = new user();
      $code = $obj->registerUser($json);                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;      
    case "save":
      $id = $json->{'data'}->{'id'};
      $obj = new user();
      $code = (empty($id) || $id=="") ? $obj->insertUser($json) : $obj->updateUser( $json);                        
      $resp = array('code' => $code, 'msg' => $obj->getMsg());   // empty msg => ok
      break;
    case "delete":
      $code = -1;
      $obj = new user();
      $obj->deleteuser($json->{'id'});            
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
      break;
    case "getUser":
      $code = -1;
      $obj = new User();
      $ret = $obj->getUser('email', $json->{'email'});
//      $logger->info('3) getUser', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;
    case "getUserByName":
      $code = -1;
      $obj = new User();
      $ret = $obj->getUser('username', $json->{'username'});
//      $logger->info('3) getUser', array('ret' => $ret));
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;   
    case "getUserGameSummary":   // by ticket id
      $code = -1;
      $obj = new User();
      $ret = $obj->getUserGameSummary($json);     // tid
      $msg = $obj->getMsg();
      $resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                             : array('code' => 1, 'msg' => '', 'data' => $ret); 
      break;          
    case "changePassword":
      $code = -1;
      $obj = new user();
      $obj->changePassword($json);            
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
      break;   
    case "resetPassword":
 //   $logger->info('3) resetPassword', array('json' => $json));
      $code = -1;
      $obj = new user();
      $obj->changePassword($json);            
      $resp = array('code' => $code, 'msg' => $obj->getMsg());
      break;   
    case "sendMail":
      $code = -1;
      $obj = new user();
      $obj->sendMail($json);
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
