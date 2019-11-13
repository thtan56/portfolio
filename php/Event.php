<?php
require __DIR__.'/DBclass.php';
//require_once('configLog.php');

class Event {
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

    public function getEvents($json) {
      $current = $json->{'data'}->{'current'}; 
      $organiser = $json->{'data'}->{'organiser'}; 
//      $logger = getLogger();
//      $logger->info('1) event.php', array('current' => $current));     // 2018-11-01
      $date  = date_create_from_format('Y-m-d', $current);
      $tsdate= $date->getTimestamp();
      $first = date('Y-m-01', $tsdate);    // 2nd param: timestamp
      $last = date('Y-m-t', $tsdate);
      $sql = "select * from game where organiser = ? and start between ? and ? ";  
      $results=[];
      $stmt = $this->db->prepare($sql);      // old=event
      $stmt->execute([ $organiser, $first, $last ]);
      $i=0;
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//        $namelist = $this->getShortNames($row);
//                          ,"title" => $namelist.':'.$row['title']
        $results[$i++] = 
            [ "start"    => $row['start']       ,"end"   => $row['end']
            ,"title"     => $row['title']       ,"cssClass" => $row['cssClass']
            ,"organiser" => $row['organiser']   ,"home_team" => $row['home_team']
            ,"away_team" => $row['away_team']   ,"remarks" => $row['remarks']
            ,"id" => $row['id']
            ];
      };
      return $results;
    }
    public function getOrgEvents($organiser) {
      $results=[];
      $sql  = "Select organiser, weekofyear(start) as weekno, start";     // end date is redundant (match only on a specific)
      $sql .= ",home_team, away_team ";                                 // home_odd, away_odd, home_score, away_score   to be considered ??
      $sql .= ",round, venue, game_winner, status, id ";                // round or week : can be look-up on period table using start date
      $sql .= "from game where organiser=? ";
      $sql .= "order by date desc ";  
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
    public function getShortNames($event) {
      $results=$event['organiser'].":";
      $team = $this->db->prepare("select shortname from team where organiser=? and name=?");      
      $team->execute([ $event['organiser'], $event['home_team'] ]);      
      while ($row = $team->fetch(PDO::FETCH_ASSOC)) { 
        $results.=$row['shortname']; 
      };  
      $results.=":";
      $team->execute([ $event['organiser'], $event['away_team'] ]);      
      while ($row = $team->fetch(PDO::FETCH_ASSOC)) { $results.=$row['shortname']; };
      return $results;
    }
    public function insertEvent($json) { 
      $sql = "insert into event(start, end, title, cssClass, organiser, home_team, away_team) values(?,?,?,?,?,?,?)";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'start'}  ,$json->{'data'}->{'end'}  ,$json->{'data'}->{'title'}
                ,$json->{'data'}->{'cssClass'} ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'home_team'}
                ,$json->{'data'}->{'away_team'} ]);
    }

    public function deleteEvent($id) {
        $stmt = $this->db->prepare("delete from event where id=?");
        $stmt->execute([ $id ]);
    }
    public function updateEvent($json) {        // object, not array
        $sql = "update event set start=?, end=?, title=?, cssClass=?, organiser=?, home_team=?, away_team=?";
        $sql .= " , remarks=? where id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'start'}   ,$json->{'data'}->{'end'}  
                ,$json->{'data'}->{'title'}     ,$json->{'data'}->{'cssClass'}
                ,$json->{'data'}->{'organiser'} ,$json->{'data'}->{'home_team'}  ,$json->{'data'}->{'away_team'}
                ,$json->{'data'}->{'remarks'} ,$json->{'data'}->{'id'} ]);
    }
    //=================================================== jquery version ==================
    public function getEvents2() {
      $results=[];
      $stmt = $this->db->prepare("select * from event");
      $stmt->execute();
      $i=0;
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[$i++] = [ "start" => $row['start'] 
                          ,"end"   => $row['end']
                          ,"title" => $row['title']
                          ,"color" => $row['cssClass']
                          ,"id" => $row['id']
                         ];
      };
      return $results;
    }
    public function insertEvent2($POST) { 
      $gamecomponents = explode(",", $POST['title']);

      $sql = "insert into event(start, end, title, organiser, home_team, away_team) values(?,?,?,?,?,?)";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $POST['start'], $POST['end'], $POST['title'],
        $gamecomponents[0], $gamecomponents[1], $gamecomponents[2]
        ]);
    }
    public function updateEvent2($POST) {        // object, not array
      $gamecomponents = explode(",", $POST['title']);

      $sql = "update event set start=?, end=?, title=?, organiser=?, home_team=?, away_team=? where id=?";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $POST['start'] ,$POST['end'],$POST['title'], 
          $gamecomponents[0], $gamecomponents[1], $gamecomponents[2], $POST['id'] ]);
    }    
//        $names = array("shortname1" => $row['shortname1'], "shortname2" => $row['shortname2'],
//                       "description" => "hello tan" );
//        $json = json_encode($names);     
}