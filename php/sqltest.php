<?php
class DB {
    private $dbDsn;
    private $dbUser;
    private $dbPass;
    public $dbServer;
    private $msg;
    protected $pdo;    // $con

    private $google = array(
      'db_dsn' => 'mysql:dbname=test;unix_socket=/cloudsql/starpunter:us-central1:mysql56',
      'db_user' => 'root',
      'db_pass' => 'cancer56'
    );
    public function __construct() {
  		$this->dbServer="google";
      	$this->dbUser=$this->google['db_user'];
      	$this->dbPass=$this->google['db_pass'];
      	$this->dbDsn=$this->google['db_dsn'];
    }
    public function getPDO() {
    	try { $this->pdo = new PDO($this->dbDsn, $this->dbUser, $this->dbPass);
      		  $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	} catch (PDOException $e) { $this->msg = $e->getMessage();  }   
      	return $this->pdo;
    }
};
class User {   
  public $db;
  public function __construct() { 
    $dbObj = new DB();
    $this->db = $dbObj->getPDO(); 
  }  
  public function getUsers() {
    $stmt = $this->db->prepare("select * from users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results = (!$users) ? [] : $users;
    return $results;
  }
};
$obj = new User();
$ret = $obj->getUsers();
print_r($ret);
?>