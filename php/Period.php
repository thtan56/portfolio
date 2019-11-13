<?php
require __DIR__.'/DBclass.php';
// require_once('configLog.php');
class Period {
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
  // public function getPeriods() {
  //  $sql = "select * from period ";
  //  $stmt = $this->db->prepare($sql);
  //  $stmt->execute();    
  //  $arr = $stmt->fetchAll(PDO::FETCH_ASSOC); 
  //  return $arr;
  // }  
  public function getRounds($organiser=NULL) {
    $sql = "select round from period ";
    $sql .= ($organiser !== NULL) ? " where organiser=?" : "";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $organiser ]);   
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    return $arr;
  }
  public function getPeriods($organiser=NULL) {
    $sql = "select * from period ";
    $sql .= ($organiser !== NULL) ? " where organiser=?" : "";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $organiser ]);   
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    return $arr;
  }
  public function getOrgWeekPeriod($json) {
    $sql = "select organiser, round, start, end_dt from period ";
    $sql .= " where organiser=? and round=? ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'organiser'}, $json->{'data'}->{'round'}  ]);   
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    return $arr;
  }  
  public function getOrgCurrentRound($json) {
//    $logger = getLogger();
//    $logger->info('1) Period.php>getOrgCurrentRound', array('json' => $json));    
    $sql = "select concat(organiser,':',round) as orgweek, round, start, end_dt from period ";
    $sql .= " where organiser=? and ? between start and end_dt ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'organiser'}, $json->{'data'}->{'today'}  ]);   
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC); 
//    $logger->info('10) getOrgCurrentRound>arr', array('arr' => $arr)); 
    return $arr;
  }
  public function insertPeriod($POST) {
    $periodcomponents = explode(",", $POST['title']);

    $color = $this->getColor($periodcomponents[1]);     // week or round
    $sql = "insert into period(start, end_dt, title, color, organiser, round) values(?,?,?,?,?,?)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $POST['start'], $POST['end_dt'], $POST['title'], $color
                    ,$periodcomponents[0], $periodcomponents[1]    
                  ]);
  }
  public function deletePeriod($id) {
    $stmt = $this->db->prepare("delete from period where id=?");
    $stmt->execute([ $id ]);
  }
  public function updatePeriod($POST) {        // object, not array
    $periodcomponents = explode(",", $POST['title']);
    $color = $this->getColor($periodcomponents[1]);     // week or round

    $sql = "update period set start=?, end_dt=?, title=?, color=?";
    $sql .= ",organiser=?, round=? where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $POST['start'], $POST['end_dt'], $POST['title'], $color
          ,$periodcomponents[0], $periodcomponents[1], $POST['id'] ]);
  }
  public function insertjPeriod($json) {
    $color = $this->getColor($json->{'data'}->{'title'});
    $sql = "insert into period(title, start, end_dt, color, organiser, round, remarks)";
    $sql .= " values(?,?,?,?,?,?,?)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ 
      $json->{'data'}->{'title'} ,$json->{'data'}->{'start'}, $json->{'data'}->{'end_dt'}
      ,$color  ,$json->{'data'}->{'organiser'}, $json->{'data'}->{'round'}
      ,$json->{'data'}->{'remarks'}    ]);
  }
  public function updatejPeriod($json) {        // object, not array
    $sql  = "update period set title=?, start=?, end_dt=?, color=? ";
    $sql .= ",organiser=?, round=?, remarks=? where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ 
          $json->{'data'}->{'title'}      ,$json->{'data'}->{'start'} 
         ,$json->{'data'}->{'end_dt'}        ,$json->{'data'}->{'color'}  
         ,$json->{'data'}->{'organiser'}  ,$json->{'data'}->{'round'}   
         ,$json->{'data'}->{'remarks'}    ,$json->{'data'}->{'id'} ]);
  }
  public function autoGenPeriod($json) {
    $sql = "insert into period(title, start, end_dt, organiser, round, remarks)";
    $sql .= " values(?,?,?,?,?, 'autogen')";
    $stmt = $this->db->prepare($sql);

    $last=intval($json->{'data'}->{'last'});
    $start=$json->{'data'}->{'start'}; 
    for ($i=1; $i <= $last; $i++) {
      $organiser=$json->{'data'}->{'organiser'};
      $round=$json->{'data'}->{'period'}.' '.$i;
      $date = strtotime($start);              // in numeric format
      $ut6 = strtotime("+6 day", $date);
      $stmt->execute([ $organiser.",".$round, $start, date("Y-m-d", $ut6),$organiser, $round ]);
      $utStart=strtotime("+7 day", $date);    // in numeric format
      $start = date("Y-m-d", $utStart);
    } 
  }
  public function getColor($title) {
    $title = strtolower($title);
    if       (strpos($title, 'round') !== false) { $color="blue";
    } elseif (strpos($title, 'week')  !== false) { $color="green";
    } else                                       { $color="red"; }
    return $color;
  }
  public function getOrgsCurrentRound($json) { 
    $sql = "select organiser, round, start, end_dt from period ";
    $sql .= " where organiser=? and ? between start and end_dt ";
    $period = $this->db->prepare($sql);
    $organisers = $json->{'data'}->{'organisers'};
    $today      = $json->{'data'}->{'today'};
    $results=[];
    foreach ($organisers as $org) {  
      $period->execute([ $org, $today ]);
      while ($row = $period->fetch(PDO::FETCH_ASSOC)) {   
        array_push($results, $row);
      }
    }   
    return $results;
  }  
  public function getOrgsWeeks($json) {
    // $logger = getLogger();
    // $logger->info('1) Period.php>getOrgsWeeks', array('json' => $json));    
    $sql = "select organiser, round, start, end_dt ";
    $sql .= ",concat(organiser,':',round) as orgweek from period ";   
    $sql .= " where organiser=? and ? between start and end_dt ";
    $period = $this->db->prepare($sql);
    // --------------------------------------------
    // stage 1: generate array of orgweeks (preparation)
    $organisers = $json->{'data'}->{'organisers'};
    $today      = $json->{'data'}->{'today'};
    $orgweeks=[];
    foreach ($organisers as $org) {  
      $period->execute([ $org, $today ]);
      while ($row = $period->fetch(PDO::FETCH_ASSOC)) {   
        array_push($orgweeks, $row['orgweek']);       
      };
    };
    //-----------------------------------------------------
    // stage 2: get orgs week - pools info (start)
    $results=[];
    $sql2 = "select concat(organiser,':',round) as orgweek, id as pool_id from pool ";
    $sql2 .= " where concat(organiser,':',round) in ('";
    $sql2 .= implode("','", $orgweeks)."') ";
    $stmt = $this->db->prepare($sql2);
    $stmt->execute();   
    $results['pools'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //-----------------------------------------------------
     // stage 3: get orgs week - tickets info (next)   
    $sql2 = "select concat(organiser,':',round) as orgweek, id as ticket_id from ticket ";
    $sql2 .= " where concat(organiser,':',round) in ('";
    $sql2 .= implode("','", $orgweeks)."') ";
    $stmt = $this->db->prepare($sql2);
    $stmt->execute();   
    $results['tickets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    //-----------------------------------------------------
    $sql2 = "select concat(organiser,':',round) as orgweek, id as game_id from game ";
    $sql2 .= " where concat(organiser,':',round) in ('";
    $sql2 .= implode("','", $orgweeks)."') ";
    $stmt = $this->db->prepare($sql2);
    $stmt->execute();   
    $results['games'] = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    //-----------------------------------------------------
    $sql2 = "select count(*) as usercount from users ";
    $stmt = $this->db->prepare($sql2);
    $stmt->execute();   
    $results['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);        
    //-----------------------------------------------------    
    return $results;
  }   
}
