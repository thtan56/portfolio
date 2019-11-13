<?php
require 'vendor/autoload.php';

try{
$con = new MongoDB\Client("mongodb+srv://tthuat56:cancer56@cluster0-0qcn9.mongodb.net");
echo ("Connection OK");

$db = $con->db_xxxxx;

// Creating Document  
$collection = $db->TechcthreeConnectivitytest;

echo ("connected to database properly");
// Insering Record  
$collection->insertOne( [ 'name' =>'MILJO JOHN KODIYAN', 'email' =>'miljo@techcthree.com' ] );
// Fetching Record  
echo ("Inserted properly");

}catch(Exception $exp) {

echo($exp);

}
?>
