<?php
require_once('Mybet.php');
//------------------------------------------------------
require __DIR__ . '/../vendor/autoload.php';   // goto parent first
//use Monolog\Logger;                     // load Monolog library
//use Monolog\Handler\StreamHandler;
//use Monolog\Handler\LogmaticHandler;
//use Monolog\Formatter\JsonFormatter; 

//$logger = new Monolog\Logger('channel_name');       // create a log channel
//$formatter = new JsonFormatter();       // create a Json formatter
//$stream = new StreamHandler(__DIR__.'/application-json.log', Logger::DEBUG);    // create a handler
//$stream->setFormatter($formatter);
//$logger->pushHandler($stream);      // bind
//---- start logging from here ----------------------------- 

$data = file_get_contents('php://input');
$json = json_decode($data);

$op = $json->{'op'};
//$logger->info('1) apiMybet.php', array('op' => $op));
//$logger->info('2) apiMybet.php', array('op' => $json));
//---- start logging from here ----------------------------- 
if(isset($op)){

    switch($op){
        case "getMybets":
            $obj = new Mybet();
            $ret = $obj->getMybets();
            // $logger->info('1) getusers', $ret);
            $count = count($ret,1);
            $msg = $obj->getMsg();
            if (!empty($msg)) { $resp = array('code' => -1, 'msg' => $msg);
            } else { $resp = array('code' => 1, 'msg' => '','data' => $ret);
            };
            // $logger->info('12) getusers>$resp:', $resp);
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
            echo json_encode($resp);
            break;
        case "save":
            $id = $json->{'data'}->{'id'};

            $game = $json->{'data'}->{'game'};
            $date = $json->{'data'}->{'date'};
            $match = $json->{'data'}->{'match'};

            $score1 = $json->{'data'}->{'score1'};
            $score2 = $json->{'data'}->{'score2'};
            $username = $json->{'data'}->{'username'};
            $bet_amount = $json->{'data'}->{'bet_amount'};
            $bet_score1 = $json->{'data'}->{'bet_score1'};
            $bet_score2 = $json->{'data'}->{'bet_score2'};
            $bet_type = $json->{'data'}->{'bet_type'};
            $bet_odd_type = $json->{'data'}->{'bet_odd_type'};
            $bet_odd = $json->{'data'}->{'bet_odd'};
            $remarks = $json->{'data'}->{'remarks'};
            $status = $json->{'data'}->{'status'};

            $obj = new Mybet();
            $code = -1;
            if(empty($id) || $id=="") {
  //              $logger->info('3) apiMybet.php', array('id' => $id));
                $code = $obj->insertMybet($game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status);
            }else{
//                $logger->info('4) apiMybet.php', array('id' => $id));            
                $code = $obj->updateUser($id, $game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status);
            }
            $resp = array('code' => $code, 'msg' => $obj->getMsg());

            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
            echo json_encode($resp);
            break;

        case "delete":

            $id = $json->{'id'};

            $obj = new user();
            $code = $obj->deleteuser($id);
            $resp = array('code' => $code, 'msg' => $obj->getMsg());

            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
            echo json_encode($resp);
            break;

        default:
            $ret = -999;
            $resp = array('code' => $ret, 'msg' => 'invalid operation');
            echo json_encode($resp);
            break;
    }
}else{
    $ret = -999;
    $resp = array('code' => $ret, 'msg' => 'invalid operation');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($resp);

}
