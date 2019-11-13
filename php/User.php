<?php
// require_once('configLog.php');
require __DIR__.'/DBclass.php';
require("../PHPMailer-master/src/PHPMailer.php");
require("../PHPMailer-master/src/SMTP.php");
class User {
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
    
  public function getUsers() {
    $stmt = $this->db->prepare("select * from users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$users) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $users;
  }
  public function registerUser($json) {
    $pass1 = $json->{'data'}->{'password'}; 
    $hashed = hash('sha256', $pass1);
    $sql = "insert into users(username,password,email, firstname, lastname, role";
    $sql .= ",address1, address2, town, postcode,country, bankbsb, bankaccount";
    $sql .= ", resetpassword, created) values(?,?,?,?,?,?,?,?,?,?,?,?,?, 0,now())";
    $stmt = $this->db->prepare($sql);    // no need to resetpassword
    $stmt->execute([ 
         $json->{'data'}->{'username'}   ,$hashed
        ,$json->{'data'}->{'email'}
        ,$json->{'data'}->{'firstname'} ,$json->{'data'}->{'lastname'}  ,$json->{'data'}->{'role'}
            ,$json->{'data'}->{'address1'}  ,$json->{'data'}->{'address2'}  ,$json->{'data'}->{'town'}
            ,$json->{'data'}->{'postcode'}  ,$json->{'data'}->{'country'}   ,$json->{'data'}->{'bankbsb'}
            ,$json->{'data'}->{'bankaccount'} ]);
        return $result;    // ok,  problem=-1
  }
  public function insertUser($json) {      // user maintenance (no password)
    $sql = "insert into users(username,email, firstname, lastname, role, address1, address2";
    $sql = ", town, postcode";
    $sql .= ",country, bankbsb, bankaccount, created) values(?,?,?,?,?,?,?,?,?,?,?,?,?,now())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ 
         $json->{'data'}->{'username'} 
        ,$json->{'data'}->{'email'}
        ,$json->{'data'}->{'firstname'} ,$json->{'data'}->{'lastname'}  ,$json->{'data'}->{'role'}
        ,$json->{'data'}->{'address1'}  ,$json->{'data'}->{'address2'}  ,$json->{'data'}->{'town'}
        ,$json->{'data'}->{'postcode'}  ,$json->{'data'}->{'country'}   ,$json->{'data'}->{'bankbsb'}
        ,$json->{'data'}->{'bankaccount'} ]);
        return $result;    // ok,  problem=-1
  }
  public function deleteUser($id) {
    $stmt = $this->db->prepare("delete from users where id=?");
    $stmt->execute([ $id ]);
    return $result;
  }
  public function updateUser($json) {        // object, not array
    $sql = "update users set username=?, email=?, firstname=?,lastname=?,role=?, ";
    $sql .= " address1=?, address2=?, town=?, postcode=?, country=?, bankbsb=?, bankaccount=? where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ 
       $json->{'data'}->{'username'}   ,$json->{'data'}->{'email'}
      ,$json->{'data'}->{'firstname'} ,$json->{'data'}->{'lastname'}  ,$json->{'data'}->{'role'}
      ,$json->{'data'}->{'address1'}  ,$json->{'data'}->{'address2'}  ,$json->{'data'}->{'town'}
      ,$json->{'data'}->{'postcode'}  ,$json->{'data'}->{'country'}   ,$json->{'data'}->{'bankbsb'}
      ,$json->{'data'}->{'bankaccount'},$json->{'data'}->{'id'} ]);
    return $result;
  }
  public function getUser($uom, $key) {
    $sql = "select * from users";
    if     ($uom === 'email')    { $sql .= " where email=?"; }
    elseif ($uom === 'username') { $sql .= " where username=?"; };
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $key ]);      
    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$user) { 
      $this->msg = 'No rows'; 
      exit;
    };
    return $user;
  }
  public function getUserByName($username) {
    $stmt = $this->db->prepare("select * from users where username=?");
    $stmt->execute([ $username ]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$users) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $users;
  }
  public function getOrgWeeks($json) {
    $results=[];
    $sql = "select round, start, end_dt from period ";
    $sql .= " where organiser=? and ? between start and end_dt ";
    $stmt = $this->db->prepare($sql);

    $organisers = $json->{'data'}->{'organisers'};  
    $today      = $json->{'data'}->{'today'};

    foreach($organisers as $org) {
      $stmt->execute([ $org, $today ]);   
      $period = $stmt->fetchAll(PDO::FETCH_ASSOC); 
      if($period) {
        $orgweek = $org.":".$period[0]['round'];
        array_push($results, $orgweek);
      };
    };
    $orgweekstr = implode("','", $results);
    return $orgweekstr;
  }
  public function getUserGameSummary($json) {          // organiser, today
    $orgweekstr = $this->getOrgWeeks($json);

    $organisers = $json->{'data'}->{'organisers'};  
    $today      = $json->{'data'}->{'today'};

    $sql =  "select username ";
    $sql .= ", concat(organiser,':',round) as orgweek";
    $sql .= ", pool_id, count(*) as game_entries "; 
    $sql .= " from ticket_games ";
    $sql .= " where concat(organiser,':',round) in ('".$orgweekstr."')";    
    $sql .= " group by username, concat(organiser,':',round), pool_id ";
    $sql .= " order by username, concat(organiser,':',round), pool_id ";    
//    $logger->info('5) getUserGameSummary', array('sql' => ['sql'=>$sql]) );
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
//  $logger->info('6) getUserGameSummary', array('results' => $results) );    
    return $results;
  }
  public function changePassword($json) {        // object, not array
    $pass1 = $json->{'data'}->{'password'}; 
    $hashed = hash('sha256', $pass1);
    $sql = "update users set password=?, resetpassword=0 where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $hashed
                    ,$json->{'data'}->{'id'} ]);
  }  
  public function sendMail($json) {   
    //$logger = getLogger();
    //$logger->info('1) sendMail', array('json' => $json));
    $recipent=$json->{'data'}->{'email'};
    $newpass =$json->{'data'}->{'newpassword'};   
    $uid     =$json->{'data'}->{'uid'};     
    //--2) change to temporary password ------------------------------------------
    $hashed = hash('sha256', $newpass);
    $sql = "update users set password=?, resetpassword=0 where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $hashed, $uid ]);
    //---3) send email with temporary password ------------------------------
    $mail = new PHPMailer\PHPMailer\PHPMailer();  
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465; // or 587
    $mail->IsHTML(true);
    $mail->Username="thtan56@gmail.com";
    $mail->Password='cancer1956';
    $mail->SetFrom('t.h.tan@dunelm.org.uk');
    $mail->Subject = "new temporary password";
    //----------------- 
    $mail->Body="Your temporary password is ".$newpass;
    $mail->AddAddress($recipent); 
    $mail->Send();
  } 
}