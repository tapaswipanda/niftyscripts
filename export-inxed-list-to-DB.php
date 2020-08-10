<?php
/*
File Name: export-inxed-list-to-DB.php
Desc: Export index list to DB from CSV file
Need to run the inxed csv file  in order, first n50, n100 and n200 list
To make Fno, run the pre-maraket ooen list to identify the fno stont from n200 list
*/

include("functions.php");
include("config.php");

$filename = "NSE-Data/ind_nifty500list.csv";    
$indexName = "n500";

//$index100 = "";

$file = fopen($filename, "r");

$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    // ignoring the first row
    // if(strtolower($getData[0]) == strtolower('company name')) {
    //     continue;
    // }
    
    
    $sql = "INSERT INTO nse_index  (company_name, sector, `symbol`, series, ISIN_code, nse_index) 
    VALUES 
    ('".addslashes($getData[0])."','".addslashes($getData[1])."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$indexName."')";
      

    //echo $sql;
    $result = mysqli_query($conn, $sql);
    //exit;
    if( $result )
    $i++;
    // else{
    //      echo $sql;
    //     echo mysqli_error($conn) . "\n";
    //     exit;
    // }
    
}
echo "\n $i Records  Inserted successfully.\n";


echo "Completed!!.\n";
$conn->close();


//nse_index
//nse_50
//SELECT `symbol`, count(*) FROM `nse_index` WHERE `nse_index` = 'n50'  group by `symbol`


?>