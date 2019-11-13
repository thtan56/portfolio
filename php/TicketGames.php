<?php
require __DIR__.'/DBclass.php';
// require_once('configLog.php');

class TicketGames {
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
  public function getTicketGames($uom, $key) {
  //    $logger = getLogger(); 
    $sql =  "select a.id, a.username, a.ticket_id, a.pool_id, a.game_id, a.organiser, a.round, a.home_team, a.away_team"; 
    $sql .= ", a.bet_team, a.bet_date, a.bet_score, a.game_winner ";
    $sql .= ", a.home_odd, a.away_odd, a.odd_date, a.status ";
    $sql .= ", concat(a.organiser,':',a.round) as orgweek ";      // computed field
    $sql .= ", g.start, g.game_winner, g.home_score, g.away_score ";
    $sql .= ", if(a.bet_team = g.game_winner, 1, 0) as win_point ";     
    $sql .= ", t.pool_type, t.entry_count, t.entry_cost, t.entry_quorum";
    $sql .= ", t.pool_prize, t.payout, t.pool_name ";
    $sql .= " from ticket_games a, game g, ticket t ";
    if     ($uom === 'ticket_id') { $sql .= " where a.ticket_id=? "; }
    elseif ($uom === 'game_id')   { $sql .= " where a.game_id=? "; }   // homegame.js
    elseif ($uom === 'username')  { $sql .= " where a.username=?  "; }
    elseif ($uom === 'organiser') { $sql .= " where a.organiser=? "; }   // manticketgames.js

    $sql .= " and a.game_id   = g.id ";
    $sql .= " and a.ticket_id = t.id";
//  $logger->info('1) getTicketGames.php', array('sql' => ['sql'=>$sql]) );     
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $key ]);             
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
//      $logger->info('2) getTicketGames.php', array('games' => $games) );
    if(!$games) { 
      $this->msg = 'No rows'; 
      exit;
     };
    return $games;
  }
  //======== system management sections (apiPool.html)========================
    public function getOrgWeek() {
      $sql =  "select concat(a.organiser,':',a.round) as orgweek"; 
      $sql .= ",week(p.start) as week, a.pool_id, a.username, a.organiser";
      $sql .= ",a.ticket_id, a.game_id, sum(a.bet_score) as betscore ";
      $sql .= " from ticket_games a ";  
      $sql .= " inner join period p on a.organiser = p.organiser and a.round = p.round ";            
      $sql .= " group by  concat(a.organiser,':',a.round), week(p.start)";
      $sql .= " ,a.pool_id, a.username, a.ticket_id, a.game_id ";    
      $stmt = $this->db->prepare($sql); 
      $stmt->execute();       
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }        
    public function getPoolUserGames($json) {    // orgweek, pid, username
      $sql =  "select concat(a.organiser,':',a.round) as orgweek, a.pool_id ";
      $sql .= ", a.username, a.game_id, a.bet_team, a.bet_amount ";
      $sql .= ", g.home_team, g.away_team, g.start "; 
      $sql .= ", p.pool_name, p.pool_type, p.entry_cost "; 
      $sql .= ", p.entry_quorum, p.pool_prize, p.payout, p.entrants "; 
      $sql .= " from ticket_games a ";
      $sql .= " inner join game g on a.game_id = g.id ";    
      $sql .= " inner join pool p on a.pool_id = p.id "; 
      $sql .= " where concat(a.organiser,':',a.round)=? and a.username=? and a.pool_id=?";   
      $stmt = $this->db->prepare($sql); 
      $stmt->execute([ $json->{'data'}->{'orgweek'} 
                        ,$json->{'data'}->{'username'} 
                        ,$json->{'data'}->{'pool_id'}      ]);            
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }    
    public function getPoolGames($json) {    // orgweek, pid
      $sql =  "select concat(a.organiser,':',a.round) as orgweek, a.pool_id ";
      $sql .= ", a.username, a.game_id ";
      $sql .= ", g.home_team, g.away_team, g.start "; 
      $sql .= ", p.pool_name, p.pool_type, p.entry_cost "; 
      $sql .= ", p.entry_quorum, p.pool_prize, p.payout, p.entrants "; 
      $sql .= " from ticket_games a ";
      $sql .= " inner join game g on a.game_id = g.id ";    
      $sql .= " inner join pool p on a.pool_id = p.id "; 
      $sql .= " where concat(a.organiser,':',a.round)=? and a.pool_id=?";   
      $stmt = $this->db->prepare($sql); 
      $stmt->execute([ $json->{'data'}->{'orgweek'} 
                        ,$json->{'data'}->{'pool_id'}      ]);            
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }   
    public function getOrgWeekTicketGames($orgweek) {    // orgweek   (for ticket summary)
    // $logger = getLogger();

      $sql =  "select concat(a.organiser,':',a.round) as orgweek, a.username ";
      $sql .= ", a.ticket_id, a.game_id, a.pool_id, a.id "; 
      $sql .= ", a.bet_team, a.bet_score ";
      $sql .= ", g.home_team, g.away_team, g.start, g.game_winner, g.home_score, g.away_score, g.status "; 
      $sql .= " from ticket_games a ";
      $sql .= " inner join game g on a.game_id = g.id ";    
      $sql .= " where concat(a.organiser,':',a.round)=? ";
    // $logger->info('21) getOrgWeekTicketGames.php', array('orgweek' => ['orgweek'=>$orgweek]) );      
      // $sql .= ", p.pool_name, p.pool_type, p.entry_cost "; 
      // $sql .= ", p.entry_quorum, p.pool_prize, p.payout, p.entrants "; 
      // $sql .= " inner join pool p on a.pool_id = p.id "; 
      $stmt = $this->db->prepare($sql); 
      $stmt->execute([ $orgweek ]);            
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // $logger->info('25) getOrgWeekTicketGames.php', array('results' => $results) );
      return $results;
    }     
    public function getTicketGames2($json) {    // orgweek, ticket#   (for game summary)
      $sql =  "select concat(a.organiser,':',a.round) as orgweek, a.username, a.ticket_id ";
      $sql .= ", a.game_id, a.pool_id, a.bet_team, a.bet_score ";
      $sql .= ", g.home_team, g.away_team, g.start, g.game_winner, g.home_score, g.away_score, g.status "; 
      $sql .= ", p.pool_name, p.pool_type, p.entry_cost "; 
      $sql .= ", p.entry_quorum, p.pool_prize, p.payout, p.entrants "; 
      $sql .= " from ticket_games a ";
      $sql .= " inner join game g on a.game_id = g.id ";    
      $sql .= " inner join pool p on a.pool_id = p.id "; 
      $sql .= " where concat(a.organiser,':',a.round)=? and a.game_id=?";   
      $stmt = $this->db->prepare($sql); 
      $stmt->execute([ $json->{'data'}->{'orgweek'} 
                        ,$json->{'data'}->{'game_id'}      ]);            
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }        
    public function getUserTicketGames($json) {    // orgweek, username, ticket#
      $sql =  "select concat(a.organiser,':',a.round) as orgweek, a.username, a.ticket_id ";
      $sql .= ", a.game_id, a.pool_id, a.bet_team, a.bet_score ";
      $sql .= ", g.home_team, g.away_team, g.start, g.game_winner, g.home_score, g.away_score "; 
      $sql .= ", p.pool_name, p.pool_type, p.entry_cost "; 
      $sql .= ", p.entry_quorum, p.pool_prize, p.payout, p.entrants "; 
      $sql .= " from ticket_games a ";
      $sql .= " inner join game g on a.game_id = g.id ";    
      $sql .= " inner join pool p on a.pool_id = p.id "; 
      $sql .= " where concat(a.organiser,':',a.round)=? and a.username=? and a.ticket_id=?";   
      $stmt = $this->db->prepare($sql); 
      $stmt->execute([ $json->{'data'}->{'orgweek'} 
                        ,$json->{'data'}->{'username'} 
                        ,$json->{'data'}->{'ticket_id'}      ]);            
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    } 
    //---  my games result ---------------------------
    public function getOrgRndUserTicketGames($json) {
      $sql =  "select a.id, a.username, a.ticket_id, a.pool_id, a.game_id, a.organiser, a.round, a.home_team, a.away_team"; 
      $sql .= ", a.bet_team, a.bet_date, a.bet_score ";
      $sql .= ", a.home_odd, a.away_odd, a.odd_date, a.status ";
      $sql .= ", g.start, g.game_winner, g.home_score, g.away_score ";
      $sql .= ", if(a.bet_team = g.game_winner, 1, 0) as win_point ";     
      $sql .= ", t.pool_type, t.entry_count, t.entry_cost, t.entry_quorum";
      $sql .= ", t.pool_prize, t.payout, t.pool_name ";
      $sql .= " from ticket_games a, game g, ticket t ";
      $sql .= " where a.organiser=? and a.round=? and a.username=? ";    
      $sql .= " and a.game_id   = g.id ";
      $sql .= " and a.ticket_id = t.id";
      $sql .= " order by a.pool_id, a.game_id "; 
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'organiser'} 
                      ,$json->{'data'}->{'round'}
                      ,$json->{'data'}->{'username'} ]);
      $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$games) { 
        $this->msg = 'No rows'; 
        exit;
      };
      return $games;
    }    
    //---  leadership result ---------------------------
    public function getOrgRndTicketGames($json) {
      $sql =  "select a.id, a.username, a.ticket_id, a.pool_id, a.game_id, a.organiser, a.round, a.home_team, a.away_team"; 
      $sql .= ", a.bet_team, a.bet_date, a.bet_score ";
      $sql .= ", a.home_odd, a.away_odd, a.odd_date, a.status ";
      $sql .= ", g.start, g.game_winner ";
      $sql .= ", if(a.bet_team = g.game_winner, 1, 0) as win_point ";     
      $sql .= ", t.pool_type, t.entry_count, t.entry_cost, t.entry_quorum";
      $sql .= ", t.pool_prize, t.payout, t.pool_name ";
      $sql .= " from ticket_games a, game g, ticket t ";
      $sql .= " where a.organiser=? and a.round=? ";    
      $sql .= " and a.game_id   = g.id ";
      $sql .= " and a.ticket_id = t.id";
      $sql .= " order by a.pool_id, a.game_id, a.username"; 
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'organiser'} ,$json->{'data'}->{'round'}
                     ]);
      $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$games) { 
        $this->msg = 'No rows'; 
        exit;
      };
      return $games;
    }
    public function getTicketGamesStat($json) {
      $results=[];
      $ticket = $this->db->prepare("select entry_cost, pool_prize,payout, top, won from ticket where id = ?");
      
      $sql =  "select organiser, round, pool_id, ticket_id, username, sum(bet_score) as total "; 
      $sql .= " from ticket_games ";
      $sql .= " group by organiser, round, pool_id, ticket_id, username ";
      $sql .= " having organiser=? and round=? "; 
      $sql .= " order by pool_id, username ";     
      $tgames = $this->db->prepare($sql);
      $tgames->execute([ $json->{'data'}->{'organiser'} ,$json->{'data'}->{'round'} ]); 
      while ($row = $tgames->fetch(PDO::FETCH_ASSOC)) {
        $ticket->execute([ $row['ticket_id'] ]);
        $row['won']=($row2=$ticket->fetch(PDO::FETCH_ASSOC))  ? $row2['won'] : 0;
        $row['entry_cost']=$row2['entry_cost'];
        $row['pool_prize']=$row2['pool_prize'];
        $row['payout']=$row2['payout'];
        $row['top']=$row2['top'];
        $row['income'] = strval($row2['pool_prize']) * $row2['won'] / strval($row2['top']);
        array_push($results, $row);
      };
      return $results;
    }
    //------------------------------------------------
    public function insertGame2Ticket($json) {  
      $sql = "insert into ticket_games(ticket_id, pool_id, game_id, home_team, away_team, bet_team, bet_amount";
      $sql .= ",username, organiser, round, bet_date, status) ";
      $sql .= " values (?,?,?,?,?,?,?,?,?,?,now(), 'pending')";
      $tgames = $this->db->prepare($sql);
      $tgames->execute([ $json->{'data'}->{'tid'} ,$json->{'data'}->{'pool_id'}
                      ,$json->{'data'}->{'gid'}
                      ,$json->{'data'}->{'home_team'} ,$json->{'data'}->{'away_team'}
                      ,$json->{'data'}->{'bet_team'}  ,$json->{'data'}->{'bet_amount'}
                      ,$json->{'data'}->{'username'}
                      ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'round'}
                     ]); 
      // stage 2 ---   no need, only when buy ticket
      //$pool = $this->db->prepare("update pool set entrants=entrants + 1 where id=?");
      //$pool->execute([ $json->{'data'}->{'pool_id'} ]);
    }
    public function deleteGame($json) {
      $tgames = $this->db->prepare("delete from ticket_games where ticket_id=? and game_id=?");
      $tgames->execute([ $json->{'data'}->{'tid'} ,$json->{'data'}->{'gid'} ]);
      // stage 2 ---
      $pool = $this->db->prepare("update pool set entrants=entrants - 1 where id=?");
      $pool->execute([ $json->{'data'}->{'pool_id'} ]);
    }
    //---------manTicketGames.js------------------------------------------
    public function insertTicketGames($json) {        // object, not array
      $sql  = "insert into ticket_games(username, game_id, ticket_id, pool_id, organiser, home_team, away_team, round ";
      $sql .= ", bet_team, home_odd, away_odd, odd_date";
      $sql .= ", bet_score, status, created) ";
      $sql .= " values (?,?,?,?,?,?,?,?,?,?,?,?,?,0,'pending',now() )";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'username'} ,$json->{'data'}->{'game_id'},$json->{'data'}->{'ticket_id'},$json->{'data'}->{'pool_id'}
           ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'home_team'}        ,$json->{'data'}->{'away_team'}
           ,$json->{'data'}->{'round'} 
           ,$json->{'data'}->{'bet_team'}  
           ,$json->{'data'}->{'home_odd'}  ,$json->{'data'}->{'away_odd'}         ,$json->{'data'}->{'odd_date'}  
           ,$json->{'data'}->{'status'} ]);
    }
    public function updateTicketGames($json) {        // object, not array
       $sql  = "update ticket_games set ";
       $sql .= " username=?, game_id=?, ticket_id=?, pool_id=?, organiser=?, home_team=?, away_team=?, round=? ";
       $sql .= ",bet_team=?, bet_score=?";
       $sql .= ",home_odd=?, away_odd=?, odd_date=? ";
       $sql .= ",status=? where id=?";       
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ $json->{'data'}->{'username'}, $json->{'data'}->{'game_id'}, $json->{'data'}->{'ticket_id'},$json->{'data'}->{'pool_id'}
            ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'home_team'}   ,$json->{'data'}->{'away_team'}
            ,$json->{'data'}->{'round'} 
            ,$json->{'data'}->{'bet_team'}  ,$json->{'data'}->{'bet_score'}
            ,$json->{'data'}->{'home_odd'}  ,$json->{'data'}->{'away_odd'}    ,$json->{'data'}->{'odd_date'}         
            ,$json->{'data'}->{'status'}    ,$json->{'data'}->{'id'} ]);
    }
    //==========================
    public function countEntry($json) {
      $sql =  "select a.ticket_id, a.game_id, t.entry_quorum, count(a.username) as usercount ";
      $sql .= " from ticket_games a ";
      $sql .= " inner join ticket t on a.ticket_id = t.id ";
      $sql .= " where a.ticket_id=? and a.game_id=? ";
      $sql .= " group by a.ticket_id, a.game_id, t.entry_quorum ";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'tid'} ,$json->{'data'}->{'gid'} ]);   
      $tgcount = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$tgcount) { 
        $this->msg = 'No rows'; 
        exit;
      };
      return $tgcount;
    }
    //===========================
    public function getGameUsers($json) {   // 29/11/18
      $sql = "select username, pool_id, game_id from ticket_games ";
      $sql .= " where pool_id=? and game_id=? ";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'pid'}, $json->{'data'}->{'gid'} ]); 
      $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$users) {
          $this->msg = 'No rows'; 
          exit;
      };
      return $users;
    }
}