<?php
/*
File Name:  date_range_fun.php
Desc: Contains all date related functions
*/


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




?>