<?php
/*
File Name: bhav_copy_full_daily.php
Desc: Contains all functions related to full bhavcopy daily parsing 
*/

/* Downlad the bhav copy for a given date and insert function to insert in to DB and process for for candle type
returns 0 if not found
*/
function downloadAndProcessFullBhavCopyReport($curDate,  $nIndex) {

    $isFileDownload = downloadFullBhavCopyReport($curDate,  $nIndex);

    if($isFileDownload) {

        $nIndexArray = getNiftyIndexStocks($nIndex);
        processFullBhavCopyReportForADay($nIndexArray, $curDate);
    }
    
}


/* Downlad the bhav copy for a given date and cann insert function to insert in to DB
returns 0 if not found
*/
function downloadFullBhavCopyReport($curDate,  $nIndex) {

    $dateFormatUrl = date('dmY', strtotime($curDate));
    $dateFormatFile = date('Y-M-d', strtotime($curDate));

    $dayWiseDataFileName = "NSE-Data/Full-dowload-report/history1/".$dateFormatFile.".csv";

    $fullDataDownloadURL = "https://archives.nseindia.com/products/content/sec_bhavdata_full_".$dateFormatUrl.".csv";
    //echo "\n".$fullDataDownloadURL;

    if (file_exists($dayWiseDataFileName)) {
        unlink($dayWiseDataFileName);
    }

    $fileDownload = downloadFile($dayWiseDataFileName, $fullDataDownloadURL);

    if(!$fileDownload) {
        echo "\n".$fullDataDownloadURL." -   Not able to download";
        exit;
    } else { // insert in to DB if download successfull
        insertFullBhavCopyToDB($dayWiseDataFileName, $nIndex);
    }

    return $fileDownload;
    
}


/* insert the ful bhav copy report to DB
returns 0 if not found
*/

function insertFullBhavCopyToDB($bhavDataFullfileName, $nIndex) {

    $conn = $GLOBALS['conn'];

    $nIndexArray = getNiftyIndexStocks($nIndex);

    //print_r($nIndexArray);

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
        if(!in_array($getData[0], $nIndexArray) ) {
            continue;
        }

        $trade_date = date("Y-m-d", strtotime($getData[2]));

        //echo $trade_date."\n";
        
        $sql = "INSERT INTO security_vol_devlivery_day_wise  (symbol, series, `trading_date`, prev_close, open_price, high_price, low_price, last_price, close_price, avg_price, total_trade_quantity, turn_over, no_of_trade, deliverable_qty, delivery_percentage) 
        VALUES 
        ('".$getData[0]."','".$getData[1]."','".$trade_date."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."','".$getData[8]."','".$getData[9]."','".$getData[10]."','".$getData[11]."','".$getData[12]."','".$getData[13]."','".$getData[14]."')";
        

        //echo $sql;
        $result = mysqli_query($conn, $sql);
        //exit;

        if($result)
        $i++;
        
    }

    return $i;
}


function compareCurrAndPrevDayDataAndUpdateDB($currTradingDayData,  $prevTradingDayData, $symbol) {

    $conn = $GLOBALS['conn'];
    // echo "\n Curr Day: ".$currTradingDayData['trading_date']." :: Prev Day: ".$prevTradingDayData['trading_date']." symbol  $symbol\n";
    // echo "\n curr TTQ- ".$currTradingDayData['total_trade_quantity']." prev TTQ- ".$prevTradingDayData['total_trade_quantity']."\n";

    $volRratio = formatNumber($currTradingDayData['total_trade_quantity'] / $prevTradingDayData['total_trade_quantity'], 2);
    $deliveryRratio = formatNumber($currTradingDayData['deliverable_qty'] / $prevTradingDayData['deliverable_qty'], 2);

    $isDpIncrease =  0;
    if($currTradingDayData['delivery_percentage'] > $prevTradingDayData['delivery_percentage'])
    $isDpIncrease =  1;

    $candleColor = 'black';
    if($currTradingDayData['close_price'] > $currTradingDayData['open_price'])
    $candleColor = 'green';
    else if($currTradingDayData['close_price'] < $currTradingDayData['open_price'])
    $candleColor = 'red';

    $priceChangePercentageClosedBased = formatNumber((($currTradingDayData['close_price'] - $prevTradingDayData['close_price'])/$prevTradingDayData['close_price']) * 100, 2);

    $priceChangePercentageOpenBased =  formatNumber((($currTradingDayData['last_price'] - $prevTradingDayData['last_price'])/$prevTradingDayData['last_price']) * 100, 2);

    $isPriceInside = checkIfBodyInside($prevTradingDayData, $currTradingDayData); 

    $currTradingDay = $currTradingDayData['trading_date'];

    $sql = "UPDATE  security_vol_devlivery_day_wise  SET 
            vol_ratio = $volRratio,
            delivery_ratio = $deliveryRratio,
            is_dp_increase = $isDpIncrease,
            candle_type = $isPriceInside,
            candle_color = '$candleColor',
            price_change_percentage = $priceChangePercentageClosedBased,
            pre_open_price_percentage = $priceChangePercentageOpenBased
            WHERE trading_date= '$currTradingDay'
            AND  symbol = '$symbol'";
    

    //echo $sql;
    $result = mysqli_query($conn, $sql);
    //exit;

    // if($result)
    // $i++;
}


/* function used to process the day end data in security_vol_devlivery_day_wise table after raw data uploaded to table
$symbolArray : ['infy', 'hdfc']
$dateRangeArray : ['startdate' => 2020-08-05", 'endDate' => "2020-09-05",]
*/

function processFullBhavCopyReportInDateRange($symbolArray, $dateRangeArray) {

    $conn = $GLOBALS['conn'];

    $dataInDateRange = getAllTradingDaysInDateRange($dateRangeArray);

    //print_r($dataInDateRange);

    if(count($dataInDateRange) > 0) {

        foreach ($dataInDateRange as $tradeDate) {

            processFullBhavCopyReportForADay($symbolArray, $tradeDate);
            
        }
        
    }
}


/* function used to process bhav copy data for a day 
$symbolArray : ['infy', 'hdfc']
$curDate : 2020-08-23
*/
function processFullBhavCopyReportForADay($symbolArray, $curDate) {

    $conn = $GLOBALS['conn'];

    if(count($symbolArray) > 0) { 
        foreach ($symbolArray as $symbol) {

            //echo "\n Updating for : ".$symbol." time :".date('H:i:s.')."\n";

            // get prev trading session date
            // Hard coded to infy, since infy has all data from jan, 2018, so that a exact prev trading session can be found 
            $prevTradingDate = getPrevTradingDate("INFY", $curDate);

            if($prevTradingDate == '') {
                echo "\n Prev session day  not found ".$prevTradingDate." for  $symbol\n";
                continue;
            }

            // insert_prev_session_date($curDate, $prevTradingDate);
            // continue;

            // this below clause required when running a whole year and prev_session_ddate table is updated
            // if(!validate_prev_session_date($curDate, $prevTradingDate)){
            //     echo "\n Prev session day validation failed for date ".$curDate." and symbol  $symbol\n";
            //     exit;
            // }

            //echo "\n Curr Day: ".$curDate." :: Prev Day: ".$prevTradingDate." symbol  $symbol\n";

            $currTradingDayData = getDataByDateAndSymbolNew($symbol, $curDate);

            if($currTradingDayData == '') {
                echo "\n Current day Data not found : ".$curDate." for $symbol\n";
                continue;
            }

            //print_r($currTradingDayData);

            // get prev trading day if exists in DB
            if($prevTradingDate != '') {

                //echo "\n Updating for Date : ".$curDate." :: Prev Trade Date: ".$prevTradingDate." \n";

                $prevTradingDayData = getDataByDateAndSymbolNew($symbol, $prevTradingDate);

                //if prev day data present, then compare today with Prev day and update DB
                if($prevTradingDayData != '')                    
                compareCurrAndPrevDayDataAndUpdateDB($currTradingDayData,  $prevTradingDayData, $symbol);
                else 
                echo "\n Prev day data not found : ".$prevTradingDate." for $symbol\n";

            }
        }
        
    }
}