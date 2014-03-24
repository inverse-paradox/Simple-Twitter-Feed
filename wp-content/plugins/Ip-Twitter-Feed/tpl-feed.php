<?php
/**
 * @param $feed json object - api response
 */
?>
<h5 class="twitter-handle">
	<a href="http://twitter.com/<?php echo $feed[0]->user->screen_name;?>">@<?php echo $feed[0]->user->screen_name;?></a>
</h5>
<ol>
	<?php foreach($feed as $item): ?>
		<li><?php echo apply_filters('twitter_links', $item->text);?></li>
	<?php endforeach;?>
</ol>