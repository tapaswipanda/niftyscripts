<?php
/*
File Name: preopen_analysis_fun.php
Desc: Contains all function related to pre-open analysis
*/


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
function analysePreOpenData($tradeDate, $nIndex) {

    $nIndexArray = getNiftyIndexStocks($nIndex);

    //$nIndexArray = ['ULTRACEMCO'];

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

        echo "\n Mother candle for $symbol : ".$motherCandle['trading_date']." for date $prevTradingDate evaluate on $tradeDate\n";

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

        $noOfCandleInside = checkNoOfCandlesInRange($symbol, $currDate, $motherCandle);


        if($ifVolDelBOHappened && $noOfCandleInside)
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
                                WHERE  `trading_date` <= '".$startDate."' AND 
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

/*
Desc: Check if  there is atleast one candle after monther candle and before breakout happened
symbol: infy, hdfc
starrt: 2020-08-02
motherCandle -  mother candle detains in the range
*/
function checkNoOfCandlesInRange($symbol, $startDate, $motherCandle) {

    $noOfCandleInside = 0;
    $dbConn = $GLOBALS['conn'];

    // get no of days after mother candle where the candle last price is lees then mother candle high and greater then
    $sqlCheckIfBOInDateRange = "SELECT * FROM `security_vol_devlivery_day_wise` 
                                    WHERE  `trading_date` <= '".$startDate."' AND 
                                    `trading_date` > '".$motherCandle['trading_date']."' 
                                    AND symbol = '$symbol' 
                                    AND (last_price <= '".$motherCandle['high_price']."'
                                    AND last_price >= '".$motherCandle['low_price']."')";

    //echo $sqlCheckIfBOInDateRange;

    $res  = $dbConn->query($sqlCheckIfBOInDateRange);

    if($res)
    $noOfCandleInside = mysqli_num_rows($res);

    return $noOfCandleInside;

}


?>