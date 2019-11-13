<?php
// require_once('configLog.php');
require __DIR__.'/DBclass.php';

class Price {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
  public function getPrices($ticker=NULL, $period='daily') {
    switch($period){
      case "daily":   $from = " from daily_prices  "; break; 
      case "weekly":  $from = " from weekly_prices "; break;     
      case "monthly": $from = " from monthly_prices "; break;  
    };
    $sql = "select ticker, date,  open, close, low, high, volume ";
    $sql .= $from ;
    $sql .= ' WHERE ticker = ? ';    
    $sql .= " order by ticker, date asc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $ticker ]);  
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$results) { $this->msg = 'No rows'; };
    return $results;
  }
  public function getGroupPrices($tickers=NULL) {
    //  $logger = getLogger();    
    $sql = "select ticker, date,  open, close, low, high, volume from daily_prices ";
    $sql .= ' WHERE ticker IN ("' . implode('","', $tickers) . '")';    
    $sql .= " order by ticker, date asc";
    //  $logger->info('1) Price.php', array('sql' => ['sql'=>$sql]));       
    $stmt = $this->db->query($sql);
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //  $logger->info('2) Price.php', array('arr' => $arr ));      
    if(!$arr) { $this->msg = 'No rows'; };
    return $arr;
  }
  public function getPeriodPrices($period, $uom=NULL, $id=NULL) {
    switch($period){
      case "weekly":  $from = " from weekly_prices p "; break;     
      case "monthly": $from = " from monthly_prices p "; break;  
      case "qtrly":   $from = " from qtrly_prices p "; break;     
      case "yearly":  $from = " from yearly_prices p "; break; 
    };
    $where = " ";
    if($uom <> NULL) { $where = " where ".$uom. "=? "; };   // favorite, sector, indice or investor
    $sql = "select p.ticker, p.date, p.open, p.high, p.low, p.close, p.volume, p.treturn ";
    $sql .= ", s.favorite, s.sector, s.indice, s.investor  ";   // computed
    $sql .= $from ;
    $sql .= " inner join stocks s on p.ticker=s.ticker ";    
    $sql .= $where ;         // favorite=F9 | sector='AUTO', etc
    $sql .= " order by p.date, p.ticker";
    // $logger->info('1) Stock.php', array('sql' => ['sql'=>$sql]));    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);  
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$prices) { $this->msg = 'No rows'; };
    return $prices;
  }
}    // end of Class
