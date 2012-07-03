<?php
/**
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/
?>
<?php
$NL = '&lt;br/>';
$rdaInstance = base_url();
$filter = '';

$feed_title = "Collections added to Research Data Australia in Twitter Format";

$rss_channel_header = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'" rel="self" type="application/rss+xml" />
		<title>'.$feed_title.'</title>
		<link>'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'</link>
		<description>The following items represent collection records added to Research Data Australia with ANZSRC-FOR codes (within the past 7 days)</description>
		<language>en</language>
		<image>
			<url>'.$rdaInstance.'/img/icon/ands_boxes.jpg</url>
			<title>'.$feed_title.'</title>
			<link>'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'</link>
		</image>
';
echo $rss_channel_header;

foreach ($rssArray AS $item)
{

	if($item['type'] == "twitter")
	{
		// depluralise
		$collection_count = $item['count'] . ($item['count'] > 1 ? " collections have " : " collection has ");
		echo "			<item>\n";
		echo "				<title>".$collection_count."been added to Research Data Australia with subject #ANZSRC".$item['code']."</title>\n";
		echo "				<description>".$collection_count."been added to Research Data Australia with subject #ANZSRC".$item['code'] ."</description>\n";
		echo "				<link>" . $rdaInstance . "search#!/q=*:*/p=1/tab=collection/subject=".rawurlencode($item['resolved_uri'])."/resultSort=date_modified%20desc</link>\n";
		echo "				<guid>" . $rdaInstance . "search#!/q=*:*/p=1/tab=collection/subject=".rawurlencode($item['resolved_uri'])."/resultSort=date_modified%20desc</guid>\n";
		echo "				<author>".$item['resolved_subject']."</author>\n";
		echo "				<pubDate>".date('r')."</pubDate>\n";
		echo "			</item>\n";
	}
}

echo "		</channel>\n";
echo "</rss>";

?>