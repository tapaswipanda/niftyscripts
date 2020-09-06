<?php
/*
File Name: pre_open_range_analysis.php
Desc: use to analyse pre-open data and get an probable action based on ranges
*/
include("functions.php");
include("config.php");


$nIndex = "n200";

// for single date
analysePreOpenData('2020-09-01', $nIndex);
exit;

$dateRangeArray['startDate'] = '2019-10-19';
$dateRangeArray['endDate'] = '2020-08-20';

$dataInDateRange = getAllTradingDaysInDateRange($dateRangeArray);

if(count($dataInDateRange) > 0) {

    foreach ($dataInDateRange as $tradeDate) {
        echo "\n Updating for date : ".$tradeDate." time :".date('H:i:s.')."\n";
        analysePreOpenData($tradeDate, $nIndex);
    }
}





?>