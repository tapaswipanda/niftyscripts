<?php
include("functions.php");
include("config.php");
// $bhavDataFullfileName = $argv[1];



if(!isset($_REQUEST['date']) ||  trim($_REQUEST['date']) == ''  || !checkValidDate($_REQUEST['date'])) {
    echo "Enter a Valid Date";
    exit;
}


$startDate = $weekReportStartDate;
$endDate = $_REQUEST['date'];

if(dateCompare($startDate, $endDate)) {

    generateWeekVolGainer($endDate);
    pushFileForDownload($weekVolReport);

} else {

    echo "Enter a Date After $startDate";
    exit;
}


?>