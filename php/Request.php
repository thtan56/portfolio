<?php
require __DIR__.'/DBclass.php';
//require __DIR__.'/configLog.php';   // no need if already in apiRequest.php

class Request {
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
    //----- A) maintainenace --------------------
  public function getAllRequests() {
    $sql = "select username, activity, description, reference_number ";
    $sql .= ",exchange_rate, cash, vcash, status, created, id ";
    $sql .= " from request ";  
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$requests) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $requests;
  }
  public function deleteRequest($id) {
    $stmt = $this->db->prepare("delete from request where id=?");
    $stmt->execute([ $id ]);
  }
  public function updateRequest($json) {        // object, not array
       $sql  = "update request set username=?, activity=?";
       $sql .= ",description=?, reference_number=?, exchange_rate=?, cash=?";
       $sql .= ",vcash=? where id=?";
       $stmt = $this->db->prepare($sql);
       $stmt->execute([ $json->{'data'}->{'username'} ,$json->{'data'}->{'activity'} 
            ,$json->{'data'}->{'description'}      ,$json->{'data'}->{'reference_number'}
            ,$json->{'data'}->{'exchange_rate'}      ,$json->{'data'}->{'cash'}  
            ,$json->{'data'}->{'vcash'} 
            ,$json->{'data'}->{'id'} ]);
  }
  public function insertRequest($json) { 
    $sql = "insert into request(username, activity, description, reference_number ";
    $sql .= ",exchange_rate, cash, vcash, created) ";
    $sql .= " values (?,?,?,?,?,?,?,now())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'username'} ,$json->{'data'}->{'activity'} 
            ,$json->{'data'}->{'description'}      ,$json->{'data'}->{'reference_number'}
            ,$json->{'data'}->{'exchange_rate'}      ,$json->{'data'}->{'cash'}  
            ,$json->{'data'}->{'vcash'} ]);
  }
  //====== B) transactions ============================================================================
  public function getUserRequests($json) {
//    $logger = getLogger();
//    $logger->info('11) getUserRequests', array('$json' => $json));
    $activity = $json->{'data'}->{'activity'};
    switch($activity){
      case "withdraw": $action = "and activity='withdraw cash'"; break;
      case "deposit":  $action = "and activity='deposit cash'"; break;
      case "buy":      $action = "and activity='buy vcash'"; break;
      case "sell":     $action = "and activity='sell vcash'"; break;
      default:         $action = ""; 
    };
    $sql = "select username, activity, description, reference_number ";
    $sql .= ",exchange_rate, cash, vcash, status, created, id ";
    $sql .= " from request ";  
    $sql .= " where username=? ".$action; 
      
//    $logger->info('12) getUserRequests', array('sql' => ['sql'=>$sql]) );
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'username'} ]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$requests) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $requests;
  }

  public function deleteUpdate($json) {
    $stmt = $this->db->prepare("delete from request where id=?");
    $stmt->execute([ $json->{'data'}->{'id'} ]);
    //-- 2) ----------------------------------------------------------------------
    // update users - cash & vcash
    $balances  = $this->getReverseBalances($json);
    $sql = "update users set cash=?,vcash=? where username=?";
    $user = $this->db->prepare($sql);
    $user->execute([ $balances['cbal'], $balances['vbal'], $json->{'data'}->{'username'} ]);    
  }
  public function insertRequest2($json) {             
    $activity = $json->{'data'}->{'activity'};
    $amount   = $json->{'data'}->{'amount'};
    $rate     = $json->{'data'}->{'exchange_rate'};
    $cbal     = $json->{'data'}->{'balcash'};
    $vbal     = $json->{'data'}->{'balvcash'};
    $details  = $this->getAmt_Balances($activity, $amount, $rate, $cbal, $vbal);
    //--1) ---------------------    
    $sql = "insert into request(username, activity, description  ";
    $sql .= ",exchange_rate, cash, vcash, status, created) ";
    $sql .= " values (?,?,?,?,?,?, 'pending', now())";
    $request = $this->db->prepare($sql);
    $request->execute([ $json->{'data'}->{'username'} ,$details['activity']
            ,$details['description'], $json->{'data'}->{'exchange_rate'}
            ,$details['cash'], $details['vcash']  ]);    // 1) derived from amount
    //-- 2) ----------------------------------------------------------------------
    // update users - cash & vcash
    $sql = "update users set cash=?,vcash=? where username=?";
    $user = $this->db->prepare($sql);
    $user->execute([ $details['cbal'], $details['vbal'], $json->{'data'}->{'username'} ]);  // 2) bal
    return $details;    // return new cash,vcash balance
  }
  //================================================================
  public function getReverseBalances($json) {
    $cbal     = $json->{'data'}->{'balcash'};
    $vbal     = $json->{'data'}->{'balvcash'};
    $cash     = $json->{'data'}->{'cash'};
    $vcash    = $json->{'data'}->{'vcash'};
    switch( $json->{'data'}->{'activity'} ) {
      case "withdraw cash": $cbal = $cbal - $cash; break;  // (-ve)
      case "deposit cash":  $cbal = $cbal - $cash; break;
      case "buy vcash":     $vbal = $vbal - $vcash;  // reduce   (+ve)
                            $cbal = $cbal - $cash;   // increase (-ve)
                            break;
      case "sell vcash":    $vbal = $vbal - $vcash;  // reduce   (+ve)
                            $cbal = $cbal - $cash;   // increase (-ve)
                            break;
    };
    return  array("cbal" => $cbal, "vbal" => $vbal);
  }
  public function getAmt_Balances($activity, $amount, $rate, $cbal, $vbal) {
    switch($activity){
      case "withdraw": 
        $array = array( "activity"=>"withdraw cash", "description" => "transfer to bank"
                        ,"cash" => -1 * $amount, "vcash" => 0
                        ,"cbal" => $cbal - $amount, "vbal" => $vbal); break;
      case "deposit":  
        $array = array( "activity" =>"deposit cash", "description" => "deposit to trust account"
                        ,"cash" => $amount, "vcash" => 0
                        ,"cbal" => $cbal + $amount, "vbal" => $vbal); break;
      case "buy":
        $vcash = $amount * $rate;      
        $array = array( "activity" =>"buy vcash", "description"=> "transfer to virtual bank"
                        ,"cash" => -1 * $amount, "vcash" => $vcash
                        ,"cbal" => $cbal - $amount, "vbal" => $vbal + $vcash ); break;
      case "sell":
        $cash = $amount / $rate;
        $array = array( "activity" =>"sell vcash", "description" => "transfer to trust account"
                        ,"cash" => $cash, "vcash" => -1 * $amount
                        ,"cbal" => $cbal + $cash, "vbal" => $vbal - $amount); break;
    }
    return $array;
  }
}
