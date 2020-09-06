<?php
/* 
File Name: import_pre_open-date.php
Arg: take date are params, like 2020-08-15
Desc: Used to download pre-open data and import in to DB 
Delete the frist row of the file(header row before running the scripts)
url : https://www.nseindia.com/api/market-data-pre-open?key=FO&csv=true
*/

include("functions.php");
include("config.php");


// if (file_exists($preOpenDatafileName)) {
//     unlink($preOpenDatafileName);
// }
//$fileDownload = downloadFile($bhavDataFullfileName, $bhavDataFullUrl);

$nIndex = "n200";

if (isset($argv[1])) {
    $curDate = $argv[1];  
} else 
    $curDate = date('Y-m-d');

echo $curDate;
// exit;

$dateformateFile = date('d-M-Y', strtotime($curDate));
$dateformateDB = date('Y-m-d', strtotime($curDate));

$preOpenDatafileName = "NSE-Data/Pre-Open/MW-Pre-Open-Market-".$dateformateFile.".csv";

if (!file_exists($preOpenDatafileName)) {
   echo "File not exists, Please check and try again....";
   exit;
}

// This is to delete first 10 lines of the file since this headers are not in proper csv format
//$content = file($preOpenDatafileName);
// array_splice($content, 0, 10);
// file_put_contents($preOpenDatafileName, $content);

// reading file in read mode
$file = fopen($preOpenDatafileName, "r");

$i = 0;
$line = 1;

while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {

    $line++;

    // // to ignore first 10 line, since it its the headetr and also not in proper csv format
    if($line < 10)
    continue;

    // var_dump($getData);
    // break;

    //echo $trade_date."\n";
    $bad_symbols = array(",");

    $prev_close = str_replace($bad_symbols, "", $getData[1]);
    $iep_price = str_replace($bad_symbols, "", $getData[1]);
    $change_abs = str_replace($bad_symbols, "", $getData[3]);
    $change_percentage = str_replace($bad_symbols, "", $getData[4]);
    $final_price = str_replace($bad_symbols, "", $getData[5]);
    $final_quantity = str_replace($bad_symbols, "", $getData[6]);
    $trade_value = str_replace($bad_symbols, "", $getData[7]);
    $m_cap = str_replace($bad_symbols, "", $getData[8]);
    $n52_week_high = str_replace($bad_symbols, "", $getData[9]);
    $n52_week_low = str_replace($bad_symbols, "", $getData[10]);

    $mCapRatio = $getData[8]/$getData[7];
    $prevCurrVolRatio = 1;
      
    $sql = "INSERT INTO pre_open_data  (symbol, trade_date, prev_close, iep_price, change_abs, 
    change_percentage, final_price, 	final_quantity, trade_value, m_cap, 52_week_high, 52_week_low,
     mcap_vol_ratio, curr_and_prev_vol_ratio) 
    VALUES 
    ('".$getData[0]."','".$dateformateDB."','".$prev_close."','".$iep_price."','".$change_abs."','".
    $change_percentage."','".$final_price."','".$final_quantity."','".$trade_value."','".$m_cap."','".
    $n52_week_high."','".$n52_week_low."','".$mCapRatio."','".$prevCurrVolRatio."')";
      
    // if($getData[0]  == "DRREDDY") {
    //     echo $sql;
    // }

    //echo $sql;
    $result = mysqli_query($conn, $sql);

    if($result)
    $i++;
    
    // if($i == 1)
    // exit;
}

analysePreOpenData($dateformateDB, $nIndex);
// analyse pre-open data and update DB, total 139 fno stocks
if($i != 139) {
    echo "There is some issue in importing preopen data, Not all records imported";
    exit;
}

echo "\n $i Records  Inserted successfully.\n";

echo "Completed!!.\n";
$conn->close();


//nse_index
//nse_50
//SELECT `symbol`, count(*) FROM `nse_index` WHERE `nse_index` = 'n50'  group by `symbol`



// function curl_get_contents($url)
// {
//     $ch = curl_init();
//     $header = [];
//     $header[] .= ":method: GET";
//     $header[] = ":scheme: https";
//     $header[] = "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9";
//     $header[] = "accept-encoding: gzip, deflate, br";
//     $header[] = "Cache-Control: max-age=0";
//     $header[] = "accept-language: en-US,en;q=0.9";
    

//     // $header[] = "Connection: keep-alive";
//     // $header[] = "Keep-Alive: 300";
//     //$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    
//     $header[] = "referer: https://www.nseindia.com/market-data/pre-open-market-cm-and-emerge-market";
//     $header[] = "sec-fetch-dest: empty";
//     $header[] = "sec-fetch-mode: cors";
//     $header[] = "sec-fetch-site: same-origin";
//     $header[] = "upgrade-insecure-requests: 1";
//     $header[] = "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36";
//     curl_setopt( $ch, CURLOPT_HTTPHEADER, $header ); 

//     // curl_setopt($ch, CURLOPT_HEADER, 0);
//     // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_URL, $url);

//     // I have added below two lines
//     // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

//     $data = curl_exec($ch);

//     if(curl_errno($ch)){
//         echo 'Request Error:' . curl_error($ch);
//     }


//     curl_close($ch);

//     print_r($data);

//     return $data;
// }



?>