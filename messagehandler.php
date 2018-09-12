<?php
require_once('./LINEBotTiny.php');
require('messagestoreapi.php');
<<<<<<< HEAD
include('secret.php');  // store token and secret

=======
include('secret.php');  // token and secret key from LINE
>>>>>>> 56303a8fba7d31b6da96d75dcb471986e948381d

$ShowID = 1;

$Line_URI = "https://api.line.me/v2/bot/profile/";

function contactline($hostname){
  $opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Accept-language: en\r\n" .
                "Authorization: Bearer " . CHANNEL_ACCESS_TOKEN . "\r\n"
    )
  );

  $context = stream_context_create($opts);
  $response = '';
  if(($fp = fopen($hostname, 'r', false, $context))) {
    while ( ! feof($fp)){
        $response .= fread($fp, 1024);
    }
  }
  else {
    return;
  }

  fclose($fp);
  return $response;
}

$client = new LINEBotTiny(CHANNEL_ACCESS_TOKEN, CHANNEL_SECRET);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            $source = $event['source'];
            switch ($message['type']) {
                case 'text':
                    $text = sanitizemsg($message['text']);

                    $url = $Line_URI . $source['userId'];
                    $out = contactline($url);
                    $outarray = json_decode($out, true);

                    $fp = fopen ('profiles/'.$source['userId'].'.jpg', 'w+');
                    $out = contactline($outarray['pictureUrl']);
                    fwrite($fp, $out);
                    fclose($fp);

                    if (array_key_exists ('groupId', $source)){
                      $groupid = $source['groupId'];
                    } else {
                      $groupid = 'null';
                    }

                    writemsg($source['userId'], $text, $outarray['displayName'], 'profiles/'.$source['userId'].'.jpg', $groupid);

                    // start add by nart
                    $mx = $message['text']; 
                    $pos = stripos($mx,"pingx");
                    if($pos !== FALSE){
                        $ipx = substr($mx,5);
                        $mx = substr($mx,0,5);	// found
                        exec("ping -c 1 -w 2 $ipx 2>&1", $output, $return_var);
                        $msng = implode(" ", $output);
                    }
                    if($mx == "pingx"){
                        $client->replyMessage(array(
                            'replyToken' => $event['replyToken'],
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => "" . $msng . ""
                                )
                            )
                        ));
                      }
                  

                    // end add by nart
// read 20 line
// $i[5] = GroupID
                    if($message['text']  == "last20line"){
                        $file = file("mesglist.csv");
                        $x=0;   $frecho = '';                   
                        for ($i = max(0, count($file) - 20); $i < count($file); $i++) {
                          $dt[] = str_getcsv($file[$i]) ;
                          if($dt[$x][5] != 'null'){
                            $frecho = $frecho.">>".$dt[$x][1]."\n";
                          }else{
//                            $frecho = $frecho.$dt[$x][1]."\n";
                          }
                          $x++;
                        }

                        $client->replyMessage(array(
                            'replyToken' => $event['replyToken'],
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => "" . $frecho . "\n https://bot.tv5.co.th/bot/mlog/list.php "
                                )
                            )
                        ));
                      }

// read  line
// start ผังรายการ http://app.tv5.co.th/ตารางออกอากาศ/
if($message['text']  == "ผังรายการ"){
    $programplan = "ติดตามผังรายการ ททบ. TV5HD1 ได้ทาง \n http://app.tv5.co.th/ตารางออกอากาศ/";

    
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text',
                'text' => "" . $programplan . ""
            )
        )
    ));
  }
// end ผังรายการ
// start pantip
if($message['text']  == "pantip" || $message['text']  == "Pantip" || $message['text']  == "พันทิพ"){
    $responsep = ''; $arx='';
    $urlp = 'https://pantip.com/forum/feed';
    $responsep = file_get_contents($urlp);
    $array = json_decode($response);
    $xml = simplexml_load_string($responsep, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    for($i=0;$i<=15;$i++){
    $pant = $pant.">>>".$array['channel']['item'][$i]['title']." \n";
    }
    //echo $arx;
    
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text',
                'text' => "Last topic from Pantip \n" . $pant . ""
            )
        )
    ));
  }
// end pantip
// start oil price
if($message['text']  == "oilprice" || $message['text']  == "ราคาน้ำมัน"){
$oilurl = new SoapClient("http://www.pttplc.com/webservice/pttinfo.asmx?WSDL",
array(
"trace"      => 1,        // enable trace to view what is happening
"exceptions" => 0,        // disable exceptions
"cache_wsdl" => 0)         // disable any caching on the wsdl, encase you alter the wsdl server
);

$params = array(
'Language' => "en",
'DD' => date('d'),
'MM' => date('m'),
'YYYY' => date('Y')
);

$dataz = $oilurl->GetOilPrice($params);
$ob = $dataz->GetOilPriceResult;
$xml = new SimpleXMLElement($ob);
// PRICE_DATE , PRODUCT ,PRICE
foreach ($xml  as  $key =>$val) {
if($val->PRICE != ''){
$oilp =  $oilp.$val->PRODUCT .'  '.$val->PRICE." บาท \n";
}

}
$client->replyMessage(array(
    'replyToken' => $event['replyToken'],
    'messages' => array(
        array(
            'type' => 'text',
            'text' => "" . $oilp . ""
        )
    )
));

}
// end oil price
// start radio
if($message['text']  == "radio" || $message['text']  == "วิทยุ" || $message['text']  == "Radio"){
    $icecast_url = 'http://fm94.tv5.co.th:8000';
    $output = file_get_contents($icecast_url);
    $search = '#<div class="newscontent">.*?Point /(.*?)<.*?href="(.*?)">M3U<.*?Listeners.*?class="streamdata">(.*?)<.*?Song:.*?class="streamdata">(.*?)<.*?</div>#si';
    preg_match_all($search, $output, $matches);
    $j = count($matches[0]);
    for ($i = 0; $i < $j; $i++) {
        $point_name = $matches[1][$i];
        $pount_m3u = $icecast_url . $matches[2][$i];
        $point_listners_count = $matches[3][$i];
        $point_current_song = $matches[4][$i];
        //
        //echo 'mount point: <b>'.$point_name.'</b>';
        //echo 'm3u: <a href="' . $pount_m3u . '">' . $pount_m3u . '</a>';
        //echo 'listners_count: <b>' . $point_listners_count . '</b>';
        //echo 'point_current_song: <b>'. $point_current_song.'</b>';
        $arx = $point_listners_count;
    }
    
/*    

    for($i=0;$i<=5;$i++){
    $arx = $arx.$array['channel']['item'][$i]['title'];
    }
*/   
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text',
                'text' => "สถานีวิทยุ ททบ. FM 94MHz \n จำนวนผู้ฟังขณะนี้ " . $arx . " คน \n ติดตามรับฟังได้ทาง \n http://fm94.tv5.co.th:8000/fm94ch3.m3u"
            )
        )
    ));
  }

// end  radio
// $i[5] = GroupID
if($message['text']  == "lastmsg"){
    $file = file("mesglist.csv");   
    $frecho = '';
    $x=0;                      
    for ($i = max(0, count($file) - 40); $i < count($file); $i++) {
      $dt[] = str_getcsv($file[$i]) ;
      if($dt[$x][5] == 'C08928f2361f20aded46356913cc35b7a'){
        $frecho = $frecho."***".$dt[$x][1]."\n";
      }else{
        //$frecho = $frecho.$dt[$x][1]."\n";
      }
      $x++;
    }

    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text',
                'text' => "" . $frecho . ""
            )
        )
    ));
  }
  
//
if($message['text'] == "สวัสดี"){

    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text',
                'text' => "สวัสดีจ้า มีอะไรให้รับใช้"
            )
        )
    ));
  }
// end hello
/*
    # Message Type "Location"
    else if($message == "พิกัดสื่อสาร"){
		// 13.7855898,100.5324428     13.7905625  100.5257741
        $arrayPostData['replyToken'] = $arrayJson['events'][0]['replyToken'];
        $arrayPostData['messages'][0]['type'] = "location";
        $arrayPostData['messages'][0]['title'] = "ศูนย์โทรคมนาคม กรมการทหารสื่อสาร";
        $arrayPostData['messages'][0]['address'] =   "13.7905625,100.5257741";
        $arrayPostData['messages'][0]['latitude'] = "13.7905625";
        $arrayPostData['messages'][0]['longitude'] = "100.5257741";
        replyMsg($arrayHeader,$arrayPostData);
    }
    #  Message Type "Text + Sticker ใน 1 ครั้ง"
    else if($message == "ลาก่อน"){
        $arrayPostData['replyToken'] = $arrayJson['events'][0]['replyToken'];
        $arrayPostData['messages'][0]['type'] = "text";
        $arrayPostData['messages'][0]['text'] = "ได้โปรด อย่าทิ้งกันไป";
        $arrayPostData['messages'][1]['type'] = "sticker";
        $arrayPostData['messages'][1]['packageId'] = "1";
        $arrayPostData['messages'][1]['stickerId'] = "131";
        replyMsg($arrayHeader,$arrayPostData);
    }
*/
// start image  
if($message['text'] == "รูปน้องแมว"){
    $image_url = "https://i.pinimg.com/originals/cc/22/d1/cc22d10d9096e70fe3dbe3be2630182b.jpg";
    $client->replyMessage(array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'image',
                'originalContentUrl' => $image_url,
                'previewImageUrl' => $image_url
            )
        )
    ));
}
/// end image

if($message['text'] == "อัตราแลกเปลี่ยน"){
$response = '';
    if(($fp = fopen('http://www.thaigold.info/RealTimeDataV2/gtdata_.txt', 'r', false))) {
                    while ( ! feof($fp)){
                            $response .= fread($fp, 1024);
                    }
    }else {
     return;
    }
fclose($fp);

$bitres = '';
    if(($fpxx = fopen('https://bx.in.th/api/', 'r', false))) {
        while ( ! feof($fpxx)){
            $bitres .= fread($fpxx, 1024);
        }
    }else {
        return;
    }
fclose($fpxx);
$bitarray = json_decode($bitres, true);
$b2 = $bitarray[1]['last_price'];
$b3 = $bitarray[1]['change'];
//echo "G2= $g2  G3= $g3";


                      $goldarray = json_decode($response, true);
                      $g1 = $goldarray[2]['bid'];
                      $g2 = $goldarray[3]['bid'];
                      $g3 = $goldarray[4]['bid'];
                      $g4 = $goldarray[4]['ask'];
                      $g5 = $goldarray[4]['diff'];
                      $g6 = $g5." \n1BTC= $b2 THB Change: $b3";

                      $client->replyMessage(array(
                          'replyToken' => $event['replyToken'],
                          'messages' => array(
                              array(
                                  'type' => 'text',
                                  'text' => "THB/USD: $g2 บาท \nGold Buy/Sale: $g3/$g4 บาท เปลี่ยนแปลง $g6"
                              )
                          )
                      ));
}

                    if($message['text'] == "อุณหภูมิ"){
                        $dtem='';
                        $url = 'https://api.openweathermap.org/data/2.5/weather?units=metric&type=accurate&zip=10330,th&appid=9f7d6d0f7f9b053577d121f2062a39a2'; 
                        $contents = file_get_contents($url);
                        $clima=json_decode($contents);
                        $temp_max=$clima->main->temp_max;
                        $temp_min=$clima->main->temp_min;
                        $temp=$clima->main->temp;
                        $hum=$clima->main->humidity;
                        $weatherx=$clima->weather[0]->description;
                        $dtem=$dtem. "Temp Max: " . $temp_max ."C\n";
                        $dtem=$dtem. "Temp Min: " . $temp_min ."C \n";
                        $dtem=$dtem. "อุณหภูมิเฉลี่ย " . $temp ."C \n";
                        $dtem=$dtem. "ความชื้น  " . $hum ."\n";
                        $dtem=$dtem."ฟ้า: ".$weatherx." \n https://www.accuweather.com/th/th/thailand-weather";

                        $client->replyMessage(array(
                            'replyToken' => $event['replyToken'],
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => $dtem
                                )
                            )
                        ));
                      }

                    // start XX comment by nart
                    /*
                    if($message['text'] == "อุณหภูมิ"){
                      $response = '';
                      if(($fp = fopen('https://www.tmd.go.th/xml/weather_report.php?StationNumber=48455', 'r', false))) {
                        while ( ! feof($fp)){
                            $response .= fread($fp, 1024);
                        }
                      }
                      fclose($fp);
                      $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
                      $json = json_encode($xml);
                      $array = json_decode($json,TRUE);
                      $weatherdata = explode("\n",$array['channel']['item']['description']);
                      $cleanweather = preg_replace("/<[^>]*>/u", "", $weatherdata[1]);
                      $cleanweather = preg_replace("/[ ]{2,}/u", "", $cleanweather);

                      $client->replyMessage(array(
                          'replyToken' => $event['replyToken'],
                          'messages' => array(
                              array(
                                  'type' => 'text',
                                  'text' => $cleanweather
                              )
                          )
                      ));
                    }
                    // end XX comment by nart
                    */

                    break;
                    case 'image':
                    $fp = fopen ('images/'.$message['id'].'.jpg', 'w+');
                    $out = contactline("https://api.line.me/v2/bot/message/". $message['id'] ."/content");
                    fwrite($fp, $out);
                    fclose($fp);

                    $url = $Line_URI . $source['userId'];
                    $out = contactline($url);
                    $outarray = json_decode($out, true);

                    $fp = fopen ('profiles/'.$source['userId'].'.jpg', 'w+');
                    $out = contactline($outarray['pictureUrl']);
                    fwrite($fp, $out);
                    fclose($fp);

                    if (array_key_exists ('groupId', $source)){
                      $groupid = $source['groupId'];
                    } else {
                      $groupid = 'null';
                    }

                    writemsg($source['userId'], "image ".$message['id']." saved at ".'images/'.$message['id'].'.jpg' , $outarray['displayName'], 'profiles/'.$source['userId'].'.jpg', $groupid);
                break;
                case 'beacon':  // start beacon
                $beax='aaa';

                $client->replyMessage(array(
                    'replyToken' => $event['replyToken'],
                    'messages' => array(
                        array(
                            'type' => 'beacon',
                            'beacon' => $event[0][0]
                        )
                    )
                ));

                break;      // end beacon

                default:
                    error_log("Unsupporeted message type: " . $message['type']);
                    break;
            }
            break;

        default:
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};
?>
