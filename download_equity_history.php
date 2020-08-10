<?php
/*
File Name: download_equity_history.php
Desc: To download historic data for Equity  
url : https://www.nseindia.com/api/market-data-pre-open?key=FO&csv=true
*/
include("functions.php");
include("config.php");

//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: test=cookie"));


$url = "https://www.nseindia.com/api/historical/cm/equity?symbol=INFY&series=[%22EQ%22]&from=08-07-2020&to=08-08-2020&csv=true";
$url = "https://www.nseindia.com/api/market-data-pre-open?key=FO&csv=true";

$get_data = callAPI('GET', $url, false);


function callAPI($method, $url, $data){
    
    $curl = curl_init();
    switch ($method){
       case "POST":
          curl_setopt($curl, CURLOPT_POST, 1);
          if ($data)
             curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          break;
       case "PUT":
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
          if ($data)
             curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
          break;
       default:
          if ($data)
             $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // OPTIONS:
    echo $url;

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
       'accept-encoding: gzip, deflate, br',
       'accept-language: en-US,en;q=0.9',
       'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36'       
    ));
    curl_setopt($curl, CURLOPT_COOKIE, 'bm_sv=98C726D83935860092695C28E1912914~acwV3f87hdlKHGv4nuXghyT17AxyJiucUS9wAokPlo2nxnzjWR/VdQNWgo7S3oeN7fSzEH5pR7EFlSIBb7O8CWQURwflhdXUf5MCj1I0g+FYRon5Rxkr3vLVWsmnzpbMDpVDGGbD61G5ZFY7GvNcdUczvDud0ezkd9GQcIz1cIw=bm_sv=EE2F5FAB6D197878346C5833E9E64358~Fw/XpPVQyfoErb/vrj/veSaqiNffHbI653XS2cYMEFOmIuioQNH76UFd++2vRJndHuhy1ASupcUFZVgmrUayFZwWBtJMRWI+hUqjPH+5jYaZQsCyrRj+Z4EyBzw8MJCPDhjmGB66i+L5chJInR6tahpQ9l7GpcijjOAYbxzrxmA=');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){die("\nConnection Failure");}
    curl_close($curl);
    return $result;
 }


 exit;


 $cookieFile = "cookies.txt";
if(!file_exists($cookieFile)) {
    $fh = fopen($cookieFile, "w");
    fwrite($fh, "");
    fclose($fh);
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiCall);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); // Cookie aware
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); // Cookie aware
curl_setopt($ch, CURLOPT_VERBOSE, true);
if(!curl_exec($ch)){
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}
else{
    $response = curl_exec($ch); 
}
curl_close($ch);
$result = json_decode($response, true);

echo '<pre>';
var_dump($result);
echo'</pre>';

//from date
//https://archives.nseindia.com/products/content/sec_bhavdata_full_30092019.csv



?>