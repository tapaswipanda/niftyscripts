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

function getDataInDateRange($symbol, $dateRange) {
    
    
    $dateInDateRange = [];
    $dbConn = $GLOBALS['conn'];

    $sqlGetAllPrevDateDate = "SELECT * FROM `daily_security_archive` 
                                WHERE  `trading_date` <= '".$dateRange['endDate']."' AND 
                                `trading_date` >= '".$dateRange['startDate']."' AND
                                symbol = '$symbol' 
                                ORDER BY `trading_date` DESC";

    // if($symbol == 'COALINDIA')
    // echo $sqlGetAllPrevDateDate;

    $res  = $dbConn->query($sqlGetAllPrevDateDate);

    $highPrice = 0;
    $lowPrice = 0;

    while($row = mysqli_fetch_assoc($res)) {   

        if($row['high_price'] >= $highPrice)
            $highPrice = $row['high_price'];
        if(($row['low_price'] <= $lowPrice) || ($lowPrice == 0))
            $lowPrice = $row['low_price'];

    }
    $dateInDateRange['high_price'] = $highPrice;
    $dateInDateRange['low_price'] = $lowPrice;


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

function checkIfHighDeliveryAndPercentage($parentData, $childData) {

    $highDeliveryWithPercentage = 0;
    
    //print_r($childData);

    if(($childData['deliverable_qty'] > $parentData['deliverable_qty']) &&
         ($childData['delivery_percentage'] > $parentData['delivery_percentage']) && 
         ($childData['total_trade_quantity'] > $parentData['total_trade_quantity']) ) {
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

    //echo $final_csv_file;

    // to get prev trading date. symbol cane be any, i passed 'infy'
    $prevTradeDate = getPrevTradingDate("infy", $tradeDate);

    //echo "\nPrev trading date: ".$prevTradeDate." \n";

    $getNse50Sql = "SELECT symbol, sector, nse_index FROM nse_50  where nse_index = 'n50'";
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
            $avgDeliveryPercentage = getAvgDeliveryPercentage($symbol, $tradeDate, 10);

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

    // to get prev trading date. symbol cane be any, i passed 'infy'
    $prevTradeDate = getPrevTradingDate("infy", $tradeDate);

    //echo "\nPrev trading date: ".$prevTradeDate." \n";

    $getNse50Sql = "SELECT symbol, sector, nse_index  FROM nse_50  where nse_index = 'n50' OR nse_index = 'n100'";
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
            $avgDeliveryPercentage = getAvgDeliveryPercentage($symbol, $tradeDate, 10);
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
    
    if(file_put_contents( $fileName,file_get_contents($url,false,$context))) { 
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

?>