<?php
function getPDO() {
  $dsn = 'mysql:dbname=test;host=localhost';
  $user = 'root';
  $pass = 'cancer56';
  $pdo = new PDO($dsn, $user, $pass);
  return $pdo;
}
function updateContract_Stock_DailyPrices($csv) {
  $pdo = getPDO();
  $sql1 = "update contracts set LastPrice = ? where ticker = ?" ;   
  $sql2 = "update stocks set lastprice = ? where ticker = ?" ;  
  $sql3 = "INSERT INTO daily_prices (name, ticker, per, date, open, high, low, close, volume) ";
  $sql3 .= " VALUES ( ?,?,?,?,?,?,?,?,? )";
  $contract = $pdo->prepare($sql1);
  $stock = $pdo->prepare($sql2);
  $daily = $pdo->prepare($sql3);    
  $i=0;
  $num_rows = sizeof($csv);
  foreach ($csv as $index => $value) {
    //===progress bar ( 2 )===========================
    $i++;
    $percent = intval($i/$num_rows * 100)."%";   
    echo '<script language="javascript">
    document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#ddd;\">&nbsp;</div>";
    document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
    </script>';   
    flush();  // Send output to browser immediately   
    //==============================
    $contract->execute( array($value['<CLOSE>'], $value['<TICKER>']) );
    $stock->execute( array($value['<CLOSE>'], $value['<TICKER>']) );
    $daily->execute( array($value['<Name>'], $value['<TICKER>'], $value['<PER>'],
        $value['<DATE>'],  $value['<OPEN>'], $value['<HIGH>'],   $value['<LOW>'],
        $value['<CLOSE>'], $value['<VOL>']   ) );
  }
  // Tell user that the process is completed  (3)
  echo '<script language="javascript">document.getElementById("information").innerHTML="Process 1 completed"</script>';       
}
//==================================
function showContracts() {
  $pdo = getPDO();
  $sql = "select * from contracts" ;   
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  echo "<table><tr><th>Ticker</th><th>Quantity</th><th>Last Price</th></tr>";  
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>".$row['ticker']."</td><td>".$row['Quantity']."</td><td>".$row['LastPrice']."</td></tr>";
  }
  echo "</table>";
}
function showContract($ticker) {
  $pdo = getPDO();
  $sql = "select id, ticker, uom, ContractDate, Status, Quantity, Price, LastPrice ";
  $sql .= " from contracts where ticker=?" ;   
  $stmt = $pdo->prepare($sql);
  $stmt->execute([ $ticker ]);  
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
     echo $row['ticker'] .":". $row['Quantity'].":". $row['Price']. "<br/>";
  }
}
//==================================
// daily_returns
function calculate_StockMeans() {
  $pdo = getPDO();
  $sql1 = "select ticker, AVG(treturn) as average, STDDEV(treturn) as stddev ";
  $sql1 .= " from daily_returns group by ticker " ;   
  $sql2 = "update stocks set daily_mean = ?, daily_sd = ? where ticker = ?" ; 
  $stock = $pdo->prepare($sql2);

  $stmt = $pdo->query($sql1);
  $num_rows=$stmt->rowCount();   $i=0;
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //===progress bar ( 2 )===========================
    $i++; $percent = intval($i/$num_rows * 100)."%";   
    echo '<script language="javascript">
    document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#ddd;\">&nbsp;</div>";
    document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
    </script>';   
    flush();  // Send output to browser immediately   
    //==============================
    $stock->execute( array($row['average'], $row['stddev']
                          ,$row['ticker']) );
  }
  // Tell user that the process is completed  (3)
  echo '<script language="javascript">document.getElementById("information").innerHTML="Process 3 completed"</script>';          
}
//==================================
// call stored_procedure
function execute_sp_update_all() {
  echo '<script language="javascript">document.getElementById("information").innerHTML="Process 2 started"</script>';   
  try {  
    $pdo = getPDO();
    $sql = 'CALL  d00_update_all()';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();  
  } catch (PDOException $e) {
    die("Error occurred:" . $e->getMessage());
  }
  echo '<script language="javascript">document.getElementById("information").innerHTML="Process 2 completed"</script>';          
}
//======================================
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
  $xx = ( $n * $xx_sum ) - ( $x_sum * $x_sum );
  
  $slope = $xx == 0 ? : ( ( $n * $xy_sum ) - ( $x_sum * $y_sum ) ) / $xx;
  $intercept = ( $y_sum - ( $slope * $x_sum ) ) / $n;     // calculate intercept

  return array( 'slope'     => $slope, 'intercept' => $intercept,
  );
}
// update stock after calculate regression
function update_StockRegression() {   // to be completed later
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
  
  $num_rows=$price->rowCount();   $i=0; $seqno=0;
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
    if ($seqno == 0) { $old_ticker  = $row['ticker']; };
    if ($old_ticker == $row['ticker'] ) { $seqno++;
    } else {
      if (count($xs)) {   // if not empty
        $results = linear_regression( $xs, $ys );
        $stock->execute([ number_format($results['slope'],2), number_format($results['intercept'],2), $old_ticker ]);
      };
      // echo $old_ticker, number_format($results['slope'],2),">>", number_format($results['intercept'],2), '<br/>';      
      $old_ticker  = $row['ticker'];    $seqno=1;    $xs = array();    $ys = array();    
    };
    array_push($xs, $seqno);
    array_push($ys, $row['close']);
  }
  // Tell user that the process is completed  (3)
  echo '<script language="javascript">document.getElementById("information").innerHTML="Process 4 completed"</script>';  
  $pdo=Null;         
} 
?>
