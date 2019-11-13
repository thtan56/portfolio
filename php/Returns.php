<?php
require_once('configLog.php');
require __DIR__.'/DBclass.php';

class Returns {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
  public function getReturns($ticker=NULL, $period=NULL) {
    switch($period){
      case "daily":  $source = " daily_returns "; break;     
      case "weekly": $source = " weekly_returns "; break;    
    };
    $sql = "select ticker, date,  open, close, low, high, volume from ".$source;
    $sql .= ' WHERE ticker = ? ';    
    $sql .= " order by ticker, date asc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $ticker ]);  
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$results) { $this->msg = 'No rows'; exit; };
    return $results;
  }
  public function getTickers($uom=NULL, $gid='F9') {
    switch($uom){
      case "favorite": $where = " where favorite=?"; break;     
      case "sector":   $where = " where sector=?"; break;  
      case "indice":   $where = " where indice=?"; break;     
      case "investor":   $where = " where investor=?"; break;   
      default: $where = " ";      
    };
    $sql = "select ticker from stocks ". $where ." order by ticker desc";           
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $gid ]);  
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$results) { $this->msg = 'No rows'; exit; };
    return $results;
  }
  public function getGroupReturns($uom=NULL, $gid='F9', $period=NULL) {
    switch($period){
      case "daily":  $source = " daily_returns "; break;     
      case "weekly": $source = " weekly_returns "; break;    
      case "monthly": $source = " monthly_returns "; break;   
      case "qtrly":   $source = " qtrly_returns "; break;   
    };   
    // $logger = getLogger();
    $results=[];
    $tickers=Returns::getTickers($uom, $gid);
    foreach ($tickers as $ticker) {     
      $tid=$ticker['ticker'];
      $sql = "select ticker, yesterday, today, period, price_prev, price, treturn, zscore from ".$source;
      $sql .= ' WHERE ticker = "'. $tid . '"';    
      $sql .= " order by ticker, today asc";
      // $logger->info('14) getGroupReturns', array('sql2' => ['sql'=>$sql]));        
      $stmt = $this->db->query($sql);
      if ($arr = $stmt->fetchAll(PDO::FETCH_ASSOC) ) {   
        array_push($results, $arr);                       // for each series/ticker
      }
      //while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      //  array_push($results,$row);
      // }
    };
    // $logger->info('6) getGroupReturns', array('tickers' => $results));
    return $results;
  }  
}    // end of Class
