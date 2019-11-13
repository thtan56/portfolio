<?php
require __DIR__.'/DBclass.php';
// require_once('configLog.php');

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
    $sql = "select a.id, a.pool_name, a.pool_type, a.entry_cost, a.entry_quorum";
    $sql .= ",a.entrants, a.pool_prize, a.payout ";
    $sql .= ",a.organiser, a.round, b.start, b.end_dt "; 
    $sql .= " from pool a, period b ";
    $sql .= " where  a.organiser = ? and a.round = ? ";
    $sql .= " and  a.round = b.round ";
    $sql .= " and  a.organiser = b.organiser ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'organiser'},$json->{'data'}->{'round'}]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
  public function getPool($id) {    // pool_id
    $sql = "select id, pool_name, pool_type, entry_cost, entry_quorum";
    $sql .= ",entrants, pool_prize, payout, status ";
    $sql .= ",organiser, round "; 
    $sql .= " from pool ";
    $sql .= " where  id = ? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
  //================================  
  public function getOrgWeeks($json) { 
    $organisers = $json->{'data'}->{'organisers'};
    $today      = $json->{'data'}->{'today'};
    //--------------------------------------------
    $sql = "select round, start, end_dt from period ";
    $sql .= " where organiser=? and ? between start and end_dt ";
    $stmt = $this->db->prepare($sql);
    //-----------------------------------------
    $orgweeks=[];
    foreach($organisers as $organiser) {
      $stmt->execute([ $organiser, $today ]);   
      $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if($periods) {
        array_push($orgweeks, array('organiser'=>$organiser, 'round'=>$periods[0]['round'],
                    'start'=>$periods[0]['start'], 'end_dt'=>$periods[0]['end_dt'] )
        );
      }
    };
    return $orgweeks;
  }
  public function getOrgsPools($json) {        // homepools.js
    $orgweeks = $this->getOrgWeeks($json);
    // $logger = getLogger();
    // $logger->info('1) pool.php>getOrgsPools:json', array('json' => $json));  
    // $logger->info('2) pool.php>orgweeks', array('orgweeks' => $orgweeks));  
    //--------------------------------------------
    $sql2 = "select id, pool_name, pool_type, entry_cost, entry_quorum";
    $sql2 .= ",entrants, pool_prize, payout ";
    $sql2 .= ",organiser, round, status ";
    $sql2 .= " from pool ";
    $sql2 .= " where organiser=? and round=? ";
    $pool = $this->db->prepare($sql2);
    //-----------------------------------------
    $results=[];
    foreach($orgweeks as $orgweek) {      
      $pool->execute([ $orgweek['organiser'], $orgweek['round'] ]);  
      while ($row = $pool->fetch(PDO::FETCH_ASSOC)) {
        $row['orgweek'] = $row['organiser'].':'.$row['round'];    // additional
        $row['pool_id'] = $row['id'];
        $row['start']  = $orgweek['start'];
        $row['end_dt'] = $orgweek['end_dt'];
        array_push($results, $row);
      };
    };
    return $results;
  } 
  //===============================================================================
  public function getOrgDatePools($json) {        // homepools.js
    $organiser = $json->{'data'}->{'organiser'};
    $today     = $json->{'data'}->{'today'};
    //--------------------------------------------
    $sql1 = "select round, start, end_dt from period ";
    $sql1 .= " where organiser=? and ? between start and end_dt ";
    $period = $this->db->prepare($sql1);
    $period->execute([ $organiser, $today ]);
    $arr = $period->fetchAll(PDO::FETCH_ASSOC);
    $round = $arr[0]['round']; 
    //-----------------------------------------------------
    $results=[];
    $sql2 = "select a.id, a.pool_name, a.pool_type, a.entry_cost, a.entry_quorum";
    $sql2 .= ",a.entrants, a.pool_prize, a.payout ";
    $sql2 .= ",a.organiser, a.round, a.status ";
    $sql2 .= ",b.start, b.end_dt ";
    $sql2 .= " from pool a ";
    $sql2 .= " inner join period b on a.organiser=b.organiser and a.round=b.round ";    
    $sql2 .= " where a.organiser=? and a.round=? ";
    $pool = $this->db->prepare($sql2);
    $pool->execute( [$organiser, $round ]);
    $results = $pool->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }  
  public function getOrgPools($id) {
    $sql = "select a.id,       a.pool_name,  a.pool_type, a.entry_cost, a.entry_quorum";
    $sql .= ",a.entrants, a.pool_prize, a.payout";
    $sql .= ",a.organiser, a.round, a.remarks"; 
    $sql .= ",b.start, b.end_dt "; 
    $sql .= " from pool a, period b ";
    $sql .= " where  a.organiser = ? ";
    $sql .= " and  a.round = b.round "; 
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
  public function getWeekPools($date) {
    $sql = "select a.id,  a.pool_name,  a.pool_type, a.entry_cost, a.entry_quorum";
    $sql .= ",a.entrants, a.pool_prize, a.payout ,   a.organiser,  a.round "; 
    $sql .= ",b.start, b.end_dt "; 
    $sql .= " from pool a, period b ";
    $sql .= " where a.organiser = b.organiser ";
    $sql .= " and   a.round     = b.round ";
    $sql .= " and  ? between b.start and b.end_dt ";        
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $date ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
  public function insertPool($json) { 
       $sql = "insert into pool(pool_name, pool_type, entry_cost, entry_quorum, entrants";
       $sql .= ", pool_prize, payout";
       $sql .= ",organiser, round";
       $sql .= ",remarks, created)";
       $sql .= " values (?,?,?,?,?,?,?,?,?,now())";
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
       $sql .= ",status=?";
       $sql .= ",organiser=?, round=?";
       $sql .= ",remarks=? where id=?";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ 
             $json->{'data'}->{'pool_name'}       ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'} ,$json->{'data'}->{'entry_quorum'}
            ,$json->{'data'}->{'entrants'}
            ,$json->{'data'}->{'pool_prize'} ,$json->{'data'}->{'payout'}  
            ,$json->{'data'}->{'status'}    
            ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'round'}   
            ,$json->{'data'}->{'remarks'}    ,$json->{'data'}->{'id'} ]);
            
    }
  public function autoGenPool() {
    $sql0  = "Select * from pool where organiser=? and round=? and pool_name=? ";

    $sql="select a.organiser, a.pool_name,  a.pool_type, a.entry_cost, a.entry_quorum, a.pool_prize, a.payout";
    $sql.=",b.round ";
    $sql.=" from plan a ";
    $sql.=" inner join period b on a.organiser = b.organiser";
    $sql2 = "insert into pool(pool_name, pool_type, entry_cost, entry_quorum";
    $sql2 .= ", pool_prize, payout";
    $sql2 .= ", organiser, round";
    $sql2 .= ", entrants , remarks, status, created)";
    $sql2 .= " values (?,?,?,?,?,?,?,?,0,'autogen','pending', now())";

    $plan  = $this->db->prepare($sql); 
    $pool2 = $this->db->prepare($sql2);
    $pool0 = $this->db->prepare($sql0);
    $plan->execute();
    while ($row = $plan->fetch(PDO::FETCH_ASSOC)) {
      //======= check for duplicate ======================================================
      $pool0->execute([ $row['organiser'], $row['round'], $row['pool_name'] ]);
      $rows0 = $pool0->fetchAll(PDO::FETCH_ASSOC);
      if(!$rows0) {
        $pool2->execute([ 
             $row['pool_name']  ,$row['pool_type']  ,$row['entry_cost'] ,$row['entry_quorum'] 
            ,$row['pool_prize'] ,$row['payout']     ,$row['organiser']  ,$row['round']   ]);
      };
    };
  }
  public function updateCount($pid) {  
    $sql  = "update pool set entrants=entrants + 1 where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $pid ]);             
  }
  public function reverseCount($pid) {  
    $sql  = "update pool set entrants=entrants - 1 where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $pid ]);             
  }
  public function updatePoolWinners($json) {   // identify tickets that win in the gool
    $sql =  "CALL g00_GenWinner()"; 
    $sp = $this->db->prepare($sql);
    $sp->execute();
    $sp->closeCursor();
    // - 2) create request records for winners (credit vcash) ---------------------
    $sql2 = "insert into request(username, activity, description, reference_number ";
    $sql2 .= ",exchange_rate, cash, vcash, created) ";
    $sql2 .= " values (?,?,?,?,?,?,?,now())";
    $insert = $this->db->prepare($sql2);

    $sql  = "Select * from ticket where organiser=? and round=?";
    $ticket = $this->db->prepare($sql);
    $ticket->execute([ $json->{'data'}->{'organiser'}
                      ,$json->{'data'}->{'round'} ]);
    $rows = $ticket->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
      if ($row['income'] > 0) {
        $insert->execute([ $row['username'],'win pool'
                        ,'won in pool#'. $row['pool_id']. ':'.$row['pool_name']  
                        ,'pool#'.$row['pool_id']
                        ,1,0
                        , $row['income']   ]);
        //----stage 3 (users - add to vcash)----------------------------------------
        $user = $this->db->prepare("update users set vcash=vcash+? where username=?");
        $user->execute([ $row['income'], $row['username'] ]); 
      }
    };
    //======= check for duplicate (duplicate in username & reference number (pool id) ========
  }
  public function getPoolGames($pid) {
    $sql =  "select a.id, a.username, a.ticket_id, a.pool_id, a.game_id, a.organiser, a.round, a.home_team, a.away_team"; 
    $sql .= ", a.bet_team, a.bet_date, a.bet_score, a.game_winner ";
    $sql .= ", a.home_odd, a.away_odd, a.odd_date, a.status ";
    $sql .= ", g.start, g.home_score, g.away_score ";
    $sql .= ", t.income ";
    $sql .= " from ticket_games a ";
    $sql .= " inner join game g on a.game_id = g.id";
    $sql .= " inner join ticket t on a.ticket_id = t.id";
    $sql .= " where a.pool_id=?";     
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $pid ]);      
    $pgames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$pgames) { 
      $this->msg = 'No rows'; 
      exit;
    };
    return $pgames;
  }   
  public function getPoolTickets($pid) {
    $sql =  "select id, username, organiser, round, pool_id  "; 
    $sql .= ", pool_name, total_score, income, fee, won, rank ";
    $sql .= ", status ";
    $sql .= " from ticket ";
    $sql .= " where pool_id=?";     
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $pid ]);      
    $ptickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$ptickets) { 
      $this->msg = 'No rows'; 
      exit;
    };
    return $ptickets;
  }      
}
