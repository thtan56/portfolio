<?php

require __DIR__.'/DBclass.php';
//require_once('configLog.php');

class System {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public $serverId;

  public function __construct() { 
    $dbObj = new DB();
    $this->db = $dbObj->getPDO(); 
    $this->serverId = $dbObj->dbServer;
  }
  public function getSystemInfo() {
    $stmt = $this->db->prepare("select * from game order by start desc");
    $stmt->execute();
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$games) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $games;
  }
}

$data = file_get_contents('php://input');
$json = json_decode($data);
$op = $json->{'op'}; 

$obj = new System();
$ret = $obj->getSystemInfo();
// echo $obj->serverId;
$resp = array('code' => 1, 'msg' => '', 'data' => $ret, 'serverid' => $obj->serverId );     

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
echo json_encode($resp);
?>
