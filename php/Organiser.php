<?php
require __DIR__.'/DBclass.php';
class Organiser {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { 
    $dbObj = new DB();
    $this->db = $dbObj->getPDO(); 
  }
  //-------------------------------------
  public function getMsg() { return $this->msg; }
  
  public function getOrganiser($orgid) {
    $sql = "select * from organiser where organiser=?";
    $stmt = $this->db->prepare($sql);    
    $stmt->execute([ $orgid ]);   
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    return $arr;
  }   
}
