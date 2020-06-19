<?php
$tradeDate = $argv[1];
// echo "\nTradeDate :: ".$tradeDate."\n";
//exit;

if($tradeDate == '') {
    echo "No Date provided...Exiting...";
    exit;
}
$final_csv_file = "logs/final/".$tradeDate.".csv"; 
$csvData = [];
$host_name = "127.0.0.1";
$db_username = "root";
$db_password = "";
$db_name = "my_demo";
$conn = new mysqli($host_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("ERROR: Unable to connect: " . $conn->connect_error);
} 
echo '\n Connected to the database.<br>';
$sql  = $conn->query("SELECT * FROM `current_history` ORDER BY `trade_date`, `instrument`, `full_time` ASC");

$dayInstrumentRecordArray = [];
$instrumentArray = [];
$i = 0;

while($row = mysqli_fetch_assoc($sql)) {   
    
    //print_r($row);

    $instrumentName = $row['instrument'];

    // echo $instrumentName."\n";
    // continue;

    if(array_key_exists($instrumentName, $instrumentArray)) {
        //print_r($instrumentArray);
        //exit;

        if(checkIfCoveredAction($instrumentArray[$instrumentName]['action'], $row['type'])) {
            
            //echo $row['type']."\n";
            if(strtolower($instrumentArray[$instrumentName]['action']) == 'buy') // it means the intitial action was buy        
                $remainingQuantity = $instrumentArray[$instrumentName]['r_quanity'] - $row['quantity'];
            else
                $remainingQuantity = $instrumentArray[$instrumentName]['r_quanity'] + $row['quantity'];

            //echo $remainingQuantity." :: remaining quantity \n";

            if($remainingQuantity == 0) { // squared off
                $instrumentArray[$instrumentName]['covered_time'] = $row['full_time'];
                $instrumentArray[$instrumentName]['covered_action'] = $row['type'];
                $instrumentArray[$instrumentName]['covered_price'] = $row['price'];

                insertAllHistory($instrumentArray[$instrumentName]); // Insert the record in to as the postion covered up completely
                $i++; // that counts the No of trades
                $dayInstrumentRecordArray[$instrumentName]['isSquaredOff'] = 1; //update the global array
                unset($instrumentArray[$instrumentName]); // remove the instrument from the array

            }
        }
        //exit;

    } else {

        $instrumentArray[$instrumentName]['trade_date'] = $row['trade_date'];
        $instrumentArray[$instrumentName]['full_time'] = $row['full_time'];
        $instrumentArray[$instrumentName]['action'] = $row['type'];
        $instrumentArray[$instrumentName]['instrument'] = $row['instrument'];
        $instrumentArray[$instrumentName]['quantity'] = $row['quantity'];
        $instrumentArray[$instrumentName]['price'] = $row['price'];
        
        $instrumentArray[$instrumentName]['r_quanity'] = 0;        
        if(strtolower($row['type']) == 'buy')
            $instrumentArray[$instrumentName]['r_quanity'] += $row['quantity'];
        else 
            $instrumentArray[$instrumentName]['r_quanity'] -= $row['quantity'];

        
        $instrumentArray[$instrumentName]['trade_type'] = "Normal";

        if(array_key_exists($instrumentName, $dayInstrumentRecordArray)) {
            
            // echo $instrumentName."\n";
            // print_r($dayInstrumentRecordArray[$instrumentName]);
            if($dayInstrumentRecordArray[$instrumentName]['initialAction'] ==  $row['type'] && $dayInstrumentRecordArray[$instrumentName]['isSquaredOff'] == 1) {
                $instrumentArray[$instrumentName]['trade_type'] = "Re-Entry";
            } else {
                $instrumentArray[$instrumentName]['trade_type'] = "Reversal";
            }
        } else {
            $dayInstrumentRecordArray[$instrumentName]['isSquaredOff'] = 0;        
            $dayInstrumentRecordArray[$instrumentName]['initialAction'] =  $row['type'];
        }

    }
    
}

// Updating CSV file
if($i >= 1) {
    updateCSV();
}

echo "\n $i No Of Trade Recorded successfully.\n";

$conn->close();

function checkIfCoveredAction($existingAction, $coveredAction) {
    if(strtolower($existingAction) == strtolower($coveredAction))
        return 0;
    else 
    return 1;
}

function insertAllHistory($instrumentSingleItemArray) {

    //print_r($instrumentSingleItemArray);

    $dbConn = $GLOBALS['conn']; 
    global $csvData;

    //$tradeDate = $GLOBALS['tradeDate'];

    $tradeDate = $instrumentSingleItemArray['trade_date'];
    $symbol = $instrumentSingleItemArray['instrument'];
    $action = $instrumentSingleItemArray['action'];
    $entry_price = $instrumentSingleItemArray['price'];
    $entry_time = $instrumentSingleItemArray['full_time'];
    $exit_price = $instrumentSingleItemArray['covered_price'];
    $exit_time = $instrumentSingleItemArray['covered_time'];
    $quantity = $instrumentSingleItemArray['quantity'];
    $trade_type = $instrumentSingleItemArray['trade_type'];
    
    if (strtotime($exit_time) < strtotime($entry_time )) {
        echo "\nThere is some error in the Trade timing for script:".$symbol."\n";
        print_r($instrumentSingleItemArray);
        exit;
      }

    if(strtolower($action) == 'buy'){
        $gain_or_loss_percentage =  (($exit_price - $entry_price)*100)/$exit_price;
        $gross_gain_or_loss =  ($exit_price - $entry_price)*$quantity;
    } else {
        $gain_or_loss_percentage =  (($entry_price - $exit_price)*100)/$entry_price;
        $gross_gain_or_loss =  ($entry_price - $exit_price)*$quantity;
    }
    $gain_loss = 'Profit';
    if($gain_or_loss_percentage < 0) {
        $gain_loss = "Loss";
    }
    $total_exposer = $entry_price * $quantity;


    $sql = "INSERT INTO full_history SET
           `date` = '$tradeDate',
           symbol = '$symbol',
           `action` = '$action',
           entry_price = '$entry_price',
           entry_time = '$entry_time',
           exit_price = '$exit_price',
           exit_time = '$exit_time',
           quantity = $quantity,
           gain_loss = '$gain_loss',
           gain_or_loss_percentage = '$gain_or_loss_percentage',
           gross_gain_or_loss = '$gross_gain_or_loss',
           total_exposer = $total_exposer,
           trade_type = '$trade_type'";
    
    //echo $sql;
    $dbConn->query($sql);

    $csvData[] = array($tradeDate, $symbol, $action, $entry_price, $entry_time, $exit_price, $exit_time,  $quantity, $gain_loss, $gain_or_loss_percentage, $gross_gain_or_loss, $total_exposer, $trade_type);
    //print_r($csvData);
    

}

function updateCSV() { 
    
    $final_csv_file = $GLOBALS['final_csv_file'];
    $csvData = $GLOBALS['csvData'];

    // print_r($csvData);
    // exit;

    $fp = fopen($final_csv_file, 'wb');
    foreach ($csvData as $line) {
        
        //print_r($line);
        fputcsv($fp, $line, ',');
    }
    fclose($fp);

}
?>