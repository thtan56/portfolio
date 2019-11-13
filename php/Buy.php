<?php
require __DIR__.'/DBclass.php';
// require __DIR__.'/configLog.php';
class Buy {
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
  
  public function getBuys() {
    $stmt = $this->db->prepare("select * from buy order by created desc");
    $stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$rs) {
      $this->msg = 'No rows'; 
    };
    return $rs;
  }
  public function insertBuy($arrdata) { 
    // $logger = getLogger();
    // $logger->info('1) Buy.php-insertBuy:', array('arrdata' => $arrdata));    
    $sql = "insert into buy(ticker, sector, investor, close, sharpe"; 
    $sql .= ",retavg, retsd, maxdrawdown, romad, pe, eps)";
    $sql .= " values (?,?,?,?,?,?,?,?,?,?,? )";
    $stmt = $this->db->prepare($sql);
    foreach ($arrdata as $index => $row) {
      //$logger->info('2) Buy.php-insertBuy:', array('row' => ['row'=>$row->ticker]));    
      $stmt->execute([ $row->ticker, $row->sector, $row->investor, $row->close, $row->sharpe
                ,$row->retavg, $row->retsd   ,$row->maxdrawdown
                ,$row->romad ,$row->pe, $row->eps   
                  ]);
    }
  } 
}    // end of Class
