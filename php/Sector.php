<?php
require __DIR__.'/DBclass.php';
class Sector {
  private $msg = "";
  private $result = 1;   // -1 if problem
  //---------------------------------------------   
  public $db;
  public function __construct() { $dbObj = new DB(); $this->db = $dbObj->getPDO(); }
  public function getMsg() { return $this->msg; }
  
  public function getSectors($uom=NULL, $id=NULL) {
    switch($uom){
      case "favorite": $where = " where uom=?"; break;     
      case "sector":   $where = " where uom=?"; break;  
      case "indice":   $where = " where uom=?"; break;     
      case "investor": $where = " where uom=?"; break; 
      default: $where = " ";      
    };
    $sql = "select uom, name, count, bgcolor, concat(icon_prefix, icon_name) as icon";
    $sql .= ", a.id as id, a.created as created, importance, status, pageviews, e_update, e_updated";
    $sql .= ", s.retavg, s.retsd, s.sharpe, s.romad, s.low, s.high, s.maxdrawdown  ";
    $sql .= " from sectors a ";
    $sql .= " left join sector_performance s on a.name=s.sector ";
    $sql .= $where;
    $sql .= " order by s.sharpe desc ";           
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $uom ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rows;        
  }
  public function setEarnings($json) {
    $sql = "update sectors set e_update=? ";
    $sql .= " where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'e_update'}  
            ,$json->{'data'}->{'id'} ]);
  }  
}    // end of Class
