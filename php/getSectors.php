<?php
require __DIR__.'/DBclass.php';
//---------------------------------------
class Sector {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
  public function getSectors() {
    $sql = "select * from sectors ";    
    $stmt = $this->db->prepare($sql);
    $stmt->execute();  
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$results) { $this->msg = 'No rows'; exit; };
    return $results;
  }
}    // end of Class
//=========================================
$code = -1;
$obj = new Sector();
$ret = $obj->getSectors();
$msg = $obj->getMsg();
$resp = (!empty($msg)) ? array('code' => -1, 'msg' => $msg) 
                       : array('code' => 1, 'msg' => '', 'data' => $ret); 
//--------------------------------------------
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
echo json_encode($resp);
