<?php
include("functions.php");
$fileName = $argv[1];

if(!$argv[2])
$niftyIndex = 'n50';
else $niftyIndex = $argv[2];

echo "Generating Report for: ".$niftyIndex." \n";
// echo "\nTradeDate :: ".$tradeDate."\n";
//exit;
$host_name = "127.0.0.1";
$db_username = "root";
$db_password = "";
$db_name = "my_demo";
$conn = new mysqli($host_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("ERROR: Unable to connect: " . $conn->connect_error);
} 
echo "\n Connected to the database.\n";


$getNse50Sql = "SELECT GROUP_CONCAT(DISTINCT `symbol` SEPARATOR ',') as nse50 FROM nse_50 where nse_index = 'n50' OR nse_index = 'n100'";
$getNse50Res =mysqli_query($conn, $getNse50Sql);
// echo $getNse50Sql;
// exit;
$getNse50Row =  mysqli_fetch_assoc($getNse50Res);
//echo $getNse50Row['nse50'];
$nse50Array = explode(',', $getNse50Row['nse50']);

// print_r($nse50Array);
// exit;

$filename = "NSE-Data/Full-dowload-report/".$fileName.".csv";    

$file = fopen($filename, "r");


$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    // ignoring the first row
    if(strtolower($getData[0]) == strtolower('symbol')) {
        continue;
    }

    // if($i > 10)
    // break;
    // $i++;

    // check if series is EQ only
    if(trim(strtolower($getData[1])) != strtolower('eq')) {
        continue;
    }

    // check if nifty 50 only
    if(!in_array($getData[0], $nse50Array) ) {
        continue;
    }

    $trade_date = date("Y-m-d", strtotime($getData[2]));

    //echo $trade_date."\n";
      
    $sql = "INSERT INTO daily_security_archive  (symbol, series, `trading_date`, prev_close, open_price, high_price, low_price, last_price, close_price, avg_price, total_trade_quantity, turn_over, no_of_trade, deliverable_qty, delivery_percentage) 
    VALUES 
    ('".$getData[0]."','".$getData[1]."','".$trade_date."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."','".$getData[8]."','".$getData[9]."','".$getData[10]."','".$getData[11]."','".$getData[12]."','".$getData[13]."','".$getData[14]."')";
      

    //echo $sql;
    $result = mysqli_query($conn, $sql);
    //exit;

    if($result)
    $i++;
    
}
echo "\n $i Records  Inserted successfully.\n";
echo "Trade Date : ".$trade_date."\n";
echo "Generating report for high DQ and DP...\n";

// Generate watch list for the day based on DP,DQ,VOL and price breakout

if($niftyIndex == 'n50')
generateDailyDqANDDpReport($trade_date);
else if($niftyIndex == 'n100')
generateDailyDqANDDpReportN100($trade_date);
else 
echo "Please Select a Valid Nifty Index";

echo "Completed!!.\n";
$conn->close();


?>