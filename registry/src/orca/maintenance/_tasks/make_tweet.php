<?php
include_once('orca/maintenance/_tasks/_twitter_class.php');

function task_make_tweet($task)
{
	$tweet = $task['registry_object_keys']; // tweet data stored in task regobjkeys field, should've left these generic...
	try
	{
       	makeTweet($tweet);
       	sleep(1);
	}
	catch (TwitterException $e)
	{
		echo "Unable to send Tweet to Twitter API (perhaps duplicate?): " . $e->getMessage() . PHP_EOL;
	}
	
	return "";
		
}
	
function makeTweet($tweet_content)
{
       	$twitter_client = new Twitter(TWITTER_CONS_KEY,TWITTER_CONS_SECRET);
		$twitter_client->setOAuthToken(TWITTER_OAUTH_TOKEN);
		$twitter_client->setOAuthTokenSecret(TWITTER_OAUTH_SECRET);

       	$twitter_client->statusesUpdate($tweet_content);
}
	