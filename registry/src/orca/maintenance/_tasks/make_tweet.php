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
		$message .= "Unable to send Tweet to Twitter API (perhaps duplicate?): " . $e->getMessage() . PHP_EOL;
	}
	
	return "";
		
}
	
function makeTweet($tweet_content)
{
       	$twitter_client = new Twitter('A28fRaEFTNdgadyosvraqg', '3W7wJK4xKklOi3VVhj2x9WsQdyK2Uh1iuEOlryjsX6I');
		$twitter_client->setOAuthToken('618889999-2bDxhcuKvccysE3qOK2zBDGiWNTuoyqPvjCOZGRu');
		$twitter_client->setOAuthTokenSecret('oD5gsdTT0MexnDJDFdB99tA797DGWvApMj2jKZaDb8');

       	$twitter_client->statusesUpdate($tweet_content);
}
	