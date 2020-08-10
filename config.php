<?php
$host_name = "127.0.0.1";
$db_username = "root";
$db_password = "";
$db_name = "my_demo";
$conn = new mysqli($host_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("ERROR: Unable to connect: " . $conn->connect_error);
} 
//echo "\n Connected to the database.\n";

$todayDate = date("d-m-Y");

$dpAvgDays = 22;

$weekReportStartDate = "2020-05-17";

$bhavDataFullUrl = "https://www1.nseindia.com/products/content/sec_bhavdata_full.csv";

$fullDataDownloadDayWiseURL = "https://archives.nseindia.com/products/content/sec_bhavdata_full_11072020.csv";

$preOpenDataUrl  = "http://www.nseindia.com/api/market-data-pre-open?key=FO&csv=true";

$bhavDataFullfileName = "NSE-Data/Full-dowload-report/bhavCopy_".$todayDate.".csv";



$preOpenDatafileName = "NSE-Data/Pre-Open/MW-Pre-Open-Market-".$todayDate.".csv";


$finalDailyListN50 = "NSE-Data/Full-dowload-report/shortlist/shortlist_n50_".$todayDate.".csv";
$finalDailyListN100 = "NSE-Data/Full-dowload-report/shortlist/shortlist_n100_".$todayDate.".csv"; 

$weekVolReport = "NSE-Data/Full-dowload-report/weekly/vol_report_".$todayDate.".csv"; 


// All report NSE link
//https://www1.nseindia.com/products/content/all_daily_reports.htm?param=Derivative

// MA report CSV
//https://www1.nseindia.com/archives/equities/mkt/MA310720.csv

// New website publication pdf
//https://archives.nseindia.com/content/circulars/WEB45032.pdf

// All in one page
//https://www.nseindia.com/market-data/analysis-and-tools-capital-market-snapshot


// new full data download file day wise
//https://archives.nseindia.com/products/content/sec_bhavdata_full_31072020.csv

// New MA activity report 
//https://archives.nseindia.com/archives/equities/mkt/MA310720.csv

// All report new site
//https://www.nseindia.com/all-reports


?>