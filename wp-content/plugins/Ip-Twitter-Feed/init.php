<?php
/*
Plugin Name: Ip Twitter Feed
Description: Add twitter feed using Twitter API 1.0 [ip_twitter user="InverseParadox" num="5" before="<li>" after="</li>" widget_before="<ul>" widget_after="</ul>"]
Author: Inverse Paradox
Version: 1.0.2
Author URI: http://inverseparadox.net
*/


// settings page
add_action('admin_menu', function(){
	add_options_page(
		"", 
		"", 
		"manage_options", 
		dirname(plugin_basename(__FILE__)), 
		function(){
			if(isset($_GET['settings'])){
				include dirname(__FILE__).'/tpl-settings.php';
			} else {
				echo "<h1>404 Not Found</h1>";
				echo "<script>window.location='".get_admin_url()."';</script>";
			}
		}
	);
});


// settings fields
add_action('admin_init', function(){
	$settings_sections = array(
		'ip_twitter' => array(
			'title' => 'Twitter Feed Settings:',
			'fields' =>array(
				'consumer_key' => array(
					'label' => 'Consumer key'
				),
				'consumer_secret' => array(
					'label' => 'Consumer secret'
				),
				'request_url' => array(
					'label' => 'Request URL'
				),
				'access_token' => array(
					'label' => 'Access token'
				),
				'access_token_secret' => array(
					'label' => 'Access token secret'
				)
			)
		)
	);
	$settings_page = 'ip_twitter';
	foreach ($settings_sections as $settings_section => $data) {
		$section_title = $data['title'];
		$settings = $data['fields'];
		add_settings_section( 
			$settings_section, 
			$section_title, 
			'', 
			$settings_page
		);
		foreach ($settings as $key => $val) {
			$setting = $settings_section.'_'.$key;
			if (isset($_POST[$setting])) {
				update_option( $setting, $_POST[$setting] );
			}
			register_setting(
				$settings_section, 
				$setting
			);
			add_settings_field(
			    $setting, 
			    $val['label'], 
			    function() use ($setting, $val){
					//settings_fields('ip_twitter');
					echo ' <input type="text" id="'.$setting.'" name="'.$setting.'" value="'.get_option($setting).'" placeholder="Empty" class="large-text" />';  
			    }, 
			    $settings_page,
			    $settings_section
			);	
		}
	}	
});


// settings link
add_filter("plugin_action_links_".plugin_basename(__FILE__), function($links) { 
	$links[] = '<a href="options-general.php?page='.dirname(plugin_basename(__FILE__)).'&settings=true">Settings</a>'; 
	return $links; 
} );


// shortcode
add_shortcode('ip_twitter', function($args){
	$last_update = get_option('ip_twitter_feed_last_update', 0);
	if(time() - $last_update > 86400){
		$defaults = array(
			'user' => 'InverseParadox',
			'num' => 5,
			'before' => '<li>',
			'after' => '</li>',
			'widget_before' => '<ol>',
			'widget_after' => '</ol>'			
		);
		$options = array_merge($defaults, $args);
		require_once('TwitterAPI.php');
		$settings = array(
		    'oauth_access_token' => get_option('ip_twitter_access_token'),
		    'oauth_access_token_secret' => get_option('ip_twitter_access_token_secret'),
		    'consumer_key' => get_option('ip_twitter_consumer_key'),
		    'consumer_secret' => get_option('ip_twitter_consumer_secret')
		);
		$url = get_option('ip_twitter_request_url');
		$fields = array(
			'screen_name' => $options['user'],
			'count' => $options['num']
		);
		$requestMethod = 'GET';
		$twitter = new TwitterAPI($settings);
		$twitter_feed = $twitter->setGetfield('?'.http_build_query($fields))
			->buildOauth($url, $requestMethod)
			->performRequest();
		update_option('ip_twitter_feed', $twitter_feed);
		update_option('ip_twitter_feed_last_update', time());
	} else {
		$twitter_feed = get_option('ip_twitter_feed', 'Error retreiving tweets');	
	}
	$feed = json_decode($twitter_feed);
	include dirname(__FILE__).'/tpl-feed.php';
});

add_filter('twitter_links', function($tweetText){
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
});
