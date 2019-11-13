<?php
require __DIR__.'/DBclass.php';

class Bet {
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
    public function getBets() {
        $stmt = $this->db->prepare("select * from bet");
        $stmt->execute();
        $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$bets) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $bets;
    }
    public function getUserBets($json) {
        $stmt = $this->db->prepare("select * from bet where username=? and status=?");
        $stmt->execute([ $json->{'data'}->{'username'}, $json->{'data'}->{'status'} ]);
        $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$bets) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $bets;
    }
    public function getOrgBets($organiser) {
        $stmt = $this->db->prepare("select * from bet where organiser=?");
        $stmt->execute([$organiser]);
        $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$bets) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $bets;
    }
    public function getOrg2Bets($json) {
        $stmt = $this->db->prepare("select * from bet where organiser=? and round=?");
        $stmt->execute([ $json->{'data'}->{'organiser'}, $json->{'data'}->{'round'} ]);
        $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$bets) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $bets;
    }
    public function getPoolUsers($id) {   // old version
        $stmt = $this->db->prepare("select username, pool_id from bet where pool_id=?");
        $stmt->execute([ $id ]);
        $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$bets) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $bets;
    }
    public function insertBet($json) {    
        //                        1             2       3          4        5           6         7           8           9 
       $sql = "insert into bet (home_team, away_team, organiser, game_date, bet_type";
       //            10      1         2         3         4           5          6           7
       $sql .= ", pool_id, odd_date, home_odd, away_odd, bet_winner, username, bet_amount";
       $sql .= ", game_winner, home_score, away_score, bet_score1";
       $sql .= ", week_no, created ) ";
       $sql .= "  values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, weekofYear(?),  now() ) ";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([  
            $json->{'data'}->{'home_team'}  ,$json->{'data'}->{'away_team'}   ,$json->{'data'}->{'organiser'}  
            ,$json->{'data'}->{'game_date'} ,$json->{'data'}->{'bet_type'}    ,$json->{'data'}->{'pool_id'}
            ,$json->{'data'}->{'odd_date'}    ,$json->{'data'}->{'home_odd'}  ,$json->{'data'}->{'away_odd'}
            ,$json->{'data'}->{'bet_winner'}   ,$json->{'data'}->{'username'} ,$json->{'data'}->{'bet_amount'}    
            ,$json->{'data'}->{'game_winner'}
            ,$json->{'data'}->{'home_score'} ,$json->{'data'}->{'away_score'} ,$json->{'data'}->{'bet_score1'}
            ,$json->{'data'}->{'game_date'}
        ]);
    }
    public function deleteBet($id) {
        $stmt = $this->db->prepare("delete from bet where id=?");
        $stmt->execute([ $id ]);
    }
    public function updateBet($json) {        // object, not array
        $sql = "update bet set organiser=?, home_team=?, away_team=?, game_date=?, game_winner=?, home_score=?, away_score=?";
        $sql .= ",bet_type=?,pool_id=?, odd_date=?, home_odd=?, away_odd=?, bet_winner=?, username=?, bet_amount=?, bet_score1=?,status=?"; 
        $sql .= ",username=?, week_no=weekofYear(game_date) ";
        $sql .= "where id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $json->{'data'}->{'organiser'}            
            ,$json->{'data'}->{'home_team'}           ,$json->{'data'}->{'away_team'}
            ,$json->{'data'}->{'game_date'}
            ,$json->{'data'}->{'game_winner'}         ,$json->{'data'}->{'home_score'}
            ,$json->{'data'}->{'away_score'}          ,$json->{'data'}->{'bet_type'}
            ,$json->{'data'}->{'pool_id'}
            ,$json->{'data'}->{'odd_date'}            ,$json->{'data'}->{'home_odd'},$json->{'data'}->{'away_odd'}
            ,$json->{'data'}->{'bet_winner'}          ,$json->{'data'}->{'username'}
            ,$json->{'data'}->{'bet_amount'}          ,$json->{'data'}->{'bet_score1'}
            ,$json->{'data'}->{'status'}            ,$json->{'data'}->{'username'}            
            ,$json->{'data'}->{'id'} ]);
    }
}