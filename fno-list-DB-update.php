<?php
/*
File Name: fno-list-DB-update.php
Desc: Update nse_index table for fno data, reading from pre-open  fno file
reads data from the fno list csv file and update the nse_index table
*/

include("functions.php");
include("config.php");

$filename = "NSE-Data/Pre-Open/MW-Pre-Open-Market-02-Aug-2020.csv";


$file = fopen($filename, "r");

$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    // ignoring the first row
    if(strtolower($getData[0]) == strtolower('SYMBOL')) {
        continue;
    }
    
    
    $sql = "UPDATE nse_index SET is_fno = 1 WHERE symbol = '$getData[0]'";
      

    //echo $sql;
    $result = mysqli_query($conn, $sql);
    //exit;
    if( $result )
    $i++;
    else{
         echo $sql;
        echo mysqli_error($conn) . "\n";
        exit;
    }

    $i++;
    if($i == 1)
    exit;    
}
echo "\n $i Records  Inserted successfully.\n";


echo "Completed!!.\n";
$conn->close();


?>