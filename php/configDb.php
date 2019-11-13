<?php
define("DSN_LOCAL", "mysql:dbname=test;host=localhost");
define("DSN2_REMOTE", "mysql:dbname=test;unix_socket=/cloudsql/tobisports-2018:us-central1:mysql1956");
define("DSN_REMOTE", "mysql:dbname=test;unix_socket=/cloudsql/starpunter:us-central1:mysql56");
define("DSN_HEROKU", "pgsql:host=ec2-54-235-242-63.compute-1.amazonaws.com;port=5432;sslmode=require;dbname=dc1cog334s79lk");
define("USER", "root");
define("PASSWORD", "cancer56");
define("DATABASE", "test");

function getMyConnection() {
	$host="localhost";
	$conn = mysqli_connect($host, USER, PASSWORD, DATABASE);
	return $conn;
}
function getPdoConnection(){
  $user = "root";
  $password = "cancer56";

  $server_id=$_SERVER['HTTP_HOST'];
  if (preg_match('/\bherokuapp\b/', $server_id)) {
  	$user="uorfkbdhshqhlv";
    $password="4c6f9e3adecae17f2f8b3ac2351f75a5effe164dd867f176d6c9e6be90400050";
    $dsn="pgsql:host=ec2-54-235-242-63.compute-1.amazonaws.com;port=5432;sslmode=require;dbname=dc1cog334s79lk";
  } else if (preg_match('/\bappspot\b/', $server_id)) {
  			 	 $dsn = "mysql:dbname=test;unix_socket=/cloudsql/starpunter:us-central1:mysql56";
           $dsn2 = "mysql:dbname=test;unix_socket=/cloudsql/tobisports-2018:us-central1:mysql1956";
  } else { $dsn = "mysql:dbname=test;host=localhost"; };
    
  try {
  	$db = new PDO($dsn, $user, $password);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  } catch (PDOException $e) { $this->msg = $e->getMessage();  }   
  return $db;
}
?>