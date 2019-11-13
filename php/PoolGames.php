<?php
require __DIR__.'/DBclass.php';
class PoolGames {
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

  public function getPoolGames() {
    $sql = "select * from pool_games";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $poolgames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$poolgames) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $poolgames;
  }
  public function getGame2Pools() {
    $sql = "select t1.name as name, t1.date as date, entry_cost, entry_quorum, entrants";
    $sql .= ",t1.organiser as organiser, pool_prize";
    $sql .= ",payout, team1_count, team2_count, t1.id as id ";
    $sql .= ",t2.odd as odd ";
    $sql .= ",t3.username as username ";
    $sql .= "from pool_games  t1 ";
    $sql .= "inner join game t2 on t1.name = t2.name and t1.date = t2.date ";
    $sql .= "inner join bet  t3 on t1.name = t3.game_name and t1.date = t3.game_date and t1.id = t3.pool_id ";
    $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$pools) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $pools;
    }
  public function getGame3Pools($json) {
    $stmt = $this->db->prepare("select * from pool_games where home_team=? and away_team=? and date=?");
    $stmt->execute([ $json->{'data'}->{'home_team'},$json->{'data'}->{'away_team'},$json->{'data'}->{'date'} ]);
    $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$pools) {
        $this->msg = 'No rows'; 
        exit;
    };
    return $pools;
  }    
  public function getOrgBetPools($id) {
    $pools=[];
    $pool = $this->db->prepare("select * from pool_games where organiser=?");
    $team = $this->db->prepare("select name, logo from team where name = ?");
    $pool->execute([ $id ]);
    while ($row = $pool->fetch(PDO::FETCH_ASSOC)) {
        $team->execute([ $row['home_team'] ]);
        $row['image1'] = ($row2=$team->fetch(PDO::FETCH_ASSOC))  ? "images/".$row2['logo'] : '';
        $team->execute([ $row['away_team'] ]);
        $row['image2'] = ($row2=$team->fetch(PDO::FETCH_ASSOC))  ? "images/".$row2['logo'] : '';
        array_push($pools, $row);
    }
    return $pools;
  }
  public function getBetPools($json) {
    $stmt = $this->db->prepare("select * from pool_games where home_team=? and away_team=? and date=?");
    $stmt->execute([ $json->{'data'}->{'home_team'}
                    ,$json->{'data'}->{'away_team'}, $json->{'data'}->{'date'} ]);
    $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$pools) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $pools;
  }
  public function getWeekPools() {
    $sql = "select t1.home_team as home_team, t1.away_team as away_team, t1.date as date, entry_cost";
    $sql .= ", entry_quorum, entrants";
    $sql .= ",t1.organiser as organiser, pool_prize";
    $sql .= ",payout, team1_count, team2_count, t1.id as id";
    $sql .= ",t2.status as status ";
//    $sql .= ",t3.username as username ";
    $sql .= "from pool_games t1 ";
    $sql .= "inner join game t2 on t1.home_team = t2.home_team and t1.away_team = t2.away_team and t1.date = t2.date ";
//    $sql .= "inner join bet  t3 on t1.id = t3.pool_id ";
    $sql .= " where week(now()) = week(t1.date) ";
    $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$pools) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $pools;
  }
  public function getWeekStats() {
    $sql = "select organiser, weekofyear(date) as weekno, sum(team1_count) as count1, sum(team2_count) as count2 ";
    $sql .= " from pool_games ";
    $sql .= " group by organiser, weekofyear(date) ";
    $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$pools) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $pools;
  }
    public function insertPool($json) { 
       $sql = "insert into pool_games(organiser, home_team, away_team, date, round, bet_type, entry_cost, entry_quorum";
       $sql .= ", pool_prize, payout";
       $sql .= ",home_odd, away_odd, odd_date, created)";
       $sql .= " values (?,?,?,?,?,?,?,?,?,?,?,?,?, now())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'organiser'}, $json->{'data'}->{'home_team'}, $json->{'data'}->{'away_team'}    
                        ,$json->{'data'}->{'date'},      $json->{'data'}->{'round'}     
                        ,$json->{'data'}->{'bet_type'} 
                        ,$json->{'data'}->{'entry_cost'},$json->{'data'}->{'entry_quorum'}
                        ,$json->{'data'}->{'pool_prize'}
                        ,$json->{'data'}->{'payout'}    ,$json->{'data'}->{'home_odd'}
                        ,$json->{'data'}->{'away_odd'}  ,$json->{'data'}->{'odd_date'} 
                    ]);
    }
    public function deletePool($id) {
        $stmt = $this->db->prepare("delete from pool_games where id=?");
        $stmt->execute([ $id ]);
    }
    public function updatePool($json) {        // object, not array
        $sql = "update pool_games set pool_id=?, bet_type=?, organiser=?"; 
        $sql .= ", home_team=?, away_team=?, date= ?, status=? where id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'pool_id'}, $json->{'data'}->{'bet_type'}, $json->{'data'}->{'organiser'}      
                        ,$json->{'data'}->{'home_team'}, $json->{'data'}->{'away_team'}  
                        ,$json->{'data'}->{'date'}
                        ,$json->{'data'}->{'status'}
                        ,$json->{'data'}->{'id'} ]);
    }
    public function updatePoolCount($json) {        // object, not array
        $sql = "update pool_games set entrants=entrants+1";     

        $teams = $json->{'data'}->{'teams'};         
        if ($json->{'data'}->{'bet_winner'} == $teams[0]) { 
            $sql .= ",team1_count=team1_count + 1 ";
        } else {
            $sql .= ",team2_count=team2_count + 1 "; 
        };
        $sql .= " where id=?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'id'} ]);
    }
//===============================================================
// auto gen
/*    
    public function generateNewPools() {
      this week in php
      $sql  = "Select organiser, week(date) as weekno, date, home_team, away_team, round";
      $sql .= ", game_winner, home_score, away_score, status, id ";
      $sql .= "from game where organiser=? and date=? ";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $keys[0], $keys[1] ]);
      $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(!$games) {
        $this->msg = 'No rows'; 
        exit;
      };
      return $games;



       $sql = "insert into pool_games(organiser, home_team, away_team, date, bet_type, entry_cost, entry_quorum, organiser";
       $sql .= ", pool_prize, payout";
       $sql .= ",home_odd, away_odd, odd_date, created)";
       $sql .= " values (?,?,?,?,?,?,?,?,?,?,?,?,?, now())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'organiser'}, $json->{'data'}->{'home_team'}, $json->{'data'}->{'away_team'}    
                        ,$json->{'data'}->{'date'},      $json->{'data'}->{'bet_type'} 
                        ,$json->{'data'}->{'entry_cost'},$json->{'data'}->{'entry_quorum'}
                        ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'pool_prize'}
                        ,$json->{'data'}->{'payout'}    ,$json->{'data'}->{'home_odd'}
                        ,$json->{'data'}->{'away_odd'}  ,$json->{'data'}->{'odd_date'} 
                    ]);
    }

*/

}