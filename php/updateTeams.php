<?php
require __DIR__.'/DBclass.php';

$dbObj = new DB();
$db = $dbObj->getPDO(); 
//-------------------------------------------
$select = $db->prepare("select name from team where name = ?");
$insert = $db->prepare("insert team (name, organiser, venue) values (?, ?, ?) ");
$update = $db->prepare("update game set home_team=?, away_team=? where id=?");
$update2 = $db->prepare("update game_pool set home_team=?, away_team=? where id=?");
//--------------------------------------------
$regex1 = "~( vs )~";
//$result = $db->prepare("select id, name, organiser, home_team, away_team, venue from game where organiser <> 'Asian Games' ");
$result = $db->prepare("select id, name, organiser, home_team, away_team from game_pool where organiser <> 'Asian Games' ");
$result->execute();
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
// $teams = preg_replace( $regex1, "" , $row['name'] ); 
  $teams = preg_split($regex1, $row['name']);
  print_r($teams);
  echo "<BR>";
  if ( count($teams) > 1 ) {
    $update2->execute([$teams[0], $teams[1], $row['id'] ]);
/*    
    foreach ($teams as $key => $team) {
      $select->execute([ $team ]);
      if ($select->rowCount() == 0) {
        $venue = ($key == 0) ? $row['venue'] : "";
        $insert->execute([ $team, $row['organiser'], $venue ]);
      }
    }
*/    
  }; 
};
echo "updated-".$regex1 ;
?>
