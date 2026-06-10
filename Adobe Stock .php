<?php
if(!isset($_GET["url"]) || empty($_GET["url"])){
  die;
}

if(!isset($_GET["secret"]) || $_GET["secret"]!="tonyyasinsina"){
  die;
}

function GetBetween($content,$start,$end)
    {
      $r = explode($start, $content);
      if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
      }
      return '';
    }
$useragent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.81 Safari/537.36";
$url = urldecode($_GET["url"]);
if(strpos($url, "?")!==false){
      if(strpos($url, "&asset_id=")!==false){
        $explo = explode("&asset_id=", $url);
        $imageid = $explo[1];
      }
      elseif(strpos($url, "?asset_id=")!==false){
        $explo = explode("?asset_id=", $url);
        $imageid = end(explode("/",$explo[0]));
      }
      elseif(strpos($url, "?k=")!==false){
        $explo = explode("?k=", $url);
        $imageid = $explo[1];
      }
      else
      {
        $explo = explode("?", $url);
        $explo = explode("/",$explo[0]);
        $imageid = end($explo);
      }
      
    }
    else
    {
    $explo = explode("/",$url);
      $imageid = end($explo);
    }
   $url = "https://stock.adobe.com/id/".$imageid;

  $geturl = "https://stock.adobe.com/Ajax/MediaData/".$imageid."?full=1";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $geturl);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  $headers = [
    'Referer: https://stock.adobe.com',
    'User-Agent: '.$useragent,
    'upgrade-insecure-requests: 1'
  ];

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                                                                                            
  $rs = curl_exec($ch);
  curl_close($ch);

  $json = json_decode($rs);

  $is_standard = $json->is_standard;
  $is_premium = $json->is_premium;
  $is_video = $json->is_video;

  if($is_premium || $is_video){
    $rsArray = array('status' => "Error", 'message' => "Adobestock now support only Standard license.");
    echo json_encode($rsArray);
    die;
  }

  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/wamp/www/adobe.com_cookies.txt');
  curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/wamp/www/adobe.com_cookies.txt');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  $headers = [
   'Referer: https://stock.adobe.com/',
   'User-Agent: '.$useragent,
   'Connection: keep-alive'
 ];

 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                                                                                            
 $rs = curl_exec($ch);
 curl_close($ch);

 $html = new DOMDocument();
 @$html->loadHTML($rs);
 $balance = 0;
 $xpath = new DOMXPath($html);
 $els = $xpath->query("//span[@data-t='quota-images-standard']");
 if($els->length > 0){
  $el = $els->item(0);
  $balance = (int) filter_var($el->nodeValue, FILTER_SANITIZE_NUMBER_INT);
}


$start = '<script type="application/json" id="js-page-config">';
$end = '</script>';
$getjs = GetBetween($rs,$start,$end);

$json = json_decode($getjs);

$csrf = $json->stockPortal->reduxState->portal->page->csrfToken;
$xRequestId = $json->stockPortal->reduxState->portal->page->xRequestId;

$loginUrl = 'https://stock.adobe.com/Ajax/GetDownload/'.$imageid.'/1';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/wamp/www/adobe.com_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/wamp/www/adobe.com_cookies.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$headers = [
 'referer: '.$url,
 'User-Agent: '.$useragent,
 'x-csrf-token: '.$csrf,
 'x-request-id: '.$xRequestId,
 'x-requested-with: XMLHttpRequest',
 'sec-fetch-site: same-origin',
 'sec-fetch-mode: cors',
 'sec-fetch-dest: empty',
 'origin: https://stock.adobe.com',
 'Connection: keep-alive',
 'content-length: 0'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$content = curl_exec($ch);
curl_close($ch);

$jsons = json_decode($content);


if(!empty($jsons->download_url)){
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $jsons->download_url);
 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
 curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:/wamp/www/adobe.com_cookies.txt');
 curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:/wamp/www/adobe.com_cookies.txt');
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_NOBODY, 1);

 $headers = [
  'Referer: '.$url,
  'User-Agent: '.$useragent,
  'Connection: keep-alive'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                                                                                            
$rs = curl_exec($ch);
$download_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

}

if(!empty($download_url) && strpos($download_url, "amazonaws.com")!==false){
  $explo = explode("filename%3D", $download_url);

                    if (strpos($explo[1], "&")!==false) {
                        $expl = explode("&", $explo[1]);
                        $filename = str_replace("%22", "", $expl[0]);
                    } else {
                        $filename = str_replace("%22", "", $explo[1]);
                    }
                    if (strpos($download_url, 'filename%3D') === false) {
                        $filename = basename(trim(strtok($download_url, '?')));
                    }
  include ('C:/wamp/www/upl.php');
  uploader('adobestock',$imageid,'adobestock_standard',$download_url,$filename);


  $rsArray = array('status' => "Success", 'stocksite' => 'Adobestock', 'imageid' => $imageid, 'url' => $download_url,'filename' => $filename, 'balance' => $balance);
   echo json_encode($rsArray);
   die;
}
else
{

  $rsArray = array('status' => "Error", 'message' => "Have error when get Adobestock. Pls try again!", 'details' => $jsons);
  echo json_encode($rsArray);
  die;
}
?>
