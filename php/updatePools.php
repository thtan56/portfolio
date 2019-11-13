<?php
require __DIR__.'/DBclass.php';

$dbObj = new DB();
$db = $dbObj->getPDO(); 
//-------------------------------------------
$select = $db->prepare("select name, logo from team where name = ?");
$insert = $db->prepare("insert team (name, organiser, venue) values (?, ?, ?) ");
$update = $db->prepare("update game set home_team=?, away_team=? where id=?");
$update2 = $db->prepare("update game_pool set image1=?, image2=? where id=?");
//--------------------------------------------
$regex1 = "~( vs )~";
//$result = $db->prepare("select id, name, organiser, home_team, away_team, venue from game where organiser <> 'Asian Games' ");
$result = $db->prepare("select id, name, organiser, home_team, away_team from game_pool where organiser <> 'Asian Games' ");
$result->execute();
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
  echo $row['home_team'];
  $select->execute([ $row['home_team'] ]);
  $image1=($row2=$select->fetch(PDO::FETCH_ASSOC))  ? "images/".$row2['logo'] : '';
  $select->execute([ $row['away_team'] ]);
  $image2=($row2=$select->fetch(PDO::FETCH_ASSOC))  ? "images/".$row2['logo'] : '';
  $update2->execute([$image1, $image2, $row['id'] ]);  
};
echo "updated images";
?>