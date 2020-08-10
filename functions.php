<?php

function getPrevTradingDate($symbol, $currentDate) {
    
    //print_r($instrumentSingleItemArray); 9912889487  8520027059
    $prevTradingDate = '';
    $dbConn = $GLOBALS['conn'];

    // SELECT STR_TO_DATE(`trading_date`, '%Y-%m-%d') FROM `daily_security_archive` 
    // WHERE  STR_TO_DATE(`trading_date`, '%Y-%m-%d') < STR_TO_DATE('2020-05-13', '%Y-%m-%d') AND 
    // symbol = 'INFY' 
    // ORDER BY `id` DESC LIMIT 1


    // SELECT STR_TO_DATE(`trading_date`, '%Y-%m-%d') FROM `daily_security_archive` 
    // WHERE  UNIX_TIMESTAMP(`trading_date`) < UNIX_TIMESTAMP('2020-01-03') AND 
    // symbol = 'INFY' 
    // ORDER BY `id` DESC LIMIT 1
    
    // $currentDate = '2020-05-14';
    // $symbol = '2020-05-14'; 
    $sqlGetAllPrevDateDate = "SELECT * FROM `daily_security_archive` 
                                WHERE  `trading_date` < '$currentDate' AND 
                                symbol = '$symbol' 
                                ORDER BY `trading_date` DESC LIMIT 1";

    //echo $sqlGetAllPrevDateDate;

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    while($row = mysqli_fetch_assoc($res)) {

        $prevTradingDate = $row['trading_date'];

    }

    return $prevTradingDate;

}

/*
Desc: returns all the  trading days in between a date range, it consider Infy as symbol
$dateRange: An array containing both start date and end date like: ['startDate' => '2020-08-05', 'endDate' => '2020-09-05']
*/
function getAllTradingDaysInDateRange($dateRange) {
    
    
    $datesInDateRange = [];
    $dbConn = $GLOBALS['conn'];

    $symbol = "INFY";

    $sqlGetAllPrevDateDate = "SELECT trading_date FROM `daily_security_archive` 
                                WHERE  `trading_date` <= '".$dateRange['endDate']."' AND 
                                `trading_date` >= '".$dateRange['startDate']."' AND
                                symbol = '$symbol' 
                                ORDER BY `trading_date` ASC";


    //echo $sqlGetAllPrevDateDate;

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    $highPrice = 0;
    $lowPrice = 0;
    $totalVol = 0;

    while($row = mysqli_fetch_assoc($res)) {

        $datesInDateRange[] = $row['trading_date'];
    }
    return $datesInDateRange;
}

/*
$symbol: symbol like Infy, hdfc
$dateRange: An array containing both start date and end date like: ['startDate' => '2020-08-05', 'endDate' => '2020-09-05']
*/
function getDataInDateRange($symbol, $dateRange) {
    
    
    $dateInDateRange = [];
    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "SELECT * FROM `daily_security_archive` 
                                WHERE  `trading_date` <= '".$dateRange['endDate']."' AND 
                                `trading_date` >= '".$dateRange['startDate']."' AND
                                symbol = '$symbol' 
                                ORDER BY `trading_date` ASC";

    // if($symbol == 'COALINDIA')
    //echo $sqlGetAllPrevDateDate;

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    $highPrice = 0;
    $lowPrice = 0;
    $totalVol = 0;

    while($row = mysqli_fetch_assoc($res)) {

        if($row['high_price'] >= $highPrice)
            $highPrice = $row['high_price'];
        if(($row['low_price'] <= $lowPrice) || ($lowPrice == 0))
            $lowPrice = $row['low_price'];
        
        $totalVol +=  $row['total_trade_quantity']; 
        
        $dateInDateRange['last_price'] = $row['last_price'];

            

    }
    $dateInDateRange['high_price'] = $highPrice;
    $dateInDateRange['low_price'] = $lowPrice;

    $dateInDateRange['total_vol'] = $totalVol;


    return $dateInDateRange;
}


function getAvgDeliveryPercentage($symbol, $currentDate, $avgNoOfDay) {
    
    //print_r($instrumentSingleItemArray); 9912889487  8520027059
    $avgDeliveryPercentage = 0;
    $totalDeliveryPercentage = 0;
    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "SELECT  delivery_percentage AS avgDeliveryPercentage  FROM `daily_security_archive` 
                                WHERE  `trading_date` <= '$currentDate' AND 
                                symbol = '$symbol' 
                                ORDER BY `trading_date` DESC LIMIT $avgNoOfDay";

    //echo $sqlGetAllPrevDateDate;

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    while($row = mysqli_fetch_assoc($res)) {   

        $totalDeliveryPercentage += $row['avgDeliveryPercentage'];

    }

    if($totalDeliveryPercentage > 0)
    $avgDeliveryPercentage = $totalDeliveryPercentage/$avgNoOfDay;

    return $avgDeliveryPercentage;

}

function getDataByDateAndSymbol($symbol, $currentDate) {
    
    $todaydata = '';
    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "SELECT * FROM `daily_security_archive` 
                            WHERE  `trading_date` = '$currentDate' AND  symbol = '$symbol'";

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    //echo $sqlGetAllPrevDateDate;

    while($row = mysqli_fetch_assoc($res)) {   

        $todaydata = $row;

    }

    return $todaydata;

}

function getDataByDateAndSymbolNew($symbol, $currentDate) {
    
    $todaydata = '';
    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "SELECT * FROM `security_vol_devlivery_day_wise` 
                            WHERE  `trading_date` = '$currentDate' AND  symbol = '$symbol'";

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    //echo $sqlGetAllPrevDateDate;

    while($row = mysqli_fetch_assoc($res)) {   

        $todaydata = $row;

    }

    return $todaydata;

}

function checkIfHighDeliveryAndPercentage($parentData, $childData) {

    $highDeliveryWithPercentage = 0;
    
    //print_r($childData);

    if(($childData['deliverable_qty'] > $parentData['deliverable_qty']) &&
         ($childData['delivery_percentage'] > $parentData['delivery_percentage']) && 
         ($childData['total_trade_quantity'] >= $parentData['total_trade_quantity']) ) {
            $highDeliveryWithPercentage = 1;

    }

    return $highDeliveryWithPercentage;
}

// it checks deliveru percentage more then 50 with delivary quantity and vol breakout
function checkIfHighDeliveryPercentage($parentData, $childData) {

    $highDeliveryPercentage = 0;
    
    //print_r($childData);

    if(($childData['deliverable_qty'] > $parentData['deliverable_qty']) &&
         ($childData['delivery_percentage'] > 50) && 
         ($childData['total_trade_quantity'] > $parentData['total_trade_quantity']) ) {
            $highDeliveryPercentage = 1;

    }

    return $highDeliveryPercentage;
}


function checkIfBodyInside($parentData, $childData) {
    
    $bodyInside = 0;

    if((($childData['open_price'] >= $parentData['low_price']) && ($childData['open_price'] <= $parentData['high_price'])) &&
    (($childData['last_price'] >= $parentData['low_price']) && ($childData['last_price'] <= $parentData['high_price'])) ) {
            $bodyInside = 1;

    }

    return $bodyInside;
}

function checkIfLastTradePriceInside($parentData, $childData) {

}

function checkIfCompleteCandleInside($parentData, $childData) {

    $completeCandleInside = 0;

    if((($childData['high_price'] >= $parentData['low_price']) && ($childData['high_price'] <= $parentData['high_price'])) &&
    (($childData['low_price'] >= $parentData['low_price']) && ($childData['low_price'] <= $parentData['high_price'])) ) {
            $completeCandleInside = 1;

    }

    return $completeCandleInside;
}

function makeIntraDayHypo($prevDateTradeData, $currDateTradeData) {
    
    $nextDayHypo = "N/A";

    if( ($currDateTradeData['high_price'] >= $prevDateTradeData['high_price']) 
        && ($currDateTradeData['last_price'] <= $prevDateTradeData['high_price']) 
        && ($currDateTradeData['low_price'] >= $prevDateTradeData['low_price']) ) {
            $nextDayHypo = "Sell";

    } else if( ($currDateTradeData['low_price'] <= $prevDateTradeData['low_price']) 
        && ($currDateTradeData['last_price'] >= $prevDateTradeData['low_price']) 
        && ($currDateTradeData['high_price'] <= $prevDateTradeData['high_price']) ) {
            $nextDayHypo = "Buy";

    }

    return $nextDayHypo;
}

function insertDelivaryReport($parentData, $childData, $completeCandleInsideFlag ) {
    
    //print_r($instrumentSingleItemArray);
    $prevTradingDate = '';

    $DQ__increase_percentage =  (($childData['deliverable_qty'] - $parentData['deliverable_qty'])*100)/$parentData['deliverable_qty'];

    $dbConn = $GLOBALS['conn'];

    // $currentDate = '2020-05-14';
    // $symbol = '2020-05-14'; 
    $insertDelivaryReportSql = "INSERT INTO delivary_report SET 
                                symbol = '".$childData['symbol']."',"."
                                current_trading_date = '".$childData['trading_date']."',"."
                                prev_trading_date = '".$parentData['trading_date']."',"."
                                current_vol = '".$childData['total_trade_quantity']."',"."
                                prev_vol = '".$parentData['total_trade_quantity']."',"."
                                current_DQ = '".$childData['deliverable_qty']."',"."
                                prev_DQ = '".$parentData['deliverable_qty']."',"."
                                insrease_DQ_percantage = '".$DQ__increase_percentage."',"."
                                current_DP = '".$childData['delivery_percentage']."',"."
                                prev_DP = '".$parentData['delivery_percentage']."',"."
                                is_complete_candle_inside = $completeCandleInsideFlag";

    //echo $insertDelivaryReportSql;

    $res = $dbConn->query($insertDelivaryReportSql);

    if ( $res === false ){
        echo mysqli_error($dbConn) . "\n";
        exit;
    }
}



function generateDailyDqANDDpReport($tradeDate) {

    $dbConn = $GLOBALS['conn'];

    $final_csv_file = $GLOBALS['finalDailyListN50'];
    $dpAvgDays = $GLOBALS['dpAvgDays'];

    //echo $final_csv_file;

    // to get prev trading date. symbol cane be any, i passed 'infy'
    $prevTradeDate = getPrevTradingDate("infy", $tradeDate);

    //echo "\nPrev trading date: ".$prevTradeDate." \n";

    $getNse50Sql = "SELECT symbol, sector, nse_index FROM nse_index  where nse_index = 'n50'";
    $getNse50Res = mysqli_query($dbConn, $getNse50Sql);

    while($row = mysqli_fetch_assoc($getNse50Res)) {   

        $symbol = $row['symbol'];
        $sector = $row['sector'];
        $prevDateTradeData = getDataByDateAndSymbol($symbol, $prevTradeDate);
        $currDateTradeData = getDataByDateAndSymbol($symbol, $tradeDate);

        $hypoForNextDay = makeIntraDayHypo($prevDateTradeData, $currDateTradeData);

        $rangeActivity = generateRangeComment($symbol, $tradeDate);

        if($prevDateTradeData == "") {
            echo "No Prev date record present for Stock ".$symbol." for date ".$tradeDate."  exiting....\n" ;
            exit;
        }

        // check for current date DQ & DP is higher
        $highDelivaryAndPercentageFlag = checkIfHighDeliveryAndPercentage($prevDateTradeData, $currDateTradeData); 

        if($highDelivaryAndPercentageFlag) {

            // check if the candled body inside
            $bodyInsideFlag = checkIfBodyInside($prevDateTradeData, $currDateTradeData);

            // To Get Avg delivery percentage for the stock for last 10 days
            $avgDeliveryPercentage = getAvgDeliveryPercentage($symbol, $tradeDate, $dpAvgDays);

            $tradeDateExcel = date('d/m/y', strtotime($tradeDate));
            $csvData[$sector][] = array($symbol, $tradeDateExcel, $currDateTradeData['open_price'], $currDateTradeData['last_price'], $prevDateTradeData['deliverable_qty'], $currDateTradeData['deliverable_qty'], (round($currDateTradeData['deliverable_qty']/$prevDateTradeData['deliverable_qty'], 2)),  $prevDateTradeData['delivery_percentage'], $currDateTradeData['delivery_percentage'], $avgDeliveryPercentage, $bodyInsideFlag, $hypoForNextDay, $rangeActivity);
            
        }


    }

    // print_r($csvData);
    // exit;

    $headerArray = ["Symbol", "Date",  "Open",	"Last",	"Prev DQ",	"Current DQ",	"Increase in DQ", "Prev DP",	"Current DP",	"Avg Per", "Inside", "S.A", "R.A", "Result", "Comment"];

    if (file_exists($final_csv_file)) {
        unlink($final_csv_file);
    }
    
    $fp = fopen($final_csv_file, 'wb');

    fputcsv($fp, $headerArray, ',');

    foreach ($csvData as $key => $csvDataSector) {

        $sectorName = [$key];
        fputcsv($fp, [], ',');
        fputcsv($fp, $sectorName, ',');
        

        foreach ($csvDataSector as $line) {
            
            //print_r($line);
            fputcsv($fp, $line, ',');
        }
    }

    fclose($fp);
    
}

// Generate Daily report for Nifty 100 stocks
function generateDailyDqANDDpReportN100($tradeDate) {

    $dbConn = $GLOBALS['conn'];

    $final_csv_file = $GLOBALS['finalDailyListN100'];
    $dpAvgDays = $GLOBALS['dpAvgDays'];

    // to get prev trading date. symbol cane be any, i passed 'infy'
    $prevTradeDate = getPrevTradingDate("infy", $tradeDate);

    //echo "\nPrev trading date: ".$prevTradeDate." \n";

    $getNse50Sql = "SELECT symbol, sector, nse_index  FROM nse_index  where nse_index = 'n50' OR nse_index = 'n100'";
    $getNse50Res = mysqli_query($dbConn, $getNse50Sql);

    while($row = mysqli_fetch_assoc($getNse50Res)) {   

        $symbol = $row['symbol'];
        $nse_index = $row['nse_index'];
        $sector = $row['sector'];
        $prevDateTradeData = getDataByDateAndSymbol($symbol, $prevTradeDate);
        $currDateTradeData = getDataByDateAndSymbol($symbol, $tradeDate);

        $hypoForNextDay = makeIntraDayHypo($prevDateTradeData, $currDateTradeData);
        $rangeActivity = generateRangeComment($symbol, $tradeDate);

        if($prevDateTradeData == "") {
            echo "No Prev date record present for Stock ".$symbol." for date ".$tradeDate."  exiting....\n" ;
            exit;
        }

        // check for current date DQ & DP is higher
        $highDelivaryAndPercentageFlag = checkIfHighDeliveryPercentage($prevDateTradeData, $currDateTradeData); 

        if($highDelivaryAndPercentageFlag) {

            // check if the candled body inside
            $bodyInsideFlag = checkIfBodyInside($prevDateTradeData, $currDateTradeData);

            // To Get Avg delivery percentage for the stock for last 10 days
            $avgDeliveryPercentage = getAvgDeliveryPercentage($symbol, $tradeDate, $dpAvgDays);
            $tradeDateExcel = date('d/m/y', strtotime($tradeDate));
            $csvData[$sector][] = array($symbol, $nse_index, $tradeDateExcel, $currDateTradeData['open_price'], $currDateTradeData['last_price'], $prevDateTradeData['deliverable_qty'], $currDateTradeData['deliverable_qty'], (round($currDateTradeData['deliverable_qty']/$prevDateTradeData['deliverable_qty'], 2)),  $prevDateTradeData['delivery_percentage'], $currDateTradeData['delivery_percentage'], $avgDeliveryPercentage, $bodyInsideFlag, $hypoForNextDay, $rangeActivity);
            
        }


    }

    // print_r($csvData);
    // exit;

    $headerArray = ["Symbol", "Index", "Date",	"Open", "Last",	"Prev DQ",	"Current DQ",	"Increase in DQ", "Prev DP",	"Current DP", "Avg Per", "Inside", "S.A", "R.A", "Result", "Comment"];

    if (file_exists($final_csv_file)) {
        unlink($final_csv_file);
    }


    $fp = fopen($final_csv_file, 'wb');

    fputcsv($fp, $headerArray, ',');

    foreach ($csvData as $key => $csvDataSector) {

        $sectorName = [$key];
        fputcsv($fp, [], ',');
        fputcsv($fp, $sectorName, ',');
        

        foreach ($csvDataSector as $line) {
            
            //print_r($line);
            fputcsv($fp, $line, ',');
        }
    }

    fclose($fp);
    
}


function insertDelivaryBreakoutInsideBar($parentData, $completeCandleInsideFlag, $nseIndex ) {
    
    //print_r($instrumentSingleItemArray);
    $prevTradingDate = '';

    $DQ__increase_percentage =  (($childData['deliverable_qty'] - $parentData['deliverable_qty'])*100)/$parentData['deliverable_qty'];

    $dbConn = $GLOBALS['conn'];

    // $currentDate = '2020-05-14';
    // $symbol = '2020-05-14'; 
    $insertDelivaryReportSql = "INSERT INTO delivary_report SET 
                                symbol = '".$childData['symbol']."',"."
                                current_trading_date = '".$childData['trading_date']."',"."
                                prev_trading_date = '".$parentData['trading_date']."',"."
                                current_vol = '".$childData['total_trade_quantity']."',"."
                                prev_vol = '".$parentData['total_trade_quantity']."',"."
                                current_DQ = '".$childData['deliverable_qty']."',"."
                                prev_DQ = '".$parentData['deliverable_qty']."',"."
                                insrease_DQ_percantage = '".$DQ__increase_percentage."',"."
                                current_DP = '".$childData['delivery_percentage']."',"."
                                prev_DP = '".$parentData['delivery_percentage']."',"."
                                is_complete_candle_inside = $completeCandleInsideFlag";

    //echo $insertDelivaryReportSql;

    $res = $dbConn->query($insertDelivaryReportSql);

    if ( $res === false ){
        echo mysqli_error($dbConn) . "\n";
        exit;
    }
} 

function downloadFile($fileName, $url) {

    $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n")); 
    //Basically adding headers to the request
    $context = stream_context_create($opts);
    
    if(file_put_contents($fileName, file_get_contents($url, false, $context))) { 
        return 1;
    } 
    else { 
        return 0;
    }
}

function pushFileForDownload($filePath) {

    if(file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        flush(); // Flush system output buffer
        readfile($filePath);
        die();
    } else {
        http_response_code(404);
        die();
    }
}

/*
Desc: It generates CVS file from an array.
Inputs:
Headerarray - coloumns of the csv file
dataArray - data in array
FileName - File to write data into
*/
function generateCSVFileFromArray($headerArray, $dataArray, $fileName) {
    
    if (file_exists($fileName)) {
        unlink($fileName);
    }
    
    $fp = fopen($fileName, 'wb');

    fputcsv($fp, $headerArray, ',');

    foreach ($dataArray as $key => $line) {
        // echo "\n";
        // print_r($line);
        // echo "\n";
        fputcsv($fp, $line, ',');
    }

    fclose($fp);
}

function getLastWeekDateRange($curDate) {

    $dateRange = ["startDate" => $curDate, "endDate" => $curDate];
    
    //echo date("2020-06-15", strtotime('last week'));

    //print date('Y-m-d', strtotime('sunday', strtotime("last week  $curDate")));

    $dateRange['startDate'] = date('Y-m-d', strtotime('monday', strtotime("last week  $curDate")));

    $dateRange['endDate'] =  date('Y-m-d', strtotime('sunday', strtotime("last week  $curDate")));
    
    // echo  "\n";
    // print_r($dateRange);

    return $dateRange;
}

function getCurrentWeekDateRange($curDate) {

    $dateRange = ["startDate" => $curDate, "endDate" => $curDate];
    
    //echo date("2020-06-15", strtotime('last week'));

    //print date('Y-m-d', strtotime('sunday', strtotime("last week  $curDate")));
    //$curDate = "2020-07-12";
    $dateRange['startDate'] = date('Y-m-d', strtotime(' last monday', strtotime($curDate)));

    $dateRange['endDate'] =  date('Y-m-d', strtotime(' last friday', strtotime($curDate)));
    
    // echo  "\n";
    // print_r($dateRange);
    return $dateRange;
}


function getLastMonthDateRange($curDate) {

    $dateRange = ["startDate" => $curDate, "endDate" => $curDate];
    
    //echo date("2020-06-15", strtotime('last week'));

    //print date('Y-m-d', strtotime("last day of last month  2020-05-25"));

    $dateRange['startDate'] = date('Y-m-d', strtotime("first day of last month  $curDate"));

    $dateRange['endDate'] =  date('Y-m-d', strtotime("last day of last month  $curDate"));
    
    // echo  "\n";
    // print_r($dateRange);

    return $dateRange;
}

//date passed are in fromate yyyy-mm-dd
function isWeekend($date) {
    $weekendFlag = 0;
    $weekendFlag = (date('N', strtotime($date)) >= 6)?1:0;
    return $weekendFlag;
}

//date passed are in fromate yyyy-mm-dd
function isNseHoliday($date) {

    // convert to mm/dd/yy - 01/07/20
    $convertDate = date('m/d/y', strtotime($date));
    
    $dbConn = $GLOBALS['conn'];

    $getHolidaySql = "SELECT * FROM holiday_list_nse WHERE holiday_date = '$convertDate'";

    //echo $getHolidaySql;

    $res = $dbConn->query($getHolidaySql);
    //while($row = mysqli_fetch_assoc($res)) {   

    if ( $res && (mysqli_num_rows($res) > 0 )) {
        return 1;
    }
   
    return 0;
}

function dateDisplay($date) {
    return date('d/m/y', strtotime($date));
}

function dateCompare($startDate, $endDate) {
    
    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);

    if($endTime > $startTime)
    return 1;

    return 0;
}

function checkValidDate($date) {
    $tempDate = explode('-', $date);
    // checkdate(month, day, year)
    return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
  }


function generateRangeComment($symbol, $tradeDate) {

    $ranegActivity = "";

    $currDateTradeData = getDataByDateAndSymbol($symbol, $tradeDate);

    /* Get weekly Range Activity */
    $lastWeekRange = getLastWeekDateRange($tradeDate);

    $lastWeekRangeData = getDataInDateRange($symbol, $lastWeekRange);

    if(($currDateTradeData['high_price'] >= $lastWeekRangeData['high_price']) && 
        ($currDateTradeData['last_price'] <= $lastWeekRangeData['high_price'])) {
        $ranegActivity  .= "Rejected From Last Week High."; 
    }

    if(($currDateTradeData['low_price'] <= $lastWeekRangeData['low_price']) && 
        ($currDateTradeData['last_price'] >= $lastWeekRangeData['low_price'])) {
        $ranegActivity  .= "Supported From Last Week Low."; 
    }


    /* Get weekly Range Activity */
    $lastMonthRange = getLastMonthDateRange($tradeDate);

    $lastMonthRangeData = getDataInDateRange($symbol, $lastMonthRange);

    // if($symbol == "COALINDIA")
    // print_r($lastMonthRangeData);

    if(($currDateTradeData['high_price'] >= $lastMonthRangeData['high_price']) && 
        ($currDateTradeData['last_price'] <= $lastMonthRangeData['high_price'])) {
        $ranegActivity  .= "Rejected From Last Month High."; 
    }

    if(($currDateTradeData['low_price'] <= $lastMonthRangeData['low_price']) && 
        ($currDateTradeData['last_price'] >= $lastMonthRangeData['low_price'])) {
        $ranegActivity  .= "Supported From Last Month Low."; 
    }

    return $ranegActivity;
}




function generateWeekVolGainer($curDate) {
    
    //$curDate = "2020-07-12";

    $isWeekEnd  = isWeekend($curDate);

    $lastWeekRange = $isWeekEnd ? getCurrentWeekDateRange($curDate) : getLastWeekDateRange($curDate);

    $lastTwoWeekRange = getLastWeekDateRange($lastWeekRange['startDate']);


    // $lastWeekData = getDataInDateRange('Infy', $lastWeekRange);
    // $lastTwoWeekData = getDataInDateRange('Infy', $lastTwoWeekRange);

    $niftyIndexStocks = getNiftyIndexStocks('n50');

    $weekActivityReport = [];
    $i = 0;
    foreach($niftyIndexStocks as $stock) {

        // Get latest week data
        $currentWeekData = getDataInDateRange($stock, $lastWeekRange);

        // Get prev week data
        $lastWeekData = getDataInDateRange($stock, $lastTwoWeekRange);  

        $weekActivityReport[$stock]['symbol'] = $stock;
        $weekActivityReport[$stock]['date'] = dateDisplay(date("Y-m-d"));
        
        $weekActivityReport[$stock]['Prev_vol'] = formatNumber($lastWeekData['total_vol'], '');
        $weekActivityReport[$stock]['current_vol'] = formatNumber($currentWeekData['total_vol'], '');
        
        $weekActivityReport[$stock]['vol_ratio'] = formatNumber($currentWeekData['total_vol']/ $lastWeekData['total_vol'], 2);
        $weekActivityReport[$stock]['percentage_change_in_price'] = formatNumber((($currentWeekData['last_price'] - $lastWeekData['last_price'])/$lastWeekData['last_price']) * 100, 2);

        
        $weekActivityReport[$stock]['prev_LTP'] = formatNumber($lastWeekData['last_price'], 2);
        $weekActivityReport[$stock]['curr_LTP'] = formatNumber($currentWeekData['last_price'], 2);

        $weekActivityReport[$stock]['high_price'] = formatNumber($currentWeekData['high_price'], 2);
        $weekActivityReport[$stock]['low_price'] = formatNumber($currentWeekData['low_price'], 2);

        // $i++;
        // if($i > 1)
        // break;
    }

    $headerArray = ["Symbol", "Date",  "Prev Vol",	"Curr Vol",	"Ratio", "% Price", "Prev LTP", "Curr LTP", "High", "Low"];
    
    $fileName = $GLOBALS['weekVolReport'];

    generateCSVFileFromArray($headerArray, $weekActivityReport, $fileName);
    
    // echo  "\n";
    // print_r($lastWeekRange);
    // print_r($lastTwoWeekRange);
    //return $dateRange;
} 

// return an array having all stock symbols for an Index, n50, n100, n200, n500
function getNiftyIndexStocks($nIndex) {
    
    $dbConn = $GLOBALS['conn'];

    if($nIndex == 'n50') {
        $nIndex = " nse_index = 'n50'";
    } else if($nIndex == 'n100') {
        $nIndex = " nse_index = 'n50' OR nse_index = 'n100' ";
    } else if($nIndex == 'n200')  {
        $nIndex = " nse_index = 'n50' OR nse_index = 'n100' OR nse_index = 'n200' ";
    } else if($nIndex == 'n500')  {
        $nIndex = " nse_index = 'n50' OR nse_index = 'n100' OR nse_index = 'n200' OR nse_index = 'n500'  ";
    }

    $getNse50Sql = "SELECT symbol  FROM nse_index where $nIndex";
    
    //echo $getNse50Sql;

    $getNse50Res =mysqli_query($dbConn, $getNse50Sql);

    //$getNse50Row =  mysqli_fetch_assoc($getNse50Res);

    while($getNse50Row = mysqli_fetch_assoc($getNse50Res)) {   

        $nseIndexArray[] = $getNse50Row['symbol'];

    }

    //echo $getNse50Row['nse50'];
    //$nseIndexArray = explode(',', $getNse50Row['nse50']);
    //print_r($nseIndexArray);

    return $nseIndexArray;
} 


function formatNumber($num, $prec) {
    //echo $num."------";
    // return number_format($num);
    
    if($prec == 0 || $prec == '')
        return number_format($num);
    else
        return number_format($num, $prec, '.', ',');

}

function updateFullBhavCopyToDB($bhavDataFullfileName, $nIndex) {

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


function insert_prev_session_date($currTradeDate, $prevTradeDate) {
    
    $conn = $GLOBALS['conn'];

    $sql = "INSERT INTO prev_trade_sessions SET 
            curr_session_date = '$currTradeDate',
            prev_session_date = '$prevTradeDate'";
    //echo $sql;
    $result = mysqli_query($conn, $sql);

}

function validate_prev_session_date($currTradeDate, $prevTradeDate) {
    
    $conn = $GLOBALS['conn'];

    //echo "\n Vdalidte curr day: $currTradeDate with Prev session day  not found ".$prevTradeDate." \n";

    $sql = "SELECT * FROM  prev_trade_sessions WHERE  curr_session_date = '$currTradeDate'";

    //echo $sql;
    $res = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($res)) {
        $prevTradeDateDB = $row['prev_session_date'];
    }

    if($prevTradeDateDB == $prevTradeDate) 
    return 1;

    return 0;

} 
/* function used to process the day end data in security_vol_devlivery_day_wise table after raw data uploaded to table
$symbolArray : ['infy', 'hdfc']
$dateRangeArray : ['startdate' => 2020-08-05", 'endDate' => "2020-09-05",]
*/

function processFullBhavCopyReport($symbolArray, $dateRangeArray) {

    $conn = $GLOBALS['conn'];

    $dataInDateRange = getAllTradingDaysInDateRange($dateRangeArray);

    //print_r($dataInDateRange);

    if(count($dataInDateRange) > 0) {

        foreach ($symbolArray as $symbol) {

            echo "\n Updating for : ".$symbol." time :".date('H:i:s.')."\n";

            foreach ($dataInDateRange as $tradeDate) {

                // get prev trading session date
                // Hard coded to infy, since infy has all data from jan, 2018, so that a exact prev trading session can be found 
                $prevTradingDate = getPrevTradingDate("INFY", $tradeDate);

                if($prevTradingDate == '') {
                    echo "\n Prev session day  not found ".$prevTradingDate." for  $symbol\n";
                    continue;
                }

                // insert_prev_session_date($tradeDate, $prevTradingDate);
                // continue;

                if(!validate_prev_session_date($tradeDate, $prevTradingDate)){
                    echo "\n Prev session day validation failed for date ".$tradeDate." and symbol  $symbol\n";
                    exit;
                }

                //echo "\n Curr Day: ".$tradeDate." :: Prev Day: ".$prevTradingDate." symbol  $symbol\n";

                $currTradingDayData = getDataByDateAndSymbolNew($symbol, $tradeDate);

                if($currTradingDayData == '') {
                    echo "\n Current daye Data not found : ".$tradeDate." for $symbol\n";
                    continue;
                }

                //print_r($currTradingDayData);

                // get prev trading day if exists in DB
                if($prevTradingDate != '') {

                    //echo "\n Updating for Date : ".$tradeDate." :: Prev Trade Date: ".$prevTradingDate." \n";

                    $prevTradingDayData = getDataByDateAndSymbolNew($symbol, $prevTradingDate);

                    //if prev day data present, then compare today with Prev day and update DB
                    if($prevTradingDayData != '')                    
                    compareCurrAndPrevDayDataAndUpdateDB($currTradingDayData,  $prevTradingDayData, $symbol);
                    else 
                    echo "\n Prev date data not found : ".$prevTradingDate." for $symbol\n";

                }
            }
        }
    }
}

function compareCurrAndPrevDayDataAndUpdateDB($currTradingDayData,  $prevTradingDayData, $symbol) {

    $conn = $GLOBALS['conn'];
    echo "\n Curr Day: ".$currTradingDayData['trading_date']." :: Prev Day: ".$prevTradingDayData['trading_date']." symbol  $symbol\n";
    echo "\n curr TTQ- ".$currTradingDayData['total_trade_quantity']." prev TTQ- ".$prevTradingDayData['total_trade_quantity']."\n";

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



/* Get pre open price for a symbol on given date
returns 0 if not found
*/
function getPreOpenPrice($symbol, $tradeDate) {
    $preOpenPrice = 0;
    $dbConn = $GLOBALS['conn'];

    $sqlGetPreOpenSql = "SELECT final_price as pre_open_price FROM `pre_open_data` 
                            WHERE  `trade_date` = '$tradeDate' AND  symbol = '$symbol'";

    // $sqlGetPreOpenSql = "SELECT open_price as pre_open_price FROM `security_vol_devlivery_day_wise` 
    //                 WHERE  `trading_date` = '$tradeDate' AND  symbol = '$symbol'";

    $res  = $dbConn->query($sqlGetPreOpenSql);

    //echo $sqlGetPreOpenSql;

    while($row = mysqli_fetch_assoc($res)) {   

        $preOpenPrice = $row['pre_open_price'];

    }

    return $preOpenPrice;
}

/*
Desc: Analyse pre-open data and update DB
date: 2020-08-15
inxed: like n200 or nfo 
*/
function analysePreOpenDate($tradeDate, $nIndex) {

    $nIndexArray = getNiftyIndexStocks($nIndex);

    //$nIndexArray = ['infy'];

    foreach ($nIndexArray as $symbol) {

        $preOpenPrice = getPreOpenPrice($symbol, $tradeDate);

        // if no pre open price found
        If($preOpenPrice == 0) {
            continue;
        }

        // get prev trading session date
        // Hard coded to infy, since infy has all data from jan, 2018, so that a exact prev trading session can be found 
        $prevTradingDate = getPrevTradingDate("INFY", $tradeDate);

        echo "\n Prev session day : ".$prevTradingDate."\n";

        // if last trading session not found
        If($prevTradingDate == '') {
            continue;
        }

        $prevTradingDayData = getDataByDateAndSymbolNew($symbol, $prevTradingDate);

        //if prev day data present, then compare today with Prev day and update DB
        if($prevTradingDayData == '')  {
            echo "\n Prev date data not found : ".$prevTradingDate."\n";
            continue;
        }

        $isInside = checkIfDayWasInside($symbol, $prevTradingDate);

        // skip if prev day is not an inside candle
        if(!$isInside)
        continue;

        $motherCandle = findRangeForInsdieCandle($symbol, $prevTradingDate);

        echo "\n Mother candle  : ".$motherCandle['trading_date']." for date $prevTradingDate evaluate on $tradeDate\n";

        // if mother candle found, validate if the range valid till today
        if(count($motherCandle)) {

            $ifRangeValid = validateRangeWithinDates($symbol, $prevTradingDate, $motherCandle);

            if($ifRangeValid) {
                
                echo "\n An Valid Range found with mother candle  : ".$motherCandle['trading_date']." evaluate on $tradeDate\n";

                $probableAction = '';
                if($preOpenPrice >= $motherCandle['high_price']) {
                    $probableAction = "buy";
                } else if($preOpenPrice <= $motherCandle['low_price']) {
                    $probableAction = "sell";
                }

                echo "\n Pre Open Price: ".$preOpenPrice." High Price:". $motherCandle['high_price']." Low Price:". $motherCandle['low_price']." Action: ".$probableAction."\n";

                if($probableAction == 'buy' || $probableAction == 'sell') {
                    insertPreOpenRangeBO($symbol, $tradeDate, $motherCandle, $preOpenPrice, 1, 1, $probableAction); 
                }              
            }
        }
     }
}

function insertPreOpenRangeBO($symbol, $tradeDate, $motherCandle, $tradeDateOpenPrice, $noOfDaysInRange, $noOfBoInRange, $probableAction) {

    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "INSERT INTO pre_open_range_BO
                            SET  symbol = '$symbol',
                            monther_candle_date = '".$motherCandle['trading_date']."',
                            trade_date = '$tradeDate',                           
                            mother_candle_high = '".$motherCandle['high_price']."',
                            mother_candle_low = '".$motherCandle['low_price']."',
                            trade_day_open_price  = '$tradeDateOpenPrice',
                            probable_action  = '$probableAction',
                            no_of_candle_in_range  = '$noOfDaysInRange',
                            no_BO_in_range = '$noOfBoInRange'";

    //echo $sqlGetAllPrevDateDate;
    $res  = $dbConn->query($sqlGetAllPrevDateDate);

}
/*
Desc: Check if the day is inside bar
date: 2020-08-15
symbol: infy, hdfc
*/
function checkIfDayWasInside($symbol, $tradeDate) {

    $isInside = 0;
    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "SELECT candle_type FROM `security_vol_devlivery_day_wise` 
                            WHERE  `trading_date` = '$tradeDate' 
                            AND  symbol = '$symbol'
                            AND candle_type = 1";

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    //echo $sqlGetAllPrevDateDate;

    while($row = mysqli_fetch_assoc($res)) {   

        $isInside = $row['candle_type'];

    }

    return $isInside;
    
}

/*
Desc: checks for range and mother candle, retuns data of mother candle
date: , this is the from which need to see all candle backward, format like, 2020-08-15
symbol: infy, hdfc
*/
function findRangeForInsdieCandle($symbol, $tradeDate) {

    $motherCandle = [];
    $dbConn = $GLOBALS['conn'];

    // getting all the records from todays date till the day where there was no inside
    $sqlGetAllPrevDateDate = "SELECT * FROM `security_vol_devlivery_day_wise` 
                                WHERE  `trading_date` < '$tradeDate' 
                                AND candle_type != 1
                                AND symbol = '$symbol' 
                                ORDER BY `trading_date` DESC LIMIT 1";

    //echo $sqlGetAllPrevDateDate;

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    while($row = mysqli_fetch_assoc($res)) {

        $motherCandle = $row;

    }

    return $motherCandle;

}


/*
Desc: Basically this checks if there was break out happened in between 
and also price never closed above high of mother candle nor below of the mother candle
symbol: infy, hdfc
*/
function validateRangeWithinDates($symbol, $currDate, $motherCandle) {

    $isValidRange = 0;
    $dbConn = $GLOBALS['conn'];

    $ifpriceBOHappened = checkIfPriceCloseBeyondRanges($symbol, $currDate, $motherCandle);

     // checking Del & vol Bo only if price BO Not happened
    if(!$ifpriceBOHappened) {     
        
        // checking  if vol & del BO Only if price is within mother candle range
        $ifVolDelBOHappened = checkIfVolAndDeliveryBOInDateRange($symbol, $currDate, $motherCandle['trading_date']);
        if($ifVolDelBOHappened)
        $isValidRange = 1;

    }

    return $isValidRange;
}  

/*
Desc: Check if  Vol & Delivery breakeout happened within range and returns the no of times if any else 0
symbol: infy, hdfc
starrt: 2020-08-02
enddate: 2020-08-08
*/
function checkIfVolAndDeliveryBOInDateRange($symbol, $startDate, $endDate) {

    $ifBoHappened = 0;
    $dbConn = $GLOBALS['conn'];


    $sqlCheckIfBOInDateRange = "SELECT * FROM `security_vol_devlivery_day_wise` 
                                WHERE  `trading_date` < '".$startDate."' AND 
                                `trading_date` > '".$endDate."'
                                AND symbol = '$symbol' 
                                AND vol_ratio >= 1
                                AND delivery_ratio >= 1";
    //echo $sqlCheckIfBOInDateRange;

    $res  = $dbConn->query($sqlCheckIfBOInDateRange);

    if($res)
    $ifBoHappened = mysqli_num_rows($res);

    return $ifBoHappened;

}

/*
Desc: Check if  closes aboves or below the range within that range, to get it invalidated, return 0 or 1
symbol: infy, hdfc
starrt: 2020-08-02
motherCandle -  mother candle detains in the range
*/
function checkIfPriceCloseBeyondRanges($symbol, $startDate, $motherCandle) {

    $ifPriceBOHappened = 0;
    $dbConn = $GLOBALS['conn'];


    $sqlCheckIfBOInDateRange = "SELECT * FROM `security_vol_devlivery_day_wise` 
                                    WHERE  `trading_date` < '".$startDate."' AND 
                                    `trading_date` > '".$motherCandle['trading_date']."' 
                                    AND symbol = '$symbol' 
                                    AND (last_price > '".$motherCandle['high_price']."'
                                        OR last_price < '".$motherCandle['low_price']."')";

    //echo $sqlCheckIfBOInDateRange;

    $res  = $dbConn->query($sqlCheckIfBOInDateRange);

    if($res)
    $ifPriceBOHappened = mysqli_num_rows($res);

    return $ifPriceBOHappened;

}

?>