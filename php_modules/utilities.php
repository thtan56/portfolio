<?php
function readCsv($filename) {
  echo "filename:".$filename."<br/>";
  $csv = array_map('str_getcsv', file($filename));
  array_walk($csv, function(&$a) use ($csv) {
    $a = array_combine($csv[0], $a);
  });
  array_shift($csv); # remove column header
  return $csv;
}
function showDailyPrices($csv) {
  echo "<table><tr><th>Name</th><th>TICKER</th><th>PER</th><th>DATE</th>";
  echo "<th>OPEN</th>,<th>HIGH</th>,<th>LOW</th><th>CLOSE</th><th>VOL</th><th>OPENINT</th></tr>";
  foreach ($csv as $index => $value) {
    echo "<tr><td>".$value['<Name>']."</td><td>".$value['<TICKER>']."</td>";
    echo "<td>".$value['<PER>'] ."</td><td>".$value['<DATE>']."</td>";
    echo "<td>".$value['<OPEN>']."</td><td>".$value['<HIGH>']."</td>";    
    echo "<td>".$value['<LOW>'] ."</td><td>".$value['<CLOSE>']."</td>";
    echo "<td>".$value['<VOL>'] ."</td><td>".$value['<OPENINT>']."</td></tr>";
  }
  echo "</table>";
}
?>
