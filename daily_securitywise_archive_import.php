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

$filename = "archive/".$fileName.".csv";    

$file = fopen($filename, "r");


$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    //$full_time_in_24_hr_format = date("H:i:s", strtotime($getData[2]));
    $trade_date = date("Y-m-d", strtotime($getData[2]));

    //echo $trade_date."\n";
    //echo $full_time_in_24_hr_format."\n";
    // continue;

    // ignoring the first row
    if(strtolower($getData[0]) == strtolower('Symbol')) {
        continue;
    }

    //$sql = "INSERT INTO daily_security_archive (symbol, series, `date`, prev_close, open_price, high_price, low_price,price) 
      //      values ('".$getData[0]."','".$trade_date."','".$full_time_in_24_hr_format."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."')";
        
      
    $sql = "INSERT INTO daily_security_archive  (symbol, series, `trading_date`, prev_close, open_price, high_price, low_price, last_price, close_price, avg_price, total_trade_quantity, turn_over, no_of_trade, deliverable_qty, delivery_percentage) 
    VALUES 
    ('".$getData[0]."','".$getData[1]."','".$trade_date."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."','".$getData[8]."','".$getData[9]."','".$getData[10]."','".$getData[11]."','".$getData[12]."','".$getData[13]."','".$getData[14]."')";
      

    //echo $sql;
    $result = mysqli_query($conn, $sql);
    //exit;

    $i++;
    
}
echo "\n $i Records  Inserted successfully.\n";

echo "Completed!!.\n";
$conn->close();


?>