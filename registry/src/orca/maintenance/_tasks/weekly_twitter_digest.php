<?php
if (!defined('eTWITTER_ENABLED')) { define ('eTWITTER_ENABLED', true); }
global $rda_root, $default_protocol, $host, $twitter_feed_rss;
$twitter_feed_rss = $default_protocol . "://" . $host . "/" . $rda_root . "/search/rss_twitter";

function task_weekly_twitter_digest($task)
{
	$message = '';
	if (!eTWITTER_ENABLED)
	{
		throw new Exception("Twitter is not enabled in maintenance/_tasks/weekly_twitter_digest.php");
		return;
	}
	
	// Fetch updated subject codes from the Twitter-style RSS feed (includes hashtag, etc)
	$tweets = getUpdatedSubjectsForTwitter();
	$message .= "Sending " . count($tweets) . " tweet(s)" . PHP_EOL;
	
	$batch_count = 0;
	$size_per_batch = 3;
	$delay_per_batch = 10; // in minutes (should be greater than 1)
	foreach ($tweets AS $tweet)
	{
		$batch_count++;
		addNewTask('MAKE_TWEET', "Queued Tweet from task #" . $task['task_id'], $tweet, '', null, floor(($batch_count-1)/$size_per_batch) * $delay_per_batch . " minutes");
	}
	
	addNewTask($task['method'], "Requeued weekly RDA tweets from #" . $task['task_id'], '', '', null, "7 days");
	return $message;
}

function getUpdatedSubjectsForTwitter()
{
       	global $twitter_feed_rss;
       	$twitter_feed = new SimpleXMLElement(file_get_contents($twitter_feed_rss));

       	$queued_tweets = array();
       	foreach ($twitter_feed->channel->item as $rss_item) {
               	$queued_tweets[] = $rss_item->title; //. " " . $rss_item->link;   // link currently disabled, twitter doesn't like us!!
       	}

       	return $queued_tweets;
}

