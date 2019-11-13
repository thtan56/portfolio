<?php
require __DIR__.'/DBclass.php';
// require_once('configLog.php');

class Contract {
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

  public function getContracts($groupid=NULL) {
    $sql = "select a.id, a.ticker, a.uom, a.ContractDate, a.Quantity";
    $sql .= ", a.UnitCost, a.Status,   a.StopPrice, a.LastPrice, a.Nav ";
    $sql .= ", s.groupid, s.stop_loss, s.atr, t.sharpe ";
    $sql .= ", a.Quantity * a.UnitCost as Cost ";
    $sql .= ", (a.Quantity * a.LastPrice) as TotalValue ";    // remove a.nav
    $sql .= ", a.Quantity * (a.LastPrice - a.UnitCost) as Profit ";
    $sql .= ", (a.LastPrice - a.UnitCost) * 100 / a.UnitCost  as ROI ";
    $sql .= " from contracts a ";
    $sql .= " inner join stocks s on a.ticker=s.ticker "; 
    $sql .= " inner join sharpes t on a.ticker=t.ticker ";        
    $sql .= " where a.status='active' ";       
    $sql .= ($groupid !== NULL) ? " and s.groupid=?" : "";   
    $sql .= " order by a.ticker desc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $groupid ]);  
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$contracts) { $this->msg = 'No rows'; 
      exit;
    };
    return $contracts;
  }
  public function getGroupContracts($json) {
    $gid = $json->{'data'}->{'gid'}; 
    $sql = "select a.id, a.ticker, a.uom, a.ContractDate, a.Quantity";
    $sql .= ", a.UnitCost, a.Status, a.StopPrice, a.LastPrice, a.Nav ";
    $sql .= ", s.groupid, s.stop_loss, s.atr ";    
    $sql .= ", a.Quantity * a.UnitCost as Cost ";    
    $sql .= ", (a.Quantity * a.LastPrice) + a.Nav as TotalValue ";
    $sql .= ", a.Quantity * (a.LastPrice - a.UnitCost) as Profit ";
    $sql .= ", (a.LastPrice - a.UnitCost) * 100 / a.UnitCost  as ROI ";  
    $sql .= ", s.groupid ";
    $sql .= " from contracts a ";
    $sql .= " inner join stocks s on a.ticker=s.ticker ";    
    $sql .= " where s.groupid=? ";  
    $sql .= " and  a.status='active' ";       
    $sql .= " order by a.ticker desc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute( [$gid ]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$contracts) { $this->msg = 'No rows'; 
      exit;
    };
    return $contracts;
  }  
  public function getTickerContracts($ticker) {
    $sql = "select a.id, a.ticker, a.uom, a.ContractDate, a.Quantity";
    $sql .= ", a.UnitCost, a.Status, a.StopPrice, a.LastPrice, a.Nav ";
    $sql .= ", s.groupid, s.stop_loss, s.atr ";    
    $sql .= ", (a.Quantity * a.LastPrice) + a.Nav as TotalValue ";
    $sql .= ", a.Quantity * (a.LastPrice - a.UnitCost) as Profit ";
    $sql .= ", (a.LastPrice - a.UnitCost) * 100 / a.UnitCost  as ROI ";  
    $sql .= ", s.groupid ";
    $sql .= " from contracts a ";
    $sql .= " inner join stocks s on a.ticker=s.ticker ";    
    $sql .= " where a.ticker=? ";  
    $sql .= " order by a.ticker desc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute( [$ticker ]);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$contracts) { $this->msg = 'No rows'; 
      exit;
    };
    return $contracts;
  }
  public function getContract($id) {
    $stmt = $this->db->prepare("select * from contracts where id=?");
    $stmt->execute([ $id ]);
    $contract = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$contract) {
      $this->msg = 'No rows';       exit;
    };
    return $contract;  }
  // round = period
  public function getContractSummary() {
    $sql  = "SELECT organiser, week(start) as weekno, start, round, count(*) as gamecount FROM contracts ";
    $sql .= " where status='active' ";       
    $sql .= "group by organiser, week(start), start, round";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$contracts) {
      $this->msg = 'No rows'; 
      exit;
    };
    return $contracts;
  }
  public function insertContract($json) { 
    $sql = "insert into contracts(ticker,uom, ContractDate, Quantity, UnitCost, Status, created)";
    $sql .= " values (?,?,?,?,?, 'pending', now())";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'ticker'}
                    ,$json->{'data'}->{'uom'} ,$json->{'data'}->{'ContractDate'}  
                    ,$json->{'data'}->{'Quantity'}, $json->{'data'}->{'UnitCost'} 
                   ]);
  } 
  public function deleteContract($id) {
    $stmt = $this->db->prepare("delete from contracts where id=?");
    $stmt->execute([ $id ]);
  }
  public function updateContract($json) {        // object, not array - mangames.js
    // $logger = getLogger();
    // $logger->info('1) contract.php:updateContract', array('json' => $json));
    $sql = "update contracts set ticker=?, uom=?, ContractDate=?";
    $sql .= ",Quantity=?, UnitCost=?, Status=?"; 
    $sql .= " where id=?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([ $json->{'data'}->{'ticker'}
            ,$json->{'data'}->{'uom'},$json->{'data'}->{'ContractDate'}
            ,$json->{'data'}->{'Quantity'}  ,$json->{'data'}->{'UnitCost'}  
            ,$json->{'data'}->{'Status'}       
            ,$json->{'data'}->{'id'} ]);
  }
}    // end of Class
