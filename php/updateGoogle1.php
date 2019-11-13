<?php
// require __DIR__.'/DBclass.php';

function getPdoConnection($server_id){
  $user = "root";
  $password = "cancer56";
  
  if (preg_match('/\bherokuapp\b/', $server_id)) {
    $user="uorfkbdhshqhlv";
    $password="4c6f9e3adecae17f2f8b3ac2351f75a5effe164dd867f176d6c9e6be90400050";
    $dsn="pgsql:host=ec2-54-235-242-63.compute-1.amazonaws.com;port=5432;sslmode=require;dbname=dc1cog334s79lk";
  } else if (preg_match('/\bappspot\b/', $server_id)) {
           $dsn = "mysql:dbname=test;unix_socket=/cloudsql/tobisports-2018:us-central1:mysql1956";
  } else { $dsn = "mysql:dbname=test;host=localhost"; };
    
  try {
    $db = new PDO($dsn, $user, $password);
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  } catch (PDOException $e) { $this->msg = $e->getMessage();  }   
  return $db;
};

$google=getPdoConnection('appspot');  echo "1) google connected<br>";
$local=getPdoConnection('local');     echo "2) local connected<br>";
$heroku=getPdoConnection('herokuapp');echo "3) heroku connected<br>";
//==================================
//$select = $db->prepare("select name from team where name = ?");
//$insert = $db->prepare("insert team (name, organiser, venue) values (?, ?, ?) ");
//$update = $db->prepare("update game set home_team=?, away_team=? where id=?");
//$update2 = $db->prepare("update game_pool set home_team=?, away_team=? where id=?");
//$result = $db->prepare("select id, name, organiser, home_team, away_team, venue from game where organiser <> 'Asian Games' ");
$result = $google->prepare("select id, name, organiser, home_team, away_team from game_pool where organiser <> 'Asian Games' ");
$result->execute();
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
// $teams = preg_replace( $regex1, "" , $row['name'] ); 
  print_r($row);
  echo "<BR>";  
};
echo "selected game_pool - google";
?>
