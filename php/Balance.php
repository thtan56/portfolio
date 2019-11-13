<?php
require __DIR__.'/DBclass.php';

class Balance {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
    
  public function getBalances() {
    $results=[];
    $sql = "select today, nav, cash, bank, adjustment, profit, total1, total2 from my_daily_profit ";   
    $stmt = $this->db->prepare($sql);
    $stmt->execute();   
    $results['daily'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //
    $sql = "select j.lastdate as today, a.nav, a.cash, a.bank, a.adjustment, a.profit, a.total1, a.total2 ";
    $sql .= " from my_daily_profit a ";    
    $sql .= " inner join ( ";
    $sql .= "   select max(today) lastdate from daily_balance ";
    $sql .= "   group by yearweek(today) ) j on a.today = j.lastdate ";    
    $stmt = $this->db->prepare($sql);
    $stmt->execute();   
    $results['weekend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //
    $sql = "select j.lastdate as today, a.nav, a.cash, a.bank, a.adjustment, a.profit, a.total1, a.total2 ";
    $sql .= " from my_daily_profit a ";    
    $sql .= " inner join ( ";
    $sql .= "   select max(today) lastdate from daily_balance ";
    $sql .= "   group by EXTRACT(YEAR_MONTH From today) ) j on a.today = j.lastdate ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();       
    $results['monthend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results;
  }
}    // end of Class
