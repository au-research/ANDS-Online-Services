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
if($group!="All")$filter .= "/group=".urlencode($group);
if($subject!="All")$filter .= "/subject=".urlencode($subject);
if($licence!="All")$filter .= "/licence=".urlencode($licence);

$feed_title = "Research Data Australia search results for collections matching " . $_GET['q'];
$feed_title_suffix = '';
if ($subjectSearchTitleSuffix) {
	$feed_title_suffix = " with subject(s): " . $subjectSearchTitleSuffix;
}
$feed_title .= $feed_title_suffix;


$atom_feed_header = '
	<feed xmlns="http://www.w3.org/2005/Atom">
  	<id>'.str_replace("&","",str_replace("=","",$rdaInstance.$_SERVER['REQUEST_URI'])).'</id>
  	<title>'.$feed_title.'</title>
  	<updated>'.date('Y-m-d',time()). "T" .date('h:s:i', time()).'Z</updated>
  	<link rel="alternate" href="'.$rdaInstance.'search#!/q='.rawurlencode($q).'/p=1/tab=collection'.$filter.'" />
  	<link rel="self" type="application/atom+xml" href="'.$rdaInstance.'search/atom/?q='.rawurlencode($q).urlencode('&classFilter='.$class.'&typeFilter=All&groupFilter='.$group.'&subjectFilter='.$subject.'&licenceFilter='.$licence.'&subscriptionType=atom').'" />
    <logo>'.$rdaInstance.'/img/icon/ands_boxes.jpg</logo>
  	<author>
    	<name>Research Data Australia</name>
    	<uri>http://researchdata.ands.org.au</uri>
    	<email>services@ands.org.au</email>
  	</author>
';
echo $atom_feed_header;

foreach ($rssArray AS $item)
{
	if($item['type'] == "digest")
	{
		echo "			<entry>\n";
		echo "				<title>Multiple Collections were added to Research Data Australia by  " . $item['key'] . " on ".$item['updated_date']."</title>\n";	
		echo "				<id>" . $rdaInstance . "search#!/q=" . $q ."/dataSource=".urlencode($item['key'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc</id>\n";	
    	echo "				<updated>".date("c",strtotime($item['updated_items'][0]['date_modified']))."</updated>\n";							
		echo "				<content type='html'>" . $item['key'] . " added more than 5 records to Research Data Australia on ".$item['updated_date'].". These records have been rolled into a single digest entry for convenience.{$NL}Please navigate to
		&lt;a href='".$rdaInstance . "search#!/q=" . $q ."/group=".urlencode($item['key'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc'>	search results&lt;/a> to see the full listing of the records in Research Data Australia {$NL}";
			$count=1;
			foreach ($item['updated_items'] AS $i) 
			{
				if($count<4)
				{
					echo "&lt;a href='".$rdaInstance . "view/?key=" . rawurlencode($i['key']) ."'>&lt;b>".$i['list_title']."&lt;/b>&lt;/a>${NL}";
				}
				$count++;
			}	
		echo "				</content>\n";
		echo "				<link href='".$rdaInstance . "search#!/q=" . $q ."/dataSource=".urlencode($item['key'])."/p=1/tab=" . $class . $filter."/resultSort=date_modified%20desc'></link>\n";		
		echo "			</entry>\n";
	}
	else
	{
		$brief = 'no';
		for($i=0;$i<count($item['description_type']);$i++)
		{
			if($item['description_type'][$i]=='brief') 
			{ 
				$item['description_value'][0] = $item['description_value'][$i];
				$brief = 'yes';
			}
			
		}
		if($brief == 'no')
		{
			for($i=0;$i<count($item['description_type']);$i++)
			{
				if($item['description_type'][$i]=='full') 
				{ 
					$item['description_value'][0] = $item['description_value'][$i];
				}
			
			}		
		}
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