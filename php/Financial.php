<?php
// require_once('configLog.php');
require __DIR__.'/DBclass.php';

class Financial {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
  public function getEps($id) {
    // $logger = getLogger();
    $sql = "select ticker2 as ticker, period, date_announced as date, year_end, year, quarter ";
    $sql .= ", revenue, eps, dividend, unit_price, profit_margin";
    $sql .= " from fintable ";
    $sql .= ' WHERE ticker2 = ? ';    
    $sql .= " order by period ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);  
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$arr) { 
      $this->msg = 'No rows';    
      // exit;     cannot exit otherwise no message pass out
    };
    return $arr;
  }
  public function getStocks($uom=NULL, $id=NULL) {
    $where = " where f.year > 2016 ";
    if($uom <> NULL) { $where .= " and ".$uom. " regexp ? "; };     // a sector,a favorite, investors
    $sql = "select a.ticker, a.sector, a.favorite, a.investor, a.indice ";
    $sql .= ", f.ticker2, f.stock_name, f.period, f.date_announced as date, f.year_end, f.year, f.quarter ";
    $sql .= ", f.revenue, f.eps, f.dividend, f.unit_price, f.profit_margin";
    $sql .= " from stocks a ";    
    $sql .= " inner join fintable f on a.ticker=f.ticker2 ";       
    $sql .= $where ;     
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $id ]);  
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$stocks) { $this->msg = 'No rows'; };
    return $stocks;
  }
}    // end of Class
