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

if(!file_exists($filename)) {
    echo $filename;
    echo "\n File not exist!!!Exiting...";
    exit;
}

$file = fopen($filename, "r");

// Make the table empty first
$sqltruncateTable = "TRUNCATE  current_history";
$result = mysqli_query($conn, $sqltruncateTable);
echo "Trauncated Old Data.\n";

$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    $full_time_in_24_hr_format = date("H:i", strtotime($getData[1]));
    $trade_date = date("Y-m-d", strtotime($getData[1]));

    //echo $trade_date."\n";
    //echo $full_time_in_24_hr_format."\n";
    // continue;

    // ignoring the first row
    if(strtolower($getData[0]) == strtolower('Trade ID')) {
        continue;
    }

    // checking if same record already present, it means same order machted and executed with 2 different trade but at same time
    $getIfSameRecordPresentSql = "SELECT * FROM current_history 
                                WHERE  trade_date =  '$trade_date' 
                                AND full_time = '$full_time_in_24_hr_format' 
                                AND `type` =  '$getData[2]' 
                                AND `instrument` = '$getData[3]' ";
    
    echo "\n".$getIfSameRecordPresentSql."\n";
    $resIfSameRecordPresentSql = mysqli_query($conn, $getIfSameRecordPresentSql);

    if(mysqli_num_rows($resIfSameRecordPresentSql)) {

        while($row = mysqli_fetch_assoc($resIfSameRecordPresentSql)) {
            
            $id = $row['id'];
            $prevQuantity = $row['quantity'];
            $prevPrice = $row['price'];

            //new quanityt = prev Quantity +  current Quantity
            $newQuantity = $prevQuantity + $getData[5];
            // To avg the price if there is any difference in price between prev and new 
            $newPrice = (($prevPrice * $prevQuantity) + ($getData[5] * $getData[6])) / $newQuantity;

            $updateNewPriceAndQuantitySql = "UPDATE current_history SET 
                                quantity = '$newQuantity',
                                price = '$newPrice'
                                WHERE id = '$id' ";

            $updateNewPriceAndQuantity =  mysqli_query($conn, $updateNewPriceAndQuantitySql); 


            if ( $updateNewPriceAndQuantity === false ) {
                echo "There is some error in the update. Please check ".$getData[3]." Exiting...";
                echo mysqli_error($conn) . "\n";
                exit;
            }
        }
    } else {
        $sql = "INSERT into current_history (tradeid,trade_date,full_time,type,instrument,product,quantity,price) 
                values ('".$getData[0]."','".$trade_date."','".$full_time_in_24_hr_format."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."')";
                
        //echo $sql;
        $result = mysqli_query($conn, $sql);

        if ( $result === false ) {
            echo "There is some error in the insert. Please check ".$getData[3]." Exiting...";
            echo mysqli_error($conn) . "\n";
            exit;
        }
        //exit;

        $i++;
    }
    
}
echo "\n $i Records  Inserted successfully.\n";

// delete the first header row
$sqlDeleteHeaderRow = "DELETE FROM current_history WHERE tradeid = 0";
$result = mysqli_query($conn, $sqlDeleteHeaderRow);
echo "Deleted the Header row of CSV file.\n";

echo "Completed!!.\n";
$conn->close();


?>