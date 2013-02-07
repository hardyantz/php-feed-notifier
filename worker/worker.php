<?php
$worker= new GearmanWorker();
$worker->addServer();

$worker->addFunction("feed_url", "deliver_mail_feed");

while($worker->work())
{
  if ($worker->returnCode() != GEARMAN_SUCCESS)
  {
    echo "return_code: " . $worker->returnCode() . "\n";
    break;
  }
}

define("LATEST_PATH","/data/detikcom/worker/latest.txt");

function parse_feed($url) { 
 
	//PARSE RSS FEED
        $feedeed = implode('', file($url));
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $feedeed, $valueals, $index);
        xml_parser_free($parser);
 
	//CONSTRUCT ARRAY
        foreach($valueals as $keyey => $valueal){
            if($valueal['type'] != 'cdata') {
                $item[$keyey] = $valueal;
			}
        }
 
        $i = 0;
 
        foreach($item as $key => $value){
 
            if($value['type'] == 'open') {
 
                $i++;
                $itemame[$i] = $value['tag'];
 
            } elseif($value['type'] == 'close') {
 
                $feed = $values[$i];
                $item = $itemame[$i];
                $i--;
 
                if(count($values[$i])>1){
                    $values[$i][$item][] = $feed;
                } else {
                    $values[$i][$item] = $feed;
                }
 
            } else {
                $values[$i][$value['tag']] = $value['value'];  
            }
        }
 
	//RETURN ARRAY VALUES
        return $values[0];
}


function get_latest() {
    $fp = file_get_contents("/data/detikcom/worker/latest.txt");
    return $fp;
}


function deliver_newest($job) {

    $sml = parse_feed($job);
    
    $get_latest = get_latest();
    //print_r($sml);

    $feed_get = array_reverse($sml['RSS']['CHANNEL']['ITEM']);

    foreach($feed_get as $item) {
      
      $get_all[] = " => {$item['LINK']} = {$item['TITLE']} = {$link}\n";
      
      if(trim($get_latest) != "" ){
        if(md5($item['LINK']) == trim($get_latest)) {
            $_deliver = true;
        }
      }
      
      if($_deliver == true) {
            $get_all = array();
            $_deliver = false;
      }
    
      $latest = md5($item['LINK']);
    }

    save_latest($latest);
    
    return array_reverse($get_all);
}

function save_latest ($string) {
  $fp = fopen("/data/detikcom/worker/latest.txt", 'w+');
  fwrite($fp, $string);
  fclose($fp);
}


function deliver_mail_feed ($job) {
  
    $get_news = deliver_newest($job->workload());
    // print_r($get_news);
    
      foreach($get_news as $value) {
          echo $value."\n";
          $fp = fopen("/data/detikcom/worker/feed.txt", "a+");
          fwrite($fp, $value);
          fclose($fp);
      }
  
}

//
//$job = "http://rss.detik.com/index.php/sepakbola";
//deliver_mail_feed($job);
?>
