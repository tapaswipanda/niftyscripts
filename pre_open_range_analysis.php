<?php
/*
File Name: pre_open_range_analysis.php
Desc: use to analyse pre-open data and get an probable action based on ranges
*/
include("functions.php");
include("config.php");


$nIndex = "n200";

// for single date
analysePreOpenDate('2020-08-10', $nIndex);
exit;

$dateRangeArray['startDate'] = '2019-10-20';
$dateRangeArray['endDate'] = '2020-08-07';

$dataInDateRange = getAllTradingDaysInDateRange($dateRangeArray);

if(count($dataInDateRange) > 0) {

    foreach ($dataInDateRange as $tradeDate) {
        echo "\n Updating for date : ".$tradeDate." time :".date('H:i:s.')."\n";
        analysePreOpenDate($tradeDate, $nIndex);
    }
}





?>