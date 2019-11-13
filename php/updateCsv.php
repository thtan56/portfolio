<?php
require __DIR__.'/DBclass.php';

$dbObj = new DB();
$db = $dbObj->getPDO(); 

if (($file = fopen("../api/nba.csv", "r")) !== FALSE) {
  $organiser = 'NBA';
  $select  = "Select organiser, home_team, away_team, start, id ";
  $select .= " from game where organiser=? and home_team=? and away_team=? and start=? ";
  $stmt1 = $db->prepare($select);

      $today = date("Y-m-d H:i:s");
      $first = date('Y-m-01'); 
      $last = date('Y-m-t');
      echo "first:today:".$first.">".$last.">".$today;

  $lineno=1;
  while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
    if ($lineno<>1) {
      $obj['round']=$row[0];
      $obj['date']=$row[1];
      $obj['venue']=$row[2];
      $obj['home_team']=$row[3];
      $obj['away_team']=$row[4];
      $obj['result']=$row[5];
      //---------------------------------

      $date  = date_create_from_format('d/m/Y H:i', $obj['date']);
      $tsdate= $date->getTimestamp();
      $start = date("Y/m/d", $tsdate);   //      $sdate = strftime("%Y/%m/%d", $tsdate);
      //----------------------
      echo $organiser.'>'.$obj['home_team'].'>'.$obj['away_team'].'>'.$start;

      $stmt1->execute([ $organiser, $obj['home_team'], $obj['away_team'], $start ]);
      $rows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
      if(!$rows) {
        echo 'No rows found'; 
      } else {
        echo ">id:".$rows[0]['id']."<br>";
      };
//-------------------------------------------
/*
      if ($row[5] <> "") {  echo "<br>>>>>>".$obj['result'];
      } else {              echo "<br>>>>>No result"; };
      echo "<br>@@@@@";
      echo date("Y-m-d", $tsdate);
      echo "<br>$$$$$$";
      $scores = explode('-',$row[5]);
      print_r($scores);
*/
    };
    $lineno++;
  };
  fclose($file);
}
?>
