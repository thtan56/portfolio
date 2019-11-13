<?php
require __DIR__.'/DBclass.php';
class Ticket {
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
    public function getTickets($username=NULL) {
      $sql =  "select concat(organiser,':',round) as orgweek, organiser, round, username "; 
      $sql .= ", id as ticket_id, start, end, total_score, income ";
      $sql .= ", pool_id, pool_name, entry_cost, entry_quorum, pool_prize, payout, entry_count "; 
      $sql .= " from ticket ";

      if ($username !== NULL) {
        $stmt = $this->db->prepare($sql . " where username=?");
        $stmt->execute([ $username ]);           
      } else {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
      };
      $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$tickets) {
          $this->msg = 'No rows'; 
          exit;
      };
      return $tickets;
    }
    public function getOrgRndTickets($json) {  
      $tickets=[];
      $sql  = "Select * from ticket where organiser=? and round=? and username=?";
      $ticket = $this->db->prepare($sql);
      $ticket->execute([ $json->{'data'}->{'organiser'}
                        ,$json->{'data'}->{'round'}
                        ,$json->{'data'}->{'username'}     ]);
      // stage 2
      $sql2 = "Select game_id as id, home_team, away_team, bet_team from ticket_games ";
      $sql2 .= " where ticket_id=? and username = ?"; 
      $tgame = $this->db->prepare($sql2); 
      while ($row = $ticket->fetch(PDO::FETCH_ASSOC)) {
        $tgame->execute([ $row['id'], $row['username']  ]);  // tid
        $row2=$tgame->fetchAll(PDO::FETCH_ASSOC); 
        $row['games'] = !$row2 ? [] : $row2;
        array_push($tickets, $row);
      };
      return $tickets;
    }
    //*************************************************************************
    public function getOrgWeekTickets() {
      $sql =  "select concat(organiser,':',round) as orgweek, username, id as ticket_id ";
      $sql .= " from ticket ";
      $sql .= " group by  concat(organiser,':',round), username, id ";    
      $stmt = $this->db->prepare($sql); 
      $stmt->execute();       
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }
    public function getOrgRndTicketsByPid($json) {  
      $sql  = "Select * from ticket where organiser=? and round=? and pool_id=?";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'organiser'}
                      ,$json->{'data'}->{'round'}
                      ,$json->{'data'}->{'pool_id'}     ]);
      $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$tickets) {
        $this->msg = 'No rows'; 
        exit;
      };
      return $tickets;
    }
    public function getOrgRndPlTicketsByUid($json) {
      $results=[];  
      $sql  = "Select id from ticket where organiser=? and round=? and pool_id=? and username=? ";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'organiser'}
                        ,$json->{'data'}->{'round'}
                        ,$json->{'data'}->{'pool_id'}    
                        ,$json->{'data'}->{'username'}     ]);
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($results, $row['id']);
      };
      return $results;   // array of ticket id
    }    
    public function getPoolUserCount() {
      $sql = 'SELECT pool_id, username, COUNT(*) as count FROM ticket GROUP BY pool_id, username';
      $stmt = $this->db->prepare($sql); 
      $stmt->execute();       
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $results;
    }      
    //=====================================================================================
    public function getUserTickets2($json) {    // orgweek, username
      $results=[];
      $sql =  "select concat(organiser,':',round) as orgweek, username "; 
      $sql .= ", id as ticket_id, start, end, total_score, income ";
      $sql .= ", pool_id, pool_name, entry_cost, entry_quorum, pool_prize, payout, entry_count "; 
      $sql .= " from ticket ";
      $sql .= " where concat(organiser,':',round)=? and username=? ";   
      $stmt = $this->db->prepare($sql); 

      $sql2 =  "select concat(organiser,':',round) as orgweek, username, ticket_id, count(*) as gamecount ";
      $sql2 .= " from ticket_games ";
      $sql2 .= " where concat(organiser,':',round)=? and username=? and ticket_id=? ";   
      $sql2 .= " group by concat(organiser,':',round), username, ticket_id ";
      $stmt2 = $this->db->prepare($sql2); 
      $stmt->execute([ $json->{'data'}->{'orgweek'}, $json->{'data'}->{'username'} ]);            
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt2->execute([ $row['orgweek'], $row['username'], $row['ticket_id'] ]);
        if ($stmt2->rowCount() >  0) {
          $rowCount=$stmt2->rowCount();
        } else {
          $rowCount=0;
        };
        $row['gamecount']=$rowCount;
        array_push($results, $row);
      };
      return $results;
    } 
    public function getUserTickets2b($json) {    // username only
      $results=[];
      $sql =  "select concat(organiser,':',round) as orgweek, username "; 
      $sql .= ", id as ticket_id, start, end, total_score, income ";
      $sql .= ", pool_id, pool_name, entry_cost, entry_quorum, pool_prize, payout, entry_count "; 
      $sql .= " from ticket ";
      $sql .= " where username=? ";   
      $stmt = $this->db->prepare($sql); 

      $sql2 =  "select concat(organiser,':',round) as orgweek, username, ticket_id, count(*) as gamecount ";
      $sql2 .= " from ticket_games ";
      $sql2 .= " where concat(organiser,':',round)=? and username=? and ticket_id=? ";   
      $sql2 .= " group by concat(organiser,':',round), username, ticket_id ";
      $stmt2 = $this->db->prepare($sql2); 
      $stmt->execute([ $json->{'data'}->{'username'} ]);            
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt2->execute([ $row['orgweek'], $row['username'], $row['ticket_id'] ]);
        if ($stmt2->rowCount() >  0) {
          $rowCount=$stmt2->rowCount();
        } else {
          $rowCount=0;
        };
        $row['gamecount']=$rowCount;
        array_push($results, $row);
      };
      return $results;
    }
    public function insertTicket($json) { 
       // start, end dates
       $sql = "insert into ticket(username, pool_id, pool_name, pool_type, entry_cost, entry_quorum";
       $sql .= ",pool_prize, payout, organiser, round";
       $sql .= ",start, end, created)";
       $sql .= " values (?,?,?,?,?,?,?,?,?,?,?,?,now())";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ $json->{'data'}->{'username'} ,$json->{'data'}->{'pool_id'}
            ,$json->{'data'}->{'pool_name'}     ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'}    ,$json->{'data'}->{'entry_quorum'}
            ,$json->{'data'}->{'pool_prize'}    ,$json->{'data'}->{'payout'} 
            ,$json->{'data'}->{'organiser'}    ,$json->{'data'}->{'round'} 
            ,$json->{'data'}->{'start'}        ,$json->{'data'}->{'end'}  
            ]);
    }
    public function insertUpdate($json) {    // buy ticket 
      // stage 1 (ticket - insert )
      $sql = "insert into ticket(username, pool_id, pool_name, pool_type, entry_cost, entry_quorum";
      $sql .= ",pool_prize, payout, organiser, round";
      $sql .= ",start, end, status, created)";
      $sql .= " values (?,?,?,?,?,?,?,?,?,?,?,?,'pending', now())";
      $ticket = $this->db->prepare($sql);
      $ticket->execute([ $json->{'data'}->{'username'} ,$json->{'data'}->{'pool_id'}
            ,$json->{'data'}->{'pool_name'}     ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'}    ,$json->{'data'}->{'entry_quorum'}
            ,$json->{'data'}->{'pool_prize'}    ,$json->{'data'}->{'payout'} 
            ,$json->{'data'}->{'organiser'}    ,$json->{'data'}->{'round'} 
            ,$json->{'data'}->{'start'}        ,$json->{'data'}->{'end'}  
            ]);
      //----stage 2 (users - deduct vcash)----------------------------------------
      $user = $this->db->prepare("update users set vcash=? where username=?");
      $user->execute([ $json->{'data'}->{'balvcash'} - $json->{'data'}->{'entry_cost'}
                      ,$json->{'data'}->{'username'} ]); 
            // stage 3 (pool - add entrant count) --- 
      $pool = $this->db->prepare("update pool set entrants=entrants + 1 where id=?");
      $pool->execute([ $json->{'data'}->{'pool_id'} ]);
      // -- stage 4 ( request - record buying of ticket# ) ---------------------------------------------
//      $cost = -1 * intval($json->{'data'}->{'entry_cost'});
      $sql = "insert into request(username, activity, description, reference_number ";
      $sql .= ",exchange_rate, cash, vcash, created) ";
      $sql .= " values (?,?,?,?,?,?,?,now())";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'username'} ,'buy ticket'
        ,'betting for pool#'. $json->{'data'}->{'pool_id'}. ':'.$json->{'data'}->{'pool_name'}  
        ,'',1,0
        , -1 * $json->{'data'}->{'entry_cost'}      ]);
      // -- stage 5 ( return new ticket# ) ---------------------------------------------
      $stmt = $this->db->prepare("select max(id) as tid from ticket");
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);     // tid                  
      return $result;    // tid
    }
    public function deleteTicket($id) {
        $stmt = $this->db->prepare("delete from ticket where id=?");
        $stmt->execute([ $id ]);
    }
    public function deleteUpdate($json) {
      $ticket = $this->db->prepare("delete from ticket where id=?");
      $ticket->execute([ $json->{'data'}->{'id'} ]);
      
      $user = $this->db->prepare("update users set vcash=? where username=?");
      $user->execute([ $json->{'data'}->{'balvcash'} + $json->{'data'}->{'entry_cost'},
                       $json->{'data'}->{'username'} ]);    
    }
    public function updateTicket($json) {        // object, not array
       $sql  = "update ticket set username=?"; 
       $sql .= ",pool_id=?, pool_name=?, pool_type=?";
       $sql .= ",entry_cost=?, entry_quorum=?, pool_prize=?, payout=?";
       $sql .= ",odd_date=?, home_odd=?, away_odd=?";
       $sql .= ",start=?, end=?";
       $sql .= ",remarks=? where id=?";
       
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ $json->{'data'}->{'username'}  ,$json->{'data'}->{'pool_id'}
            ,$json->{'data'}->{'pool_name'}       ,$json->{'data'}->{'pool_type'} 
            ,$json->{'data'}->{'entry_cost'} ,$json->{'data'}->{'entry_quorum'}
            ,$json->{'data'}->{'pool_prize'} ,$json->{'data'}->{'payout'}
            ,$json->{'data'}->{'odd_date'}   ,$json->{'data'}->{'home_odd'},$json->{'data'}->{'away_odd'}       
            ,$json->{'data'}->{'start'}   ,$json->{'data'}->{'end'}  
            ,$json->{'data'}->{'remarks'}    ,$json->{'data'}->{'id'} ]);
            
    }
    public function getTicketStatus($json) {     // poolsummary  
      $sql  = "SELECT a.organiser, a.round, a.rank, a.payout, a.pool_prize, a.top ";
      $sql .= ", a.total_score, a.income, a.username, a.pool_id, a.id as ticket_id ";
      $sql .= ", p.pool_type, p.pool_name, p.entry_cost, p.entry_quorum, p.entrants, p.status ";
      $sql .= " FROM test.ticket a ";
      $sql .= " inner join pool p on a.pool_id = p.id ";
      $sql .= " where concat(a.organiser,':',a.round)=? and a.pool_id=?";   
      $sql .= " order by a.organiser, a.round, a.pool_id, a.total_score desc";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'orgweek'} 
                      ,$json->{'data'}->{'pool_id'}      ]);   
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);                    
      return $results;    // tid
    }
    public function getTicketStatus2($json) {     // poolresults
      $sql  = "SELECT a.organiser, a.round, a.rank, a.payout, a.pool_prize, a.top ";
      $sql .= ", a.total_score, a.income, a.username, a.pool_id ";
      $sql .= ", p.pool_type, p.pool_name, p.entry_cost ";
      $sql .= " FROM test.ticket a ";
      $sql .= " inner join pool p on a.pool_id = p.id ";
      $sql .= " where a.organiser = ? and  a.round=?";   
      $sql .= " order by a.organiser, a.round, a.pool_id, a.total_score desc";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'organiser'} 
                      ,$json->{'data'}->{'round'}      ]);   
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);                    
      return $results;    // tid
    }    
    //===================================
}