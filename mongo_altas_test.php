<?php
require 'vendor/autoload.php';

try{
  $con = new MongoDB\Client("mongodb+srv://tthuat56:cancer56@cluster0-0qcn9.mongodb.net");
  echo ("1) Connection OK <br/>");
  $db = $con->portfolio;      echo "2) Database db portfolio selected <br />";
  $collection = $db->stocks;  echo "3) Collection stocks selected <br />";  
  $documents = $collection->find();
  echo "<table border=1><tr><th>Ticker</th><th>Sector</th><th>Name</th></tr>";
  foreach ($documents as $document) {
    echo "<tr><td>" . 
      $document["ticker"] . '</td><td>' .
      $document["sector"] . '</td><td>' .
      $document["name"]   . "</td></tr>";
  }
  echo "</table>";
echo ("connected to database properly");
// Insering Record  
//$collection->insertOne( [ 'name' =>'MILJO JOHN KODIYAN', 'email' =>'miljo@techcthree.com' ] );
// Fetching Record  

}catch(Exception $exp) {

echo($exp);

}
?>
