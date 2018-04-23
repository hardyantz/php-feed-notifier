<?php

error_reporting(~E_NOTICE);

// define constant & variable
$check	= 600;			// if running as a service, check for every n second duration
$argv	= getopt("u:t:");
$url 	= isset($_SERVER["argv"][1])?$_SERVER["argv"][1]:"u";
$to 	= isset($_SERVER["argv"][2])?$_SERVER["argv"][2]:"t";
$subject = "latest feed for {$url}";
$latest_feed = "latest_".md5($url).".txt";  	// save for the latest 

/**
 * Parse Feed
 * @author Buyung hardyantz <hardyantz@yahoo.com>
 * @copyright copyright (c) 2013 , hardyantz
 * @since 1.1.0
 * @param (string) url_feed
 * @return (array) array feed content
 */
function parse_feed($url) { 
 
    //PARSE RSS FEED
    $feed = implode('', file($url));
    $parser = xml_parser_create();
    xml_parse_into_struct($parser, $feed, $valueall, $index);
    xml_parser_free($parser);

    //CONSTRUCT ARRAY
    foreach($valueall as $key => $value){
	if($value['type'] != 'cdata') {
	    $item[$key] = $value;
	  }
    }

    $i = 0;

    foreach($item as $key => $_value){

	if($_value['type'] == 'open') {

	    $i++;
	    $items[$i] = $_value['tag'];

	} elseif($_value['type'] == 'close') {

	    $feed = $values[$i];
	    $item = $items[$i];
	    $i--;

	    if(count($values[$i])>1){
		$values[$i][$item][] = $feed;
	    } else {
		$values[$i][$item] = $feed;
	    }

	} else {
	   $values[$i][$_value['tag']] = $_value["value"];  
	}
    }

    //RETURN ARRAY VALUES
    return $values[0];
}

/**
 * get Latest
 * @author Buyung hardyantz <hardyantz@yahoo.com>
 * @copyright copyright (c) 2013 , hardyantz
 * @since 1.1.0
 * @return (string) latest feed.
 */
function get_latest() {
    global $latest_feed;
    $fp = @file_get_contents($latest_feed);
    return $fp;
}

/**
 * Deliver Newest
 * @author Buyung hardyantz <hardyantz@yahoo.com>
 * @copyright copyright (c) 2013 , hardyantz
 * @param (string) url_feed
 * @since 1.1.0
 * @return (array) output for notification .
 */
function deliver_newest($job) {

    $sml = parse_feed($job);
    
    $get_latest = get_latest();
    //print_r($sml);

    $feed_get = array_reverse($sml['RSS']['CHANNEL']['ITEM']);

    foreach($feed_get as $item) {
      
      $get_all[] = " => <a href = \"{$item['LINK']}\" target=\"blank\"> {$item['TITLE']} </a><br>"; // here's the output variable, you can change whetever you like for $item 
      
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

/**
 * save latest feed
 * @author Buyung Hardyansyah <hardyantz@yahoo.com>
 * @since 1.1.0
 * @param string md5(date_latest)
 * @return not output & no return.
 */
function save_latest ($string) {
  global $latest_feed;  
  $fp = fopen($latest_feed, 'w+');
  fwrite($fp, $string);
  fclose($fp);
}

/**
 * deliver feed
 * @author Buyung hardyantz <hardyantz@yahoo.com>
 * @copyright copyright (c) 2013 , hardyantz
 * @since 1.1.0
 * @return send mail.
 */
function deliver_mail_feed ($feed_url) {
    global $to, $subject;
    
    $get_news = deliver_newest($feed_url);
    
      foreach($get_news as $value) {

	  $get_feed .= $value."<br>";

      }
    //
 
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    
    // Additional headers
    $headers .= 'To: '.$to.' <'.$to.'>' . "\r\n";
    $headers .= 'From: who <admin@example.com>' . "\r\n";

    if(!mail($to, $subject, $get_feed, $headers)) echo "can't send mail";  

}

function oldTheme() {
	return true;
}

// you can run as service or crontab for each n second
while(true){
    deliver_mail_feed($url);  
    sleep($check);  // check each for every second
}

/**

run ini console
$ php feed-notifier.php $url $mail

*/
?>
