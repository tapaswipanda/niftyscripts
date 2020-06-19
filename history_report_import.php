<?php
$fileName = $argv[1];
// echo "\nTradeDate :: ".$tradeDate."\n";
//exit;
$host_name = "127.0.0.1";
$db_username = "root";
$db_password = "";
$db_name = "my_demo";
$conn = new mysqli($host_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("ERROR: Unable to connect: " . $conn->connect_error);
} 
echo "\n Connected to the database.\n";

$filename = "logs/".$fileName.".csv";    

$file = fopen($filename, "r");

// Make the table empty first
$sqltruncateTable = "TRUNCATE  current_history";
$result = mysqli_query($conn, $sqltruncateTable);
echo "Trauncated Old Data.\n";
$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    $tradeid = $getData[8];
    $trade_date = date("Y-m-d", strtotime($getData[0]));

    $full_time_in_24_hr_format = date("H:i:s", strtotime($getData[9]));
    //$trade_date = date("Y-m-d", strtotime($getData[1]));

    $order_type = $getData[4];
    $instrument = $getData[1];
    $product = $getData[3];
    $quantity = $getData[5];
    $price = $getData[6];

    //echo $trade_date."\n";
    //echo $full_time_in_24_hr_format."\n";
    // continue;

    //echo $instrument."\n";

    // ignoring the first row
    if(strtolower($tradeid) == strtolower('Trade_ID')) {
       continue;
    }

    $sql = "INSERT into current_history (tradeid,trade_date,full_time,type,instrument,product,quantity,price) 
            values ('".$tradeid."','".$trade_date."','".$full_time_in_24_hr_format."','".$order_type."','".$instrument."','".$product."','".$quantity."','".$price."')";
            
    //echo $sql;

    $result = mysqli_query($conn, $sql);
    //exit;

    $i++;
    
}

//echo $instrument."\n";
echo "\n $i Records  Inserted successfully.\n";

// delete the first header row
$sqlDeleteHeaderRow = "DELETE FROM current_history WHERE tradeid = 0";
$result = mysqli_query($conn, $sqlDeleteHeaderRow);
echo "Deleted the Header row of CSV file.\n";

echo "Completed!!.\n";
$conn->close();


?>