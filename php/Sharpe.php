<?php
require __DIR__.'/DBclass.php';
//require_once('configLog.php');

class Sharpe {
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

  public function getSharpes($uom=NULL, $id=NULL) {
    switch($uom){
      case "favorite": $where = " where s.favorite=?"; break;     
      case "ticker": $where = " where a.ticker=?"; break;     
      default: $where = " ";      
    };
    $sql = "select a.id, a.ticker, a.ticker as name ";   // duplicate for scatter graph
    $sql .= ", a.retavg, a.retsd, a.sharpe";
    $sql .= ", a.maxdrawdown, a.romad, a.close, a.created, a.eps, a.pe, a.investor ";
    $sql .= " ,s.favorite, s.sector, s.indice, s.gradient, s.intercept, s.lastprice ";
    $sql .= " from sharpes a ";
    $sql .= " inner join stocks s on a.ticker=s.ticker ";      
    $sql .= $where ;       
    $sql .= " order by a.ticker desc";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);  
    $sharpes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$sharpes) { $this->msg = 'No rows'; 
      exit;
    };
    return $sharpes;
  }
  public function getTickerSharpes($ticker=NULL, $period=NULL) {
    switch($period){
      case "daily":  
        $sql = "select ticker, close, retavg, retsd, sharpe, maxdrawdown, romad from sharpes ";
        $sql .= " where ticker=? ";    
        break;     
      case "weekly": 
        $sql = "select ticker, period, close, retavg, retsd, sharpe, maxdrawdown, romad ";
        $sql .= " from sharpes_weekly "; // weekly_returns
        $sql .= " where ticker=? ";  
        break; 
      case "monthly": 
        $sql = "select ticker, period, close, retavg, retsd, sharpe, maxdrawdown, romad ";
        $sql .= " from sharpes_monthly "; 
        $sql .= " where ticker=? ";  
        break;               
    };
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $ticker ]);  
    $sharpes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$sharpes) { 
      $this->msg = 'No rows'; 
      exit;
    };
    return $sharpes;
  }  
  public function getReturns($uom=NULL, $id=NULL) {
    switch($uom){
      case "favorite": $where = " where s.favorite=?"; break;    
      case "ticker": $where = " where a.ticker=?"; break;
      default: $where = " ";      
    };
    $sql = "select a.id, a.ticker as ticker, a.period, a.today, a.price_prev, a.price";
    $sql .= ", a.dist, a.treturn ";
    $sql .= " ,s.favorite, s.sector, s.indice ";
    $sql .= " from daily_returns a ";
    $sql .= " inner join stocks s on a.ticker=s.ticker ";    
    $sql .= $where ;       
    $sql .= " order by a.ticker desc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);  
    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$returns) { $this->msg = 'No rows'; 
      exit;
    };
    return $returns;
  }  
  public function getSharpeHistory($uom=NULL, $id=NULL) {
    switch($uom){
      case "favorite": $where = " where s.favorite=?"; break; 
      case "sector": $where = " where s.sector=?"; break;               
      case "ticker": $where = " where a.ticker=?"; break;     
      default: $where = " ";      
    };
    $sql = "select s.ticker, s.favorite, s.sector, s.investor, s.gradient, s.intercept, s.lastprice ";
    $sql .= ", r.retavg, r.retsd, r.sharpe, r.maxdrawdown, r.close ";  

    $sql1 = $sql . ",r.eps, r.pe,'this_week' as period  from sharpes r ";
    $sql1 .= " inner join stocks s on r.ticker=s.ticker " . $where ;      

    $sql2 = $sql . ",'last_week' as period  from lw_sharpes r ";        // eps, pe not available
    $sql2 .= " inner join stocks s on r.ticker=s.ticker " . $where ; 

    $sql3 = $sql . ",'last_month' as period  from lm_sharpes r ";
    $sql3 .= " inner join stocks s on r.ticker=s.ticker " . $where ;     

    $stmt = $this->db->prepare($sql1);
    $stmt->execute([ $id ]);  
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt2 = $this->db->prepare($sql2);
    $stmt2->execute([ $id ]); 
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
      array_push($results,$row);
    }  
    $stmt3 = $this->db->prepare($sql3);
    $stmt3->execute([ $id ]); 
    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
      array_push($results,$row);
    }     
    //$logger = getLogger();
    //$logger->info('1) Sharpe.php', array('results' => $results));
    return $results;
  }
}    // end of Class  