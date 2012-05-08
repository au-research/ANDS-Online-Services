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
if($group!="All")$filter .= "/group=".$group;
if($subject!="All")$filter .= "/subject=".$subject;

$atom_feed_header = '
	<feed xmlns="http://www.w3.org/2005/Atom">
  	<id>'.str_replace("&","",str_replace("=","",$rdaInstance.$_SERVER['REQUEST_URI'])).'</id>
  	<title>Research Data Australia search results for "'.$_GET['q'].'"</title>
  	<updated>'.date('Y-m-d',time()). "T" .date('h:s:i', time()).'Z</updated>
  	<link rel="self" href="'.$rdaInstance.rawurlencode($_SERVER['QUERY_STRING']).'" type="application/atom+xml" />
  	<author>
    	<name>ANDS</name>
    	<uri>http://www.ands.org.au</uri>
    	<email>services@ands.org.au</email>
  	</author>
';
echo $atom_feed_header;

foreach ($rssArray AS $item)
{
	if($item['type'] == "digest")
	{
		echo "			<entry>\n";
		echo "				<title>Digest: " . $item['key'] . " updated multiple records</title>\n";	
		echo "				<id>" . $rdaInstance . "search#!/q=" . $q ."/dataSource=".urlencode($item['key'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc</id>\n";	
    	echo "				<updated>".date("c",strtotime($item['updated_items'][0]['date_modified']))."</updated>\n";							
		echo "				<content type='html'>The registry has had multiple records updated in data source: ".$item['key']."${NL}${NL}Updated Records include:${NL}${NL}";
				foreach ($item['updated_items'] AS $i) 
			{
				echo "&lt;a href='".$rdaInstance . "view/?key=" . rawurlencode($i['key']) ."'>&lt;b>".$i['list_title']."&lt;/b>&lt;/a>${NL}${NL}";
			}	
		echo "				</content>\n";
		echo "				<link href='".$rdaInstance . "search#!/q=" . $q ."/dataSource=".urlencode($item['key'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc'></link>\n";		
		echo "			</entry>\n";
	}
	else
	{
		echo "			<entry>\n";
		echo "				<title>" . $item['list_title'] . "</title>\n";	
		echo "				<id>" . $rdaInstance . "view/?key=" . rawurlencode($item['key']) . "</id>\n";		
    	echo "				<updated>".date("c",strtotime($item['date_modified']))."</updated>\n";	
		echo "				<content type='html'>" . (isset($item['description_value'][0]) ? str_replace("<","&lt;",$item['description_value'][0]) : "No description")  . "${NL}${NL}";
		echo "				</content>\n";		
		echo "				<link href='" . $rdaInstance . "view/?key=" . rawurlencode($item['key']) . "'></link>\n";		
		echo "			</entry>\n";
	}
}
	
echo "</feed>";
		
?>