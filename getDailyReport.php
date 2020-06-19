<?php
include("functions.php");
include("config.php");
// $bhavDataFullfileName = $argv[1];

if(isset($_REQUEST['nindex']) &&  strtolower($_REQUEST['nindex']) == 'n100' )
$niftyIndex = 'n100';
else $niftyIndex = 'n50';

//echo $niftyIndex;

if (file_exists($bhavDataFullfileName)) {
    unlink($bhavDataFullfileName);
}

$fileDownload = downloadFile($bhavDataFullfileName, $bhavDataFullUrl);

if(!$fileDownload) {
    echo "Not able to download file from NSE, Please try after sometime. Exiting....";
    exit;
}

//exit;


//echo "Generating Report for: ".$niftyIndex." \n";
// echo "\nTradeDate :: ".$tradeDate."\n";
//exit;



$getNse50Sql = "SELECT GROUP_CONCAT(DISTINCT `symbol` SEPARATOR ',') as nse50 FROM nse_50 where nse_index = 'n50' OR nse_index = 'n100'";
$getNse50Res =mysqli_query($conn, $getNse50Sql);
// echo $getNse50Sql;
// exit;
$getNse50Row =  mysqli_fetch_assoc($getNse50Res);
//echo $getNse50Row['nse50'];
$nse50Array = explode(',', $getNse50Row['nse50']);

// print_r($nse50Array);
// exit;

//$bhavDataFullfileName = "NSE-Data/Full-dowload-report/".$bhavDataFullfileName.".csv";    

$file = fopen($bhavDataFullfileName, "r");


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
// echo "\n $i Records  Inserted successfully.\n";
// echo "Trade Date : ".$trade_date."\n";
// echo "Generating report for high DQ and DP...\n";

// Generate watch list for the day based on DP,DQ,VOL and price breakout

// if($i < 100) {
//     echo "There is error in the downloaded file, please check and run it again, it did not find all 100 stocks.Exiting...";
//     exit;
// }

if($niftyIndex == 'n50') {
    generateDailyDqANDDpReport($trade_date);
    pushFileForDownload($finalDailyListN50);
}
else if($niftyIndex == 'n100') { 
    generateDailyDqANDDpReportN100($trade_date);
    pushFileForDownload($finalDailyListN100);
}
else 
    echo "Please Select a Valid Nifty Index";

//echo "Completed!!.\n";
$conn->close();


?>