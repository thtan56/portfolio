<?php
require __DIR__.'/DBclass.php';
//require_once('configLog.php');

class Game {
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
  
  public function getGames() {
    $stmt = $this->db->prepare("select * from game order by start desc");
    $stmt->execute();
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$games) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $games;
  }
  public function getGame($id) {
    $stmt = $this->db->prepare("select * from game where id=?");
    $stmt->execute([ $id ]);
    $game = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$game) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $game;
  }
  // round = period
  public function getGameSummary() {
    $sql  = "SELECT organiser, week(start) as weekno, start, round, count(*) as gamecount FROM game ";
    $sql .= "group by organiser, week(start), start, round";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$games) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $games;
  }
  public function getGameLeaders() {
//    $logger = getLogger();
    $sql  = "SELECT organiser, round, count(*) as weekcount FROM ticket ";
    $sql .= " group by organiser, round";
    $stmt = $this->db->prepare($sql);

    $sql2  = "SELECT organiser, round, username, sum(total_score) as tscore";
    $sql2 .= ", sum(income) as winning FROM ticket ";
    $sql2 .= " group by organiser, round, username";
    $sql2 .= " having organiser=? and round=? ";
    $sql2 .= " order by sum(total_score) desc ";   // highest top
    $stmt2 = $this->db->prepare($sql2);

    $stmt->execute();
    $results=[];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {   
      $stmt2->execute([$row['organiser'], $row['round'] ]);
      $users=[];
      while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {  
        // $logger->info('1) game.php>getGameLeaders', array('row2' => $row2));      
        array_push($users, array('username'=>$row2['username']
                                ,'userscore'=>$row2['tscore']
                                ,'userincome'=>$row2['winning'] )
                  );
      };
      array_push($results, array('week'=>$row['organiser'].':'.$row['round'],
                                  'entrants'=>$users
                                ) );
    };
    return $results;
  }  
  public function getWeek1Tickets() {
    //$logger = getLogger();
    $sql  = "SELECT concat(organiser,':',round) as orgweek, count(*) as weekcount ";
    $sql .= " FROM ticket ";
    $sql .= " group by organiser, round";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;    
  }    
  public function getWeek2Games() {
    $sql  = "SELECT concat(organiser,':',round) as orgweek, home_team, away_team, start ";
    $sql .= " FROM game ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }      
    //$sql3  = "SELECT game_id, count(username) ";
    //$sql3 .= " FROM ticketgame ";
    //$sql3 .= " group by game_id ";
    //$sql3 .= " having game_id=? ";
    //$stmt3 = $this->db->prepare($sql3);  
  public function getGameWeeks() {
    //$logger = getLogger();
    $sql  = "SELECT organiser, round, count(*) as weekcount ";
    $sql .= " FROM ticket ";
    $sql .= " group by organiser, round";
    $stmt = $this->db->prepare($sql);

    $sql2  = "SELECT organiser, round, home_team, away_team, start ";
    $sql2 .= " ,id, home_score, away_score, status ";
    $sql2 .= " FROM game ";
    $sql2 .= " where organiser=? and round=? ";
    $stmt2 = $this->db->prepare($sql2);

    $stmt->execute();
    $results=[];
    while ($trow = $stmt->fetch(PDO::FETCH_ASSOC)) {   
      $stmt2->execute([$trow['organiser'], $trow['round'] ]);
      $games=[];
      while ($grow = $stmt2->fetch(PDO::FETCH_ASSOC)) {  
        //$logger->info('1) game.php>getGameWeeks', array('row2' => $row2));      
        array_push($games, array('home_team' =>$grow['home_team']  ,'away_team' =>$grow['away_team']
                                ,'start'     =>$grow['start']      ,'game_id'   =>$grow['id']
                                ,'home_score'=>$grow['home_score'] ,'away_score'=>$grow['away_score']
                                ,'status'    =>$grow['status']
                                ,'orgweek'   =>$grow['organiser'].":".$grow['round']
                                 ));
      };
      array_push($results, array('week'=>$trow['organiser'].':'.$trow['round'],    // for tabs label
                                  'games'=>$games
                                ) );
    };
    return $results;
  }  
  public function getOrgGames($organiser) {    
    $games=[];
    $sql  = "Select organiser, home_team, away_team"; 
    // $sql .= ",weekofyear(start) as weekno";
    $sql .= ", start ";
    $sql .= ",home_score, away_score,home_odd, away_odd";
    $sql .= ",round, venue, game_winner, status, id ";
    $sql .= "from game where organiser=? ";
    $sql .= "order by start desc ";  
    $game = $this->db->prepare($sql);
    $team = $this->db->prepare("select name, logo from team where name = ?");
    $game->execute([ $organiser ]);
    while ($row = $game->fetch(PDO::FETCH_ASSOC)) {
        $team->execute([ $row['home_team'] ]);
        $row['image1'] = ($row2=$team->fetch(PDO::FETCH_ASSOC))  ? "images/".$row2['logo'] : '';
        $team->execute([ $row['away_team'] ]);
        $row['image2'] = ($row2=$team->fetch(PDO::FETCH_ASSOC))  ? "images/".$row2['logo'] : '';
        array_push($games, $row);
    }
    return $games;
  }
  public function getOrgRndGames($json) {  
    $games=[];
    $sql  = "Select id, home_team, away_team, start ";
    $sql .= ",organiser, round, venue, status";
    $sql .= ",home_score, away_score, home_odd, away_odd ";
    $sql .= "from game where organiser=? and round=? ";
    $sql .= "order by start asc, home_team ";  
    $game = $this->db->prepare($sql);
    $game->execute([ $json->{'data'}->{'organiser'},$json->{'data'}->{'round'}]);
    while ($row = $game->fetch(PDO::FETCH_ASSOC)) {
        array_push($games, $row);
    }
    return $games;
  }
  public function getOrgJGames($id) {
    $results = [];
    $curr_date=null;
    $stmt = $this->db->prepare("select * from game where organiser=? order by start desc");
    $stmt->execute([ $id ]);
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $teams = array('home_team'=>$row['home_team'], 'away_team'=>$row['away_team'], 
                       'round'    =>$row['round'],     'game_id'  =>$row['id'] );
      if (is_null($curr_date)) {        // 1st time
        $curr_date = $row['start'];
        $teamlist=[];
        array_push($teamlist, $teams );
        $game = $row;
      } else if ($curr_date == $row['start']) {
        array_push($teamlist, $teams );
      } else {
        $game['games'] = $teamlist;
        array_push($results, $game);

        $curr_date = $row['start'];
        $teamlist=[];
        array_push($teamlist, $teams );
        $game = $row;
      }
      $count ++;
    };
    if ($count > 0) { 
      $game['games'] = $teamlist;
      array_push($results, $game);
    };
    return $results;
  }
  public function getDayGames($keys) {
    $sql  = "Select organiser, week(start) as weekno, start, home_team, away_team, round";
    $sql .= ", game_winner, home_score, away_score, status, id ";
    $sql .= "from game where organiser=? and start=? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $keys[0], $keys[1] ]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$games) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $games;
  }
  // remove name later
  public function insertGame($json) { 
    $sql = "insert into game(organiser,venue, start, home_team, away_team,  round, status, created)";
    $sql .= " values (?,?,?,?,?,?, 'pending', now())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'organiser'}
                    ,$json->{'data'}->{'venue'} ,$json->{'data'}->{'start'}  
                    ,$json->{'data'}->{'home_team'}, $json->{'data'}->{'away_team'} 
                    ,$json->{'data'}->{'round'} 
                   ]);
  } 
  public function deleteGame($id) {
    $stmt = $this->db->prepare("delete from game where id=?");
    $stmt->execute([ $id ]);
  }
  public function updateWinnerTicketGame($json) {
    if ($json->{'data'}->{'home_score'} == "") { $winner = "";
    } else {
      $winner = ( $json->{'data'}->{'home_score'} > $json->{'data'}->{'away_score'} ) 
               ?  $json->{'data'}->{'home_team'} 
               :  $json->{'data'}->{'away_team'}; 
      $sql = "update ticket_games set game_winner=? ";
      $sql .= ",bet_score = if(bet_team = ?, 1, 0 ) ";
      $sql .= " where game_id = ?";
      $tgames = $this->db->prepare($sql);
      $tgames->execute([ $winner, $winner, $json->{'data'}->{'id'} ]);
    };
    return $winner;
  }
  public function updateGame($json) {        // object, not array - mangames.js
      $winner = $this->updateWinnerTicketGame($json);
      $sql = "update game set organiser=?, venue=?, start=?";
      $sql .= ",home_team=?, away_team=?, home_score=?, away_score=?,home_odd=?, away_odd=?"; 
      $sql .= ",round=?, status=?, game_winner=? where id=?";
      $game = $this->db->prepare($sql);
      $game->execute([ $json->{'data'}->{'organiser'}
            ,$json->{'data'}->{'venue'},$json->{'data'}->{'start'}
            ,$json->{'data'}->{'home_team'}  ,$json->{'data'}->{'away_team'}  
            ,$json->{'data'}->{'home_score'} ,$json->{'data'}->{'away_score'}       
            ,$json->{'data'}->{'home_odd'} ,$json->{'data'}->{'away_odd'}  
            ,$json->{'data'}->{'round'}    
            ,$json->{'data'}->{'status'}, $winner    
            ,$json->{'data'}->{'id'} ]);
  }
  public function updateGameResult($json) {        // result - gameresults.js
      $winner = $this->updateWinnerTicketGame($json);

      $sql = "update game set status=?, home_score=?, away_score=?"; 
      $sql .= " ,home_team=?, away_team=? ";
      $sql .= " ,game_winner=? ";
      $sql .= " where id=?";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ 'closed'  ,$json->{'data'}->{'home_score'}
                          ,$json->{'data'}->{'away_score'}
                          ,$json->{'data'}->{'home_team'},$json->{'data'}->{'away_team'}
                          ,$winner
                          ,$json->{'data'}->{'id'} ]);  
  }
  //==================================================================
  // end date is not used-redundant
  public function fcgetGames($organiser=NULL) {
      $results=[];
      $sql = "select * from game ";
      $sql .= ($organiser !== NULL) ? " where organiser=?" : "";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $organiser ]);    
      $i=0;
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[$i++] = [ "start" => $row['start'], "end"   => $row['end']
                          ,"title" => $row['organiser'].",".$row['home_team'].",".$row['away_team']
                          ,"color" => $row['cssClass'],"id" => $row['id']
                         ];
      };
      return $results;
  }
  public function fcinsertGame($POST) { 
      $gamecomponents = explode(",", $POST['title']);

      $sql = "insert into game(start, end, title, organiser, home_team, away_team) values(?,?,?,?,?,?)";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $POST['start'], $POST['end'], $POST['title'],
        $gamecomponents[0], $gamecomponents[1], $gamecomponents[2]
        ]);
  }
  public function fcupdateGame($POST) {        // object, not array
      $gamecomponents = explode(",", $POST['title']);

      $sql = "update game set start=?, end=?, title=?, organiser=?, home_team=?, away_team=? where id=?";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $POST['start'] ,$POST['end'],$POST['title'], 
          $gamecomponents[0], $gamecomponents[1], $gamecomponents[2], $POST['id'] ]);
  } 
  //======================================
  public function getOrgPeriod($organiser) {
    $sql  = "Select period, color from organiser where organiser=? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $organiser ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows[0];
  }
  public function csvinsertGames($organiser) {
    $filename='../api/'.$organiser.'.csv';
    $orgDetails = $this->getOrgPeriod($organiser);
    $period_desc = $orgDetails['period'];
    $color       = $orgDetails['color'];

    $select  = "Select organiser, home_team, away_team, start, id ";
    $select .= " from game where organiser=? and home_team=? and away_team=? and start=? ";

    $insert = "insert into game(organiser, home_team, away_team, home_score, away_score, game_winner";
    $insert .= ",round, start,title, cssClass, venue, status, remarks )";
    $insert .= " values(?,?,?,?,?,?,?,?,?,?,?,'pending','csv insert')";

    $update = "update game set home_score=?, away_score=?, game_winner=? where id=?";

    $select_stmt = $this->db->prepare($select);
    $istmt = $this->db->prepare($insert);
    $ustmt = $this->db->prepare($update);

    $file = fopen($filename, "r");
    $lineno=1;
    while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
      if ($lineno<>1) {
        $obj['round']    =$row[0];    $obj['date']     = $row[1];
        $obj['venue']    =$row[2];    $obj['home_team']=$row[3];
        $obj['away_team']=$row[4];    $obj['result']   =$row[5];
        //================================================
        $round=$period_desc . ' ' . $obj['round'];
        $title=$obj['home_team'].",".$obj['away_team'].':'.$organiser;
        //------------------------------
        if ($obj['result'] <> "") {
          $scores = explode('-',$obj['result']);
          $winner = ( $scores[0] > $scores[1] ) ?  $obj['home_team'] :  $obj['away_team']; 
        } else {
          $scores = array_fill(0,2,0);  // start_index, num, value
          $winner = "";
        };
        //----------------------------------------
        $date  = date_create_from_format('d/m/Y H:i', $obj['date']);
        $tsdate= $date->getTimestamp();
        $start = date("Y/m/d", $tsdate);
        //=============================================================
        $select_stmt->execute([ $organiser, $obj['home_team'], $obj['away_team'], $start ]);
        $rows = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$rows) {
          $istmt->execute([ $organiser, $obj['home_team'], $obj['away_team']
                          ,$scores[0], $scores[1], $winner, $round, $start, $title, $color
                          ,$obj['venue']  ]);
        } else {
          $ustmt->execute([ $scores[0], $scores[1], $winner, $rows[0]['id'] ]);
        };
        //------------------------------------------------
      };
      $lineno++;
    };
    fclose($file);
  }  // end of csvInsert
}    // end of Class
