e<?php
require __DIR__.'/DBclass.php';
class Pool {
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

    public function getPools($json) {
      $sql = "select * "; 
      $sql .= " from pond ";
      $sql .= " where  organiser = ? and round = ? ";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'organiser'} ,$json->{'data'}->{'round'}   ]);
      $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$pools) {
        $this->msg = 'No rows'; 
        exit;
      };
     return $pools;
    }
/*      $sql = "select a.id, a.pool_name, a.pool_type, a.entry_cost, a.entry_quorum";
      $sql .= ",a.entrants, a.pool_prize, a.payout";
      $sql .= ",a.organiser, a.round, b.start, b.end "; 
      $sql .= " from pool3 a, period b ";
      $sql .= " where  a.organiser = ? and a.round = ? ";
      $sql .= " and  a.round = b.round ";
*/
    public function getOrgPools($id) {
      $sql = "select a.id, a.pool_name, a.pool_type, a.entry_cost, a.entry_quorum";
      $sql .= ",a.entrants, a.pool_prize, a.payout";
      $sql .= ",a.organiser, a.round, a.remarks"; 
      $sql .= ",b.start, b.end "; 
      $sql .= " from pool a, period b ";
      $sql .= " where  a.organiser = ? ";
      $sql .= " and  a.round = b.round ";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $id ]);
      $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$pools) {
          $this->msg = 'No rows'; 
          exit;
      };
      return $pools;
    }
    public function insertPool($json) { 
       $sql = "insert into pool(pool_name, pool_type, entry_cost, entry_quorum, entrants";
       $sql .= ", pool_prize, payout";
       $sql .= ",organiser, round";
       $sql .= ",remarks, created)";
       $sql .= " values (?,?,?,?,?,?,?,?,now())";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ $json->{'data'}->{'pool_name'} ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'}      
            ,$json->{'data'}->{'entry_quorum'} ,$json->{'data'}->{'entrants'}
            ,$json->{'data'}->{'pool_prize'}      ,$json->{'data'}->{'payout'}  
            ,$json->{'data'}->{'organiser'}      ,$json->{'data'}->{'round'}  
            ,$json->{'data'}->{'remarks'} ]);
    }
    public function deletePool($id) {
        $stmt = $this->db->prepare("delete from pool where id=?");
        $stmt->execute([ $id ]);
    }
    public function updatePool($json) {        // object, not array
       $sql  = "update pool set pool_name=?, pool_type=?";
       $sql .= ",entry_cost=?, entry_quorum=?, entrants=?, pool_prize=?, payout=?";
       $sql .= ",organiser=?, round=?";
       $sql .= ",remarks=? where id=?";
       
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ 
             $json->{'data'}->{'pool_name'}       ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'} ,$json->{'data'}->{'entry_quorum'},$json->{'data'}->{'entrants'}
            ,$json->{'data'}->{'pool_prize'} ,$json->{'data'}->{'payout'}   
            ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'round'}   
            ,$json->{'data'}->{'remarks'}    ,$json->{'data'}->{'id'} ]);
            
    }
    public function updateCount($pid) {  
        $sql  = "update pool set entrants=entrants - 1 where id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $pid ]);             
     }
    public function reverseCount($pid) {  
      $sql  = "update pool set entrants=entrants + 1 where id=?";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $pid ]);             
    }  
}
