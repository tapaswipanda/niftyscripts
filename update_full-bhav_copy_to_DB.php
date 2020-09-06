<?php
/*
File Name: update_full-bhav_copy_to_DB.php
Desc: used to read full bhav copy data and update DB, then  need to proceess data to process the EOD data fields 
First uncomment the section to read data from file and insert DB
then call processFullBhavCopyReportInDateRange function to process thr report in a date range
*/
include("functions.php");
include("config.php");

$dir = "NSE-Data/Full-dowload-report/history1/";

// Sort in ascending order - this is default
$files = scandir($dir);

//echo "\n ".count($files)." no of files present";

$i = 0;

$nIndex = "n200";

$nIndexArray = getNiftyIndexStocks($nIndex);

//Going through files in that folder
// foreach ($files as $file) {

//     //echo $file;
//     if ($file != '.' && $file != '..') { 

//         $fullFileName = $dir."/".$file;
//         echo "\n ".$fullFileName;

//         $updateCount = updateFullBhavCopyToDB($fullFileName, $nIndex);

//         $i++;

//         // if($i == 20)
//         // break;
//     }
// }
//  exit;

//$nIndexArray = ['infy'];


$dateRangeArray['startDate'] = '2020-08-20';
$dateRangeArray['endDate'] = '2020-08-21';

processFullBhavCopyReportInDateRange($nIndexArray, $dateRangeArray);

//SELECT `symbol`,`trading_date`,`last_price`,`total_trade_quantity`,`deliverable_qty`,`delivery_percentage`,`vol_ratio`,`delivery_ratio`,`candle_type` FROM `security_vol_devlivery_day_wise` WHERE `symbol` LIKE 'infy' AND `vol_ratio` >= 1 AND `delivery_ratio` >= 1 AND `trading_date` >= '2020-01-01' and  `trading_date` <= '2020-02-01'




?>