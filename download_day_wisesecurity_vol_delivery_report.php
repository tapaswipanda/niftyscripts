<?php
/*
File Name: download_day_wisesecurity_vol_delivery_report.php
Desc: Used to download day wise historical data security volume and delivery report and stored in to a given location
After that update_full-bhav_copy_to_DB.php can be called to import in to DB
Params: A date range need to specify for download
url : https://www.nseindia.com/api/market-data-pre-open?key=FO&csv=true
*/
include("functions.php");
include("config.php");

//from date
//https://archives.nseindia.com/products/content/sec_bhavdata_full_30092019.csv

$begin = new DateTime('2020-09-03');
$end = new DateTime('2020-09-04');
$nIndex = "n200";

$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($begin, $interval, $end);

foreach ($period as $dt) {

    //echo "in for loop ";
    $curDate = $dt->format("Y-m-d");
    echo "\n".$curDate;

    continue;

    $isWeekEnd = isWeekend($curDate);
    //if($isWeekEnd) echo "  - Weekend";

    $isNseHoliday = isNseHoliday($curDate);
    //if($isNseHoliday) echo "  - Holiday";

    if(!$isWeekEnd && !$isNseHoliday) {
        downloadAndProcessFullBhavCopyReport($curDate,  $nIndex);
    }
    
}

/*
TO download data via javascript in browser
$('.download-data-link a').trigger( "click" );
var csv_test_data = $('.download-data-link a').attr('href')
window.open(csv_test_data);


stockObj = ["LT","infy","TCS"]
for (symbolst in stockObj) {
$('#symbol').val(stockObj[symbolst])
submitData();
setTimeout(downloadFile, 3000)
}
function downloadFile() {
$('.download-data-link a').trigger( "click" );
var csv_test_data = $('.download-data-link a').attr('href')
window.location.href = csv_test_data
}
*/


?>