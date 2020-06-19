<?php
include("functions.php");
// $fileName = $argv[1];
// // echo "\nTradeDate :: ".$tradeDate."\n";
// //exit;
$host_name = "127.0.0.1";
$db_username = "root";
$db_password = "";
$db_name = "my_demo";
$conn = new mysqli($host_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("ERROR: Unable to connect: " . $conn->connect_error);
} 
echo "\n Connected to the database.\n";

$sqlGetTodayData = "SELECT * FROM `daily_security_archive` ORDER BY `id` ASC";

//echo $sqlGetTodayData;
$res  = $conn->query($sqlGetTodayData);

$dayInstrumentRecordArray = [];
$instrumentArray = [];
$i = 0;

while($row = mysqli_fetch_assoc($res)) {   

    //print_r($row);
    
    $currentDayData  = $row;
    $symbol = $row['symbol'];

    //echo "\nsymbol:: ".$symbol."\n";

    $prevTradingDate = getPrevTradingDate($symbol, $row['trading_date']); // Getting prev trading date
    //echo "\n Current Trading Day:". $row['trading_date']." :: Prev trading Day:". $prevTradingDate."\n";

    if($prevTradingDate == '') {
        echo "\n No Data Available before::". $row['trading_date']."\n";
        continue;
    }

    $prevDayData = getDataByDateAndSymbol($symbol, $prevTradingDate); // Getting prev trading date data

    //print_r($prevDayData);

    // check for current date DQ & DP is higher
    $highDelivaryAndPercentageFlag = checkIfHighDeliveryAndPercentage($prevDayData, $currentDayData); 

    if($highDelivaryAndPercentageFlag) {

        // check if the candled body inside
        $bodyInsideFlag = checkIfBodyInside($prevDayData, $currentDayData);

        // if candle body insdie, check if the complete candle inside
        if($bodyInsideFlag) {
            $completeCandleInsideFlag = checkIfCompleteCandleInside($prevDayData, $currentDayData);

            insertDelivaryReport($prevDayData, $currentDayData, $completeCandleInsideFlag);
            echo "\n $symbol:: ".$row['trading_date'];
            
            if($completeCandleInsideFlag) {
                echo " :: Complete Candle Inside "."\n";                    
            }

            $i++;
        }

        
    }


    //break;
}








echo "Completed!!.\n";
echo "Total record found: $i\n";
$conn->close();


?>