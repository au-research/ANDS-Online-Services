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

function exportDoiCreators($doi_id)
{
	$creators = getDoiCreators($doi_id);
	if($creators)
	{
		$outstr = '<creators>';			
		foreach($creators as $creator)
		{
			$outstr .= '
			<creator><creatorName>'.$creator['creator_name'].'</creatorName>';
			if($creator['name_identifier_scheme'])
			{
				$outstr .='
				<nameIdentifier nameIdentifierScheme="'.$creator['name_identifier_scheme'].'">'.$creator['name_identifier'].'</nameIdentifier>';
			}
			$outstr .= '</creator>';
		}
		$outstr .='
		</creators>';
	}	
	return $outstr;
}

function exportDoiTitles($doi_id)
{
	$titles = getDoiTitles($doi_id);
	if($titles)
	{
		$outstr = '<titles>';			
		foreach($titles as $title)
		{
			$outstr .= '
			<title';
			if($title['title_type'])
			{
				$outstr .= ' titleType="'.$title['title_type'].'"';
			}
			$outstr .='>'.$title['title'].'</title>';					
		}
		$outstr .='
		</titles>';
	}	
	return $outstr;
}

function exportDoiPublisher($doi_id)
{
	$publisher = getDoiPublisher($doi_id);
	if($publisher)
	{
		$outstr = '<publisher>'.$publisher.'</publisher>';
	}
	return $outstr;
}

function exportDoiPublicationYear($doi_id)
{
	$publication_year = getDoiPublicationYear($doi_id);
	if($publication_year)
	{
		$outstr = '<publicationYear>'.$publication_year.'</publicationYear>';
	}
	return $outstr;
}

function exportDoiSubjects($doi_id)
{
	$subjects = getDoiSubjects($doi_id);
	if($subjects)
	{
		$outstr = '<subjects>';			
		foreach($subjects as $subject)
		{
			$outstr .= '
			<subject';
			if($subject['subject_scheme'])
			{
				$outstr .= ' subjectScheme="'.$subject['subject_scheme'].'"';
			}
			$outstr .='>'.$subject['subject'].'</subject>';					
		}
		$outstr .='
		</subjects>';
	}
	return $outstr;
}

function exportDoiContributors($doi_id)
{
	$contributors = getDoiContributors($doi_id);
	if($contributors)
	{
		$outstr = '<contributors>';			
		foreach($contributors as $contributor)
		{
			$outstr .= '
			<contributor';
			if($contributor['contributor_type'])
			{
				$outstr .= ' contributorType="'.$contributor['contributor_type'].'"';
			}
			$outstr .='><contributorName>'.$contributor['contributor_name'].'</contributorName>';
			if($contributor['name_identifier_scheme'])
			{
				$outstr .='
				<nameIdentifier nameIdentifierScheme="'.$contributor['name_identifier_scheme'].'">'.$contributor['name_identifier'].'</nameIdentifier>';
			}
			$outstr .= '</contributor>';
		}
		$outstr .='
		</contributors>';
	}	
	return $outstr;
}

function exportDoiDates($doi_id)
{
	$dates = getDoiDates($doi_id);
	if($dates)
	{
		$outstr = '<dates>';			
		foreach($dates as $date)
		{
			$outstr .= '
			<date';
			if($date['date_type'])
			{
				$outstr .= ' dateType="'.$date['date_type'].'"';
			}
			$outstr .='>'.$date['date'].'</date>';					
		}
		$outstr .='
		</dates>';
	}
	return $outstr;
}

function exportDoiLanguage($doi_id)
{
	$language = getDoiLanguage($doi_id);
	if($language)
	{
		$outstr = '<language>'.$language.'</language>';
	}
	return $outstr;
}
function exportDoiVersion($doi_id)
{
	$version = getDoiVersion($doi_id);
	if($version)
	{
		$outstr = '<version>'.$version.'</version>';
	}
	return $outstr;
}
function exportDoiRights($doi_id)
{
	$rights = getDoiRights($doi_id);
	if($rights)
	{
		$outstr = '<rights>'.$rights.'</rights>';
	}
	return $outstr;
}
function exportDoiResourceType($doi_id)
{
	ini_set('error_reporting', E_ALL);
	$resourceType = getDoiResourceType($doi_id);
	if($resourceType)
	{
		echo $resourceType;
		$outstr = '<resourceType';
			if($resourceType[0]['resource_type_general'])
			{
				$outstr .= ' resourceTypeGeneral="'.$resourceType[0]['resource_type_general'].'"';
			}
				
			if($resourceType[0]['resource_description'])
			{
				$outstr .= ' resourceDescription="'.$resourceType[0]['resource_description'].'"';
			}
			$outstr .='>'.$resourceType[0]['resource'].'</resourceType>';					

	}
	return $outstr;
}

function exportDoiAlternateIdentifiers($doi_id)
{
	$alternateIdentifiers = getDoiAlternateIdentifiers($doi_id);
	print_r($alternateIdentifiers);
	if($alternateIdentifiers)
	{
		$outstr = '<alternateIdentifiers>';			
		foreach($alternateIdentifiers as $alternateIdentifier)
		{
			$outstr .= '
			<alternateIdentifier';
			if($alternateIdentifier['alternate_identifier_type'])
			{
				$outstr .= ' alternateIdentifierType="'.$alternateIdentifier['alternate_identifier_type'].'"';
			}
			$outstr .='>'.$alternateIdentifier['alternate_identifier'].'</alternateIdentifier>';					
		}
		$outstr .='
		</alternateIdentifiers>';
	}
	return $outstr;
}
function exportDoiRelatedIdentifiers($doi_id)
{
	$relatedIdentifiers = getDoiRelatedIdentifiers($doi_id);
	if($relatedIdentifiers)
	{
		$outstr = '<relatedIdentifiers>';			
		foreach($relatedIdentifiers as $relatedIdentifier)
		{
			$outstr .= '
			<relatedIdentifier';
			if($relatedIdentifier['related_identifier_type'])
			{
				$outstr .= ' relatedIdentifierType="'.$relatedIdentifier['related_identifier_type'].'"';
			}
			if($relatedIdentifier['relation_type'])
			{
				$outstr .= ' relationType="'.$relatedIdentifier['relation_type'].'"';
			}			
			$outstr .='>'.$relatedIdentifier['related_identifier'].'</relatedIdentifier>';					
		}
		$outstr .='
		</relatedIdentifiers>';
	}
	return $outstr;
}

function exportDoiSizes($doi_id)
{
	$sizes = getDoiSizes($doi_id);
	if($sizes)
	{
		$outstr = '<sizes>';			
		foreach($sizes as $size)
		{
			$outstr .= '
			<size>'.$size['size'].'</size>';					
		}
		$outstr .='
		</sizes>';
	}
	return $outstr;
}

function exportDoiFormats($doi_id)
{
	$formats = getDoiFormats($doi_id);
	if($formats)
	{
		$outstr = '<formats>';			
		foreach($formats as $format)
		{
			$outstr .= '
			<format>'.$format['format'].'</format>';					
		}
		$outstr .='
		</formats>';
	}
	return $outstr;
}
function exportDoiDescriptions($doi_id)
{
	$descriptions = getDoiDescriptions($doi_id);
	if($descriptions)
	{
		$outstr = '<descriptions>';			
		foreach($descriptions as $description)
		{
			$outstr .= '
			<description';
			if($description['description_type'])
			{
				$outstr .= ' descriptionType="'.$description['description_type'].'"';
			}
			$outstr .='>'.$description['description'].'</description>';					
		}
		$outstr .='
		</descriptions>';
	}
	return $outstr;
}
?>