<?php
require_once __DIR__ . '/vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();

$config = new \FxcmRest\Config([
    'host' => 'api.fxcm.com',
    'token' => '2d8ef4e9699a2ab612fa887846ee4ba1d13afbd4',
]);
function epoch2date($str) {  // 1483228800123 (13-chars)
    $iepoch = (int)substr($str,0,10);  // 1st 10-chars (original 13 chars)
    $date=date_create();        
    date_timestamp_set($date, $iepoch);
    return $date;
};

function showPriceTable($counter, $jsdata) {
    echo "<table border='1'><tr><th>Counter</th><th>Key</th><th>Value</th></tr>";    
    $arr=json_decode($jsdata, true);   // associative array (3)
                                       //       key     var
                                       // 1 = Updated  1555794 (date)
                                       // 2 = Rate     array(1.12, 1.13, 1.20)
                                       // 3 = Symbol   EUR/USD
    foreach ($arr as $k=>$v){
        echo "<tr><td>".$counter."</td><td>".$k."</td>";
        if ($k === 'Updated') {
            $date=epoch2date($v);      
            echo "<td>".date_format($date, 'U = Y-m-d H:i:s')."</td>";
        } else {
            echo "<td>".( is_array($v) ? implode(",",$v) : $v )."</td>";
        }
        echo "</tr>";
    };
    echo "</table>";  
};
function array2String($e) {
    return is_array($e) ? implode(",",$e) : $e;
};
function showAccDetails($obj2) {
  echo "** Show Account Details **<br/>";
  echo "accountId:".$obj2["accountId"];
  echo "<br/>accountName:".$obj2["accountName"];  
  echo "<br/>balance:".$obj2["balance"];
  echo "<br/>usableMargin:".$obj2["usableMargin"];  
  echo "<br/>";  
};
function showAccounts($jsdata) {  
  $obj=json_decode($jsdata, true);   // associative array (2)
  $js2=$obj['accounts'];
  echo "<table border='1'><tr>";     
  foreach ($js2 as $k1=>$v1){   // $k1 : 0,1
    echo "<td><u>Key</u>:<br/>".$k1."</td><td><u>Value</u>:<br/>"; 
    foreach($v1 as $k2=>$v2) {  // $v1 : associative array
      echo $k2."=>".$v2."<br/>";    
    }
    echo "</td>";       
  };
  echo "</tr></table>";
  showAccDetails($js2[0]);   // 0, 1
};
$counter = 0;
$rest = new \FxcmRest\FxcmRest($loop, $config);
// signals: connected, error
// Offer, OpenPosition, ClosedPosition, Account, Summary, Properties - /trading/subscribe
// EUR/USD, EUR/GBP,... - /subscribe
$rest->on('connected', function() use ($rest,&$counter) {
    //-- assignment 1 (get account details) -------------------
    $rest->request('GET', '/trading/get_model?models=Account',[], 
        function($code,$data) {
            showAccounts($data);
        }
    );
    // -- assignment 2 (get daily prices for EUR/USD ---------------------------
    $rest->request('POST', '/subscribe', ['pairs' => 'EUR/USD'],
        function($code, $data) use ($rest,&$counter) {
            if($code === 200) {
              $rest->on('EUR/USD', function($data) use ($rest,&$counter) {
                    // echo "price update: {$data}\n";
                    showPriceTable($counter, $data);
                    $counter++;
                    if($counter === 5){
                        $rest->disconnect();
                    }
                });
            }
       }
    );
    // -- end of assignments ---------------------------    
});

$rest->on('error', function($e) use ($loop) {
    echo "socket error: {$e}\n";
    $loop->stop();
});
$rest->on('disconnected', function() use ($loop) {
    echo "FxcmRest disconnected\n";
    $loop->stop();
});
$rest->connect();

$loop->run();
?>
