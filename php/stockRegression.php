<?php
$pdo = getPDO();

$sql1   = "SELECT a.ticker, a.date, a.close, b.count FROM daily_prices a "; 
$sql1 .= " INNER JOIN ( ";
$sql1 .= " SELECT ticker, count(*) as count FROM daily_prices "; 
$sql1 .= " WHERE (NOT (ticker LIKE '%-%')) ";
$sql1 .= " GROUP BY ticker ) b ON  a.ticker = b.ticker ";
$sql1 .= " ORDER BY a.ticker, a.date ";

$sql2 = "update stocks set gradient = ?, intercept = ? where ticker = ?" ; 
$stock = $pdo->prepare($sql2);
$price = $pdo->query($sql1);  

$num_rows=$stmt->rowCount();   $i=0;
$old_ticker="";
$xs = array();
$ys = array();  
while ($row = $price->fetch(PDO::FETCH_ASSOC)) {
  //===progress bar ( 2 )===========================
  $i++; $percent = intval($i/$num_rows * 100)."%";   
  echo '<script language="javascript">
  document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#ddd;\">&nbsp;</div>";
  document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
  </script>';   
  flush();  // Send output to browser immediately   
  //==============================  
  if ($i == 0) { $old_ticker  = $row['ticker']; };
  if ($old_ticker == $row['ticker'] ) { $i++;
  } else {
    $results = linear_regression( $xs, $ys );
    $stock->execute([ number_format($results['slope'],2), number_format($results['intercept'],2), $old_ticker ]);

    echo $old_ticker, number_format($results['slope'],2), 
          ">>", number_format($results['intercept'],2), '<br/>';      
    $old_ticker  = $row['ticker'];    $i=1;    $xs = array();    $ys = array();    
  };
  array_push($xs, $i);
  array_push($ys, $row['close']);
}
// Tell user that the process is completed  (3)
echo '<script language="javascript">document.getElementById("information").innerHTML="Process 4 completed"</script>';  
$pdo=Null;
echo "Updated successfully";  
//===============================================
function getPDO() {
  $dsn = 'mysql:dbname=test;host=localhost';
  $user = 'root';
  $pass = 'cancer56';
  $pdo = new PDO($dsn, $user, $pass);
  return $pdo;
}
function linear_regression( $x, $y ) {
  $n     = count($x);     // number of items in the array
  $x_sum = array_sum($x); // sum of all X values
  $y_sum = array_sum($y); // sum of all Y values

  $xx_sum = 0;
  $xy_sum = 0;

  for($i = 0; $i < $n; $i++) {
      $xy_sum += ( $x[$i]*$y[$i] );
      $xx_sum += ( $x[$i]*$x[$i] );
  }
  $slope = ( ( $n * $xy_sum ) - ( $x_sum * $y_sum ) ) / ( ( $n * $xx_sum ) - ( $x_sum * $x_sum ) );
  $intercept = ( $y_sum - ( $slope * $x_sum ) ) / $n;     // calculate intercept

  return array( 
      'slope'     => $slope,
      'intercept' => $intercept,
  );
}
?>


