<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';

// Get the record from the database.
$task = getQueryValue('task');
if($task=='')
{
	$task = $_POST['task'];
}

if($task=='getUser')
{
	global $ip;
	echo "we are in";
	$user = getLoggedInUser();
	if($user)
	{
		echo "Logged in user: ".$user." From ip :".$ip;
	}else{

		echo $ip;
	}
}elseif($task=="resync")
{
	$key = getQueryValue('key');
	$datasource_key = getQueryValue('datasource_key');
	$return = syncKey($key, $datasource_key);
}
elseif($task=="getTheCaptcha")
{
	$date = date("Ymd");
	$rand = rand(0,9999999999999);
	$height = "50";
	$width  = "150";
	$img    = "$date$rand-$height-$width.jpgx";
	echo "<input type='hidden' name='img' value='".$img."' id='img'>";
	echo "<img src='http://www.opencaptcha.com/img/$img' height='$height' alt='captcha' width='$width' border='0' /><br />
	<span id='captchaError' class='tagFormError'> <br /></span><br />";
	echo "<input type='text' name='code' id='code' value='Enter The Code' size='20' />";

}
elseif($task="checkTheCaptcha")
{
	$response = getQueryValue('response');
	$img = getQueryValue('img');
	
	if(file_get_contents("http://www.opencaptcha.com/validate.php?ans=".$response."&img=".$img)=='pass') {
	  	 echo "OK";
	} else {
  		echo "FAILED";
	}
}
?>