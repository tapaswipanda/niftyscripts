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

$weekReportStartDate = "2020-05-17";

$bhavDataFullUrl = "https://www1.nseindia.com/products/content/sec_bhavdata_full.csv";

$bhavDataFullfileName = "NSE-Data/Full-dowload-report/bhavCopy_".$todayDate.".csv";

$finalDailyListN50 = "NSE-Data/Full-dowload-report/shortlist/shortlist_n50_".$todayDate.".csv"; 

$finalDailyListN100 = "NSE-Data/Full-dowload-report/shortlist/shortlist_n100_".$todayDate.".csv"; 

$weekVolReport = "NSE-Data/Full-dowload-report/weekly/vol_report_".$todayDate.".csv"; 





?>