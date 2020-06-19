<?php
// include("functions.php");
// $fileName = $argv[1];
// echo "\nTradeDate :: ".$tradeDate."\n";
// exit;
$host_name = "127.0.0.1";
$db_username = "root";
$db_password = "";
$db_name = "my_demo";
$conn = new mysqli($host_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("ERROR: Unable to connect: " . $conn->connect_error);
} 
echo "\n Connected to the database.\n";


// $getNse50Sql = "SELECT GROUP_CONCAT(DISTINCT `symbol` SEPARATOR ',') as nse50 FROM nse_50";
// $getNse50Res =mysqli_query($conn, $getNse50Sql);

// $getNse50Row =  mysqli_fetch_assoc($getNse50Res);
// //echo $getNse50Row['nse50'];
// $nse50Array = explode(',', $getNse50Row['nse50']);

// print_r($nse50Array);
// exit;

$filename = "NSE-Data/ind_nifty100list.csv";    

$file = fopen($filename, "r");


$i = 0;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    // ignoring the first row
    if(strtolower($getData[0]) == strtolower('company name')) {
        continue;
    }
    
    $sql = "INSERT INTO nse_50  (company_name, sector, `symbol`, series, ISIN_code) 
    VALUES 
    ('".$getData[0]."','".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."')";
      

    //echo $sql;
    $result = mysqli_query($conn, $sql);
    //exit;
    if( $result )
    $i++;
    
}
echo "\n $i Records  Inserted successfully.\n";


echo "Completed!!.\n";
$conn->close();


?>