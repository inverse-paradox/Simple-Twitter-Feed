<?php
/*
Plugin Name: Simple Twitter Feed
Description: Add twitter feed [ip_twitter user="InverseParadox" num="5" before="<li>" after="</li>" widget_before="<ul>" widget_after="</ul>"]
Author: Ryan Tulino
Version: 1.0.1
Author URI: http://inverseparadox.net
*/
define('SAVED_TWEET', realpath(__DIR__).'/savedTweet.php' );

function getTweets($handle, $num, $before, $after, $widget_before, $widget_after){  		
	$tweetArray = array();
	$url = "https://api.twitter.com/1/statuses/user_timeline.xml?screen_name=".$handle;	
	if(file_exists(SAVED_TWEET)){include(SAVED_TWEET);}
	$last_update = time() - (isset($last_update) ? $last_update : 0);
	//get tweet from file
	if ($last_update < 10000) {
		$tweetArray = unserialize(stripslashes($savedTweet));
	} elseif($xml = ip_get_xml($url)){
		//get tweet from twitter
		foreach($xml->status as $status){
			$tweetArray[] = makeLinks($status->text);
		}
		// Write tweet to file;
		file_put_contents(
			SAVED_TWEET, 
			"<?php\n\n\$savedTweet = \"".addslashes(serialize($tweetArray))."\";\n\n\$last_update = ".time().";\n\n?>"
		);
	}	
	if(!$tweetArray){
		$tweetArray = unserialize(stripslashes($savedTweet));
	}
	$count = 0;
	echo "<h5 class='twitter-handle'><a href='http://twitter.com/$handle' target='_blank'>Follow us on Twitter @$handle</a></h5>";
	echo $widget_before;
	foreach($tweetArray as $tweet){if($count++ == $num){break;}
		echo $before.$tweet.$after;
	}
	echo $widget_after;
}

function ip_get_xml($url){
	/*$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	$output = curl_exec($ch);
	curl_close($ch);
	return simplexml_load_string($output);
	*/
	return simplexml_load_file($url);
}

function makeLinks($tweetText) {

	// Check for URL's and make 'em links
	// The Regular Expression filter
	$reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	$reg_exAt = '/(?<=^|\s)@([a-z0-9_]+)/i';
	// preg_match($reg_exAt, $tweetText, $at)
	if(preg_match($reg_exUrl, $tweetText, $url)) { $hasUrl = true; }
	if(preg_match($reg_exAt, $tweetText, $at)) { $hasAt = true; }
	// Tweet has URLs, but no handles
	if($hasUrl == true && $hasAt = false) {
     	$tweetWithUrl = preg_replace($reg_exUrl, "<a href='".$url[0]."' rel='nofollow'>".$url[0]."</a>", $tweetText);
		$linkTweet = str_replace("\"", "'", "$tweetWithUrl");
		return $linkTweet;
	} 
	// Tweet has Handles, but no URLs
	elseif ($hasUrl == false && $hasAt = true) {
		$tweetWithHandle = preg_replace($reg_exAt, "<a href='http://twitter.com/".$at[0]."' rel='nofollow'>".$at[0]."</a>", $tweetText);
		$linkTweet = str_replace("\"", "'", "$tweetWithHandle");
		return $linkTweet;
	}
	// Tweet has both handles and URLs
	elseif ($hasUrl == true && $hasAt = true) {
		$tweetWithUrl = preg_replace($reg_exUrl, "<a href='".$url[0]."' rel='nofollow'>".$url[0]."</a>", $tweetText);
		$tweetWithUrlHandle = preg_replace($reg_exAt, "<a href='http://twitter.com/".$at[0]."' rel='nofollow'>".$at[0]."</a>", $tweetWithUrl);
		$linkTweet = str_replace("\"", "'", "$tweetWithUrlHandle");
		return $linkTweet;
	}
	else {
		// Tweet has no links or handles
		$linkTweet = htmlspecialchars("$tweetText", ENT_QUOTES);
 	 	return $linkTweet;
	}

}

add_shortcode('ip_twitter',function($args){
	getTweets(
		isset($args['user']) ? $args['user'] : 'InverseParadox',
		isset($args['num']) ? $args['num'] : 5,
		isset($args['before']) ? $args['before'] : '<li>',
		isset($args['after']) ? $args['after'] : '</li>',
		isset($args['widget_before']) ? $args['widget_before'] : '<ol class="twitter-feed">',
		isset($args['widget_after']) ? $args['widget_after'] : '</ol>'
	);									
});


?>
