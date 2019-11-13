<?php
require __DIR__.'/DBclass.php';

class Plan {
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

  public function getPlans($json) {  
    $sql = "select id, pool_name, pool_type, a.entry_cost, a.entry_quorum";
    $sql .= ",entrants, pool_prize, payout";
    $sql .= ",organiser  "; 
    $sql .= " from plan ";
    $sql .= " where  a.organiser = ? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'organiser'}]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
  public function getOrgPlans($id) {
    $sql = "select id,  pool_name,  pool_type, entry_cost, entry_quorum";
    $sql .= ",entrants, pool_prize, payout";
    $sql .= ",organiser, remarks"; 
    $sql .= " from plan ";
    $sql .= " where  organiser = ? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
  public function insertPlan($json) { 
       $sql = "insert into plan(pool_name, pool_type, entry_cost, entry_quorum, entrants";
       $sql .= ", pool_prize, payout";
       $sql .= ",organiser";
       $sql .= ",remarks, created)";
       $sql .= " values (?,?,?,?,?,?,?,?,?,now())";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ $json->{'data'}->{'pool_name'} ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'}      
            ,$json->{'data'}->{'entry_quorum'} ,$json->{'data'}->{'entrants'}
            ,$json->{'data'}->{'pool_prize'}      ,$json->{'data'}->{'payout'}  
            ,$json->{'data'}->{'organiser'}   
            ,$json->{'data'}->{'remarks'} ]);
  }
  public function deletePlan($id) {
        $stmt = $this->db->prepare("delete from plan where id=?");
        $stmt->execute([ $id ]);
  }
  public function updatePlan($json) {        // object, not array
       $sql  = "update plan set pool_name=?, pool_type=?";
       $sql .= ",entry_cost=?, entry_quorum=?, entrants=?, pool_prize=?, payout=?";
       $sql .= ",organiser=? ";
       $sql .= ",remarks=? where id=?";
       
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ 
             $json->{'data'}->{'pool_name'}       ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'} ,$json->{'data'}->{'entry_quorum'},$json->{'data'}->{'entrants'}
            ,$json->{'data'}->{'pool_prize'} ,$json->{'data'}->{'payout'}   
            ,$json->{'data'}->{'organiser'} 
            ,$json->{'data'}->{'remarks'}    ,$json->{'data'}->{'id'} ]);
            
  }
}
