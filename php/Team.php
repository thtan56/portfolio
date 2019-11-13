<?php
require __DIR__.'/DBclass.php';
// require __DIR__.'/configLog.php';

class Team {
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
    public function getTeams() {
        $stmt = $this->db->prepare("select * from team");
        $stmt->execute();
        $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$teams) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $teams;
    }
    public function getOrgTeams($oid) {
        $stmt = $this->db->prepare("select * from team where organiser=?");
        $stmt->execute([$oid ]);
        $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$teams) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $teams;
    }
    public function getOrgTeamNames($id) {
        $stmt = $this->db->prepare("select name from team where organiser=? order by name");
        $stmt->execute([$id ]);
        $teams = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);   // return as arrays
        if(!$teams) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $teams;
    }
    public function getTeamLongNames($keys) {
//      $logger = getLogger();
//      $logger->info('1) Team.php-getTeamLongName:', array('keys' => $keys));

      $result1="";
//      $stmt = $this->db->prepare("select name,logo from team where organiser=? and shortname=? ");      
      $stmt = $this->db->prepare("select name,logo from team where organiser=? and name=? ");
      $stmt->execute([$keys[0], $keys[1] ]);
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
        $result1 = $row['name'];   
        $result2 = $row['logo'];
      };
      $result1.=":";      $result2.=":";
      $stmt->execute([$keys[0], $keys[2] ]);
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result1 .= $row['name'];  
        $result2 .= $row['logo'];
      }; 
//       $results.=$row['name']; };
      $results = array('name'=>$result1, 'logo'=>$result2);
//      $logger->info('2) Team.php', array('results' => $results));

      return $results;
    }
    public function insertTeam($json) { 
       $sql = "insert into team(name,organiser, shortname, venue, logo, created) values(?,?,?,?,?,now())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'name'}   ,$json->{'data'}->{'organiser'}  ,$json->{'data'}->{'shortname'}
                ,$json->{'data'}->{'venue'},$json->{'data'}->{'logo'} ]);
    }
    public function deleteTeam($id) {
        $stmt = $this->db->prepare("delete from team where id=?");
        $stmt->execute([ $id ]);
    }
    public function updateTeam($json) {        // object, not array
        $sql = "update team set name=?, organiser=?, shortname=?, venue=?, logo=? where id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'name'}   ,$json->{'data'}->{'organiser'}  
                ,$json->{'data'}->{'shortname'}  
                ,$json->{'data'}->{'venue'}, $json->{'data'}->{'logo'}
                ,$json->{'data'}->{'id'} ]);
    }
}