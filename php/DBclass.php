<?php
class DB {
    private $dbDsn;
    private $dbUser;
    private $dbPass;
    public $dbServer;
    private $msg;
    protected $pdo;    // $con

    private $local = array(
      // version 1
      'db_dsn' => 'mysql:dbname=test;host=localhost',
      'db_user' => 'root',
      'db_pass' => 'cancer56'
      //----------------------------
      // version 2
      //'db_dsn' => 'pgsql:host=ec2-54-235-242-63.compute-1.amazonaws.com;port=5432;sslmode=require;dbname=dc1cog334s79lk',
      //'db_user' => 'uorfkbdhshqhlv',
      //'db_pass' => '4c6f9e3adecae17f2f8b3ac2351f75a5effe164dd867f176d6c9e6be90400050'
      );
    private $google = array(
//      'db_dsn' => 'mysql:dbname=test;unix_socket=/cloudsql/tobisports-2018:us-central1:mysql1956',
      'db_dsn' => 'mysql:dbname=test;unix_socket=/cloudsql/starpunter:us-central1:mysql56',
      'db_user' => 'root',
      'db_pass' => 'cancer56'
      );
    private $heroku = array(
      'db_dsn' => 'pgsql:host=ec2-54-235-242-63.compute-1.amazonaws.com;port=5432;sslmode=require;dbname=dc1cog334s79lk',
      'db_user' => 'uorfkbdhshqhlv',
      'db_pass' => '4c6f9e3adecae17f2f8b3ac2351f75a5effe164dd867f176d6c9e6be90400050'
    );
    public function __construct() {
    	$server_id=$_SERVER['HTTP_HOST'];

    	if (preg_match('/\bherokuapp\b/', $server_id)) {
    		$this->dbServer="heroku";
      	$this->dbUser=$this->heroku['db_user'];
      	$this->dbPass=$this->heroku['db_pass'];
      	$this->dbDsn=$this->heroku['db_dsn'];
    	} else if (preg_match('/\bappspot\b/', $server_id)) {
    		$this->dbServer="google";
      	$this->dbUser=$this->google['db_user'];
      	$this->dbPass=$this->google['db_pass'];
      	$this->dbDsn=$this->google['db_dsn'];
    	} else {
    		$this->dbServer="local";
      	$this->dbUser=$this->local['db_user'];
      	$this->dbPass=$this->local['db_pass'];
      	$this->dbDsn=$this->local['db_dsn'];
      }
		}
    public function getPDO() {
    	try {
    		$this->pdo = new PDO($this->dbDsn, $this->dbUser, $this->dbPass);
      	$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	} catch (PDOException $e) { $this->msg = $e->getMessage();  }   
      return $this->pdo;
    }
}
?>
