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
<p>
<span id="connections-realnumfound">1</span>
<?php /*
$theContributor =  getInstitutionPage($theGroup);
if($person['numfound']==0&&$group['numfound']==0&&$collection['numfound']==0&&$service['numfound']==0&&$activity['numfound']==0&&!$theContributor)
{
 echo '<span id="connections-realnumfound">0</span>';
}

// display the researcher connections

$person["heading"] = '<h3><img  class="icon-heading-connections" src="'.base_url().'/img/icon/party_one_16.png"/>Researchers</h3>';
$person["title"] = 'researcher';
$person["titles"] = 'researchers';
$person["id"] = 'person';
$person["action_id"] = 'type_id="person"';
// display the research group connections

$group["heading"] = '<h3><img  class="icon-heading-connections" src="'.base_url().'/img/icon/party_multi_16.png"/>Research Groups</h3>';
$group["title"] = 'research group';
$group["titles"] = 'research groups';
$group["id"] = 'group';
$group["action_id"] = 'type_id="group"';
// display the collection connections

$collection["heading"] = '<h3><img  class="icon-heading-connections" src="'.base_url().'/img/icon/collections_16.png"/>Collections</h3>';
$collection["title"] = 'collection';
$collection["titles"] = 'collections';
$collection["id"] = 'collection';
$collection["action_id"] = 'class_id="collection"';
// display the service connections

$service["heading"] = '<h3><img  class="icon-heading-connections" src="'.base_url().'/img/icon/services_16.png"/>Services</h3>';
$service["title"] = 'service';
$service["titles"] = 'services';
$service["id"] = 'service';
$service["action_id"] = 'class_id="service"';
// display the activity connections

$activity["heading"] = '<h3><img  class="icon-heading-connections" src="'.base_url().'/img/icon/activities_16.png"/>Activities</h3>';
$activity["title"] = 'activity';
$activity["titles"] = 'activities';
$activity["id"] = 'Activity';
$activity["action_id"] = 'class_id="activity"';

if($thisClass=='party' or $thisClass=='group')
{
	displayConnection($collection,$externalKeys);
	displayConnection($service,$externalKeys);
	displayConnection($activity,$externalKeys);
	displayConnection($person,$externalKeys);
	displayConnection($group,$externalKeys);
}elseif($thisClass=='collection')
{
	displayConnection($person,$externalKeys);
	displayConnection($group,$externalKeys);
	displayConnection($service,$externalKeys);
	displayConnection($activity,$externalKeys);
	displayConnection($collection,$externalKeys);
}elseif($thisClass=='service')
{
	displayConnection($person,$externalKeys);
	displayConnection($group,$externalKeys);
	displayConnection($collection,$externalKeys);
	displayConnection($activity,$externalKeys);
	displayConnection($service,$externalKeys);
}elseif($thisClass=='activity')
{
	displayConnection($person,$externalKeys);
	displayConnection($group,$externalKeys);
	displayConnection($collection,$externalKeys);
	displayConnection($service,$externalKeys);
	displayConnection($activity,$externalKeys);
}

function displayConnection($groups,$externalKeys)
{

if(isset($groups)&&$groups['numfound']>0){

	echo $groups['heading'];
	//echo "<pre>";
	//print_r($groups['json']);
	//echo "</pre>";
	//exit();
	if($groups['numfound']>1)$title = $groups['titles']; else $title = $groups['title'];

	$max = 5;
	$seeMore = '';
	if($groups['numfound']<6)
	{
		$max =$groups['numfound'];
	}else{
		$seeMore = "See More...";
	}
	echo '<ul class="connection_list">';
	for($i=0;$i<$max;$i++)
	{
		$autoLink = '';
		$autoLinkTitle = '';
		if($externalKeys)
		{
			for($j=0;$j<count($externalKeys);$j++)
			{
				if($groups['json']->{'response'}->{'docs'}[$i]->{'key'}==$externalKeys[$j])
				{
					$autoLink = '<br /><span class="faded">(Automatic link)</span>';
					$autoLinkTitle = '(Automatic link)';
				}

			}
		}

		$logostr = '';
		if(isset($groups['json']->{'response'}->{'docs'}[$i]->{'description_type'}))
		{
			if($description_types = $groups['json']->{'response'}->{'docs'}[$i]->{'description_type'})
			{
				$description_value =  $groups['json']->{'response'}->{'docs'}[$i]->{'description_value'};
				$logostr = '';
				for($j=0;$j<count($description_types);$j++)
				{
					if($description_types[$j]=='logo')
					{
						$logostr = '<div><img id="party_logo"  style="max-width:130px;max-height:63px;" src="'.strip_tags($description_value[$j]).'"/></div>';
					}
				}

			}
		}
		$rda_link = ($groups['json']->{'response'}->{'docs'}[$i]->{'url_slug'} ? $groups['json']->{'response'}->{'docs'}[$i]->{'url_slug'} : 'view/?key='.urlencode($groups['json']->{'response'}->{'docs'}[$i]->{'key'}));
		echo '<li><a href="'.base_url().$rda_link.'" title="'.findRelationship($groups['json']->{'response'}->{'docs'}[$i]->{'key'},$groups['relatedKey'],$groups['relationship']).' '.$autoLinkTitle.'">';
		echo $groups['json']->{'response'}->{'docs'}[$i]->{'display_title'};
		echo '</a>'.findRelationshipDescription($groups['json']->{'response'}->{'docs'}[$i]->{'key'},$groups['relatedKey'],$groups['relationship_description']).$autoLink.$logostr.'</li>';
	}
	echo '</ul>';
	if($seeMore)
	{
		echo '<a href="javascript:void(0);" class="connections_NumFound" '.$groups["action_id"].'>View All <span id="collectionconnections-realnumfound"> '. ($groups['numfound']).'</span> connected '.$title.'</a>';
	}
}

}
showContributors($theGroup);
function showContributors($theGroup)
{
	$theGroupKey = getInstitutionPage($theGroup);

	if($theGroupKey)
	{
		$theLogo='';
		if($logoStr = getPageLogo($theGroupKey)){
	     $theLogo ='   <br /><img id="party_logo" style="max-width:130px;" src="'.strip_tags($logoStr).'" alt="Party Logo" />';
		}
		echo '<h3><img  class="icon-heading-connections" src="'.base_url().'/img/icon/party_multi_16.png"/>Contributed by</h3>';
		echo '<ul>';
		echo '<li><a href="'.base_url().'view/group/?group='.urlencode($theGroupKey).'&groupName='.$theGroup.'" title="Contributor group">'.$theGroup.'</a>'.$theLogo.'</li>';
		echo '</ul>';
	}
}
function findRelationship($key, $relatedKeys, $relationships){

	for($i=0;$i<count($relatedKeys);$i++)
	{
		if($relatedKeys[$i]==$key)
			return $relationships[$i];
	}
}
function findRelationshipDescription($key, $relatedKeys, $relationshipDescriptions){

	for($i=0;$i<count($relatedKeys);$i++)
	{
		if($relatedKeys[$i]==$key&&isset($relationshipDescriptions[$i]))
		{
			if($relationshipDescriptions[$i]!='null'&&strlen($relationshipDescriptions[$i])<64){
				$return = "<br /><span class='faded'>".$relationshipDescriptions[$i]."</span>";
				return $return;
			}
		}
	}
}
*/ ?>
</p>