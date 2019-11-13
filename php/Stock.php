<?php
require __DIR__.'/DBclass.php';
require('targets.php');
// require_once('configLog.php');

class Stock {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
  
  public function getStocks($uom=NULL, $id=NULL) {
    $where = " ";
    if($uom <> NULL) { $where = " where ".$uom. " regexp ? "; };     // a sector,a favorite, investors
    $sql = "select a.id, a.ticker, a.pe, a.dy, a.eps, a.dps, a.bkval, a.atr ";
    $sql .= ", a.qty, a.unitcost ";
    $sql .= ", a.lastprice, a.stop_loss, a.lowest, a.daily_mean, a.daily_sd ";   // computed
    $sql .= ", a.sector, a.favorite, a.investor, a.comments, a.indice, a.groupid ";
    $sql .= ", s.sharpe, s.maxdrawdown, s.es, s.romad, s.VaR, s.retavg, s.retsd  ";
    $sql .= " from stocks a ";    
    $sql .= " inner join sharpes s on a.ticker=s.ticker ";    
    $sql .= $where ;       
    $sql .= " order by s.sharpe desc";
    // $logger->info('1) Stock.php', array('sql' => ['sql'=>$sql]));    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);  
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$stocks) { $this->msg = 'No rows'; };
    return $stocks;
  }
  //      $where .= " and a.daily_mean >".TGT_MEAN . " and  a.daily_sd <".TGT_SD ;  
  public function getGroupTickers($uom=NULL, $id=NULL) {
    switch($uom){
      case "favorite": $where = " where favorite=?"; break;     
      case "sector":   $where = " where sector=?"; break;  
      case "indice":   $where = " where indice=?"; break;     
      case "investor":   $where = " where investor regexp ?"; break;   
      default: $where = " ";      
    };
    $sql = "select ticker from stocks ". $where ." order by ticker desc";           
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;        
  }
  public function insertStock($json) { 
    $sql = "insert into stocks(ticker, qty, unitcost, lastprice, sector, investor, favorite, indice, groupid, created)";
    $sql .= " values (?,?,?,?,?,?, ?,?,?, now())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'ticker'}
                    ,$json->{'data'}->{'qty'}       ,$json->{'data'}->{'unitcost'}  
                    ,$json->{'data'}->{'lastprice'}, $json->{'data'}->{'sector'} 
                    ,$json->{'data'}->{'investor'},  $json->{'data'}->{'favorite'}
                    ,$json->{'data'}->{'indice'},     $json->{'data'}->{'groupid'} 
                    ]);
  } 
  public function deleteStock($id) {
    $stmt = $this->db->prepare("delete from stocks where id=?");
    $stmt->execute([ $id ]);
  }
  public function updateStock($json) {        // object, not array - mangames.js
      $sql = "update stocks set ticker=?, qty=?, unitcost=?";
      $sql .= ",lastprice=?, sector=?, investor=?, favorite=?"; 
      $sql .= ",indice=?, groupid=? "; 
      $sql .= " where id=?";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([ $json->{'data'}->{'ticker'}
            ,$json->{'data'}->{'qty'},$json->{'data'}->{'unitcost'}
            ,$json->{'data'}->{'lastprice'}  ,$json->{'data'}->{'sector'}  
            ,$json->{'data'}->{'investor'} ,$json->{'data'}->{'favorite'}       
            ,$json->{'data'}->{'indice'} ,$json->{'data'}->{'groupid'}    
            ,$json->{'data'}->{'id'} ]);
  }
  public function getSharpeGroups() {
    $sql  = "select b.sector, a.scategory as category, count(*) as units ";
    $sql .= " from sharpes a ";  
    $sql .= " inner join stocks b on a.ticker=b.ticker ";           
    $sql .= " where a.scategory<>'0.0' ";     
    $sql .= " group by b.sector, a.scategory";   
    $sql .= " order by b.sector, a.scategory";            
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    return $rows;   // array 
  } 
  public function getReturnGroups() {
    $sql  = "select b.sector, a.rcategory as category, count(*) as units ";
    $sql .= " from sharpes a ";  
    $sql .= " inner join stocks b on a.ticker=b.ticker ";           
    $sql .= " where a.rcategory<>'0.0' ";     
    $sql .= " group by b.sector, a.rcategory";   
    $sql .= " order by b.sector, a.rcategory";            
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    return $rows;   // array 
  }
  public function getEpsGroups() {
    $sql  = "select b.sector, a.ecategory as category, count(*) as units ";
    $sql .= " from sharpes a ";  
    $sql .= " inner join stocks b on a.ticker=b.ticker ";           
    $sql .= " where a.ecategory<>'000' and a.ecategory IS NOT NULL ";     
    $sql .= " group by b.sector, a.ecategory";   
    $sql .= " order by b.sector, a.ecategory";            
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    return $rows;   // array 
  }      
  public function getDividendGroups() {
    $sql = "select sector, dy as category, count(*) as units ";
    $sql .= " from stocks ";    
    $sql .= " where dy > 0 ";    
    $sql .= " group by sector, dy";   
    $sql .= " order by sector, dy";        
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$stocks) { $this->msg = 'No rows'; };
    return $stocks;
  }    
  public function getSharpeStocks($sGroup) {
    if (preg_match("/-/", $sGroup)) {  $array=explode("-", $sGroup);   // same as explode
    } else {                           $text = preg_replace("/>/", '', $sGroup); };  // replace '>' with ''
    $sql = "select a.id, a.ticker, a.ticker as name ";  // for scatter graph
    $sql .= ", a.pe, a.dy, a.eps, a.dps, a.bkval, a.atr ";
    $sql .= ", a.qty, a.unitcost ";
    $sql .= ", a.lastprice, a.daily_mean, a.daily_sd, a.stop_loss, a.lowest  ";   // computed
    $sql .= ", a.sector, a.favorite, a.investor, a.comments, a.indice, a.groupid ";
    $sql .= ", s.sharpe, s.retavg, s.retsd, s.maxdrawdown, s.es, s.romad, s.VaR ";
    $sql .= " from stocks a ";    
    $sql .= " inner join sharpes s on a.ticker=s.ticker ";
    if (preg_match("/-/", $sGroup)) {
      $sql .= " where s.sharpe between ? and ? ";
      $sql .= " order by s.sharpe desc";             
      $stmt = $this->db->prepare($sql);      
      $stmt->execute([ $array[0], $array[1] ]);  
    } else {
      $sql .= " where s.sharpe > ? ";  
      $sql .= " order by s.sharpe desc";   
      $stmt = $this->db->prepare($sql);                   
      $stmt->execute([ $text ]);  
    };
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    if(!$stocks) { $this->msg = 'No rows'; };
    return $stocks;
  }
  public function getCategoryStocks($uom, $xGroup) {
    switch($uom){
      case "sector":   $where = " where a.sector=?"; break;         
      case "dividend": $where = " where a.dy=?"; break;     
      case "return":   $where = " where s.rcategory=?"; break;     
      case "eps":      $where = " where s.ecategory=?"; break;   
      //case "sharpe":   $where = " where s.scategory=?"; break;        
    };
    $sql = "select a.id, a.ticker, a.ticker as name ";  // scatter graph
    $sql .= ", a.pe, a.dy, a.eps, a.dps, a.bkval, a.atr ";
    $sql .= ", a.qty, a.unitcost ";
    $sql .= ", a.lastprice, a.daily_mean, a.daily_sd, a.stop_loss, a.lowest  ";   // computed
    $sql .= ", a.sector, a.favorite, a.investor, a.comments, a.indice, a.groupid ";
    $sql .= ", s.sharpe, s.retavg, s.retsd, s.maxdrawdown, s.es, s.romad, s.VaR ";
    $sql .= ", s.scategory, s.rcategory, s.ecategory ";
    $sql .= " from stocks a ";    
    $sql .= " inner join sharpes s on a.ticker=s.ticker ";
    $sql .= $where;
    $sql .= " order by s.sharpe desc";   
    $stmt = $this->db->prepare($sql);                   
    $stmt->execute([ $xGroup ]);  
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);    
    if(!$stocks) { $this->msg = 'No rows'; };
    return $stocks;
  }       
}    // end of Class
