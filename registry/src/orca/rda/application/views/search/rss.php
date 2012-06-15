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
$q = $_GET['q'];
$class = $_GET['classFilter'];
$group = $_GET['groupFilter'];
$subject = $_GET['subjectFilter'];
$licence = $_GET['licenceFilter'];
if($group!="All")$filter .= "/group=".$group;
if($subject!="All")$filter .= "/subject=".$subject;
if($licence!="All")$filter .= "/licence=".$licence;
$rss_channel_header = '
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'" rel="self" type="application/rss+xml" />
		<title>Research Data Australia search results for "'.$_GET['q'].'"</title>
		<link>'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'</link>
		<description>The following items reflect search results obtained from Research Data Australia when the search term "'.$_GET['q'].'" was used.</description>
		<language>en</language>
		<image>
			<url>http://services.ands.org.au:8080/ands_logo_white.jpg</url>
			<title>Research Data Australia search results for "'.$_GET['q'].'</title>
			<link>'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'</link>
		</image>
';
echo $rss_channel_header;

foreach ($rssArray AS $item)
{
//print(strtotime("2012-1-6"));
	if($item['type'] == "digest")
	{

		echo "			<item>\n";
		echo "				<title>Digest: " . $item['group'] . " updated multiple records on ".$item['updated_date']."</title>\n";			
		echo "				<description>The registry has had multiple records updated in data source: " . $item['key'] . "${NL}${NL}Updated Records include:${NL}${NL}"; 
			foreach ($item['updated_items'] AS $i) 
			{

				echo "&lt;a href='".$rdaInstance . "view/?key=" . rawurlencode($i['key']) ."'>&lt;b>".$i['list_title']."&lt;/b>&lt;/a>${NL}${NL}";
			}
		echo "				</description>\n";
		echo "				<link>" . $rdaInstance . "search#!/q=" . $q ."/group=".urlencode($item['group'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc</link>\n";	
		echo "				<guid>" . $rdaInstance . "search#!/q=" . $q ."/group=".urlencode($item['group'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc</guid>\n";		
		echo "				<author>" . $item['key'] . "</author>\n";									
		echo "				<pubDate>".date('r', strtotime($item['updated_items'][0]['date_modified']))."</pubDate>\n";
		echo "			</item>\n";
	}
	else
	{

		echo "			<item>\n";
		echo "				<title>" . $item['list_title'] . "</title>\n";			
		echo "				<description>" . (isset($item['description_value'][0]) ? str_replace("<","&lt;",$item['description_value'][0]) : "No description")  . "${NL}${NL}";
		echo "				</description>\n";		
		echo "				<link>" . $rdaInstance . "view/?key=" . rawurlencode($item['key']) . "</link>\n";	
		echo "				<guid>" . $rdaInstance . "view/?key=" . rawurlencode($item['key']) . "</guid>\n";	
		echo "				<author>" . $rdaInstance . "view/?key=" . rawurlencode($item['key']) . "</author>\n";						
		echo "				<pubDate>".date('r', strtotime($item['date_modified']))."</pubDate>\n";
		echo "			</item>\n";
	}
}
	
echo "		</channel>\n";
echo "</rss>";
		
?>