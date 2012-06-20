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
echo "<h3> <img src='".base_url()."img/icon/link_64.jpg' style='width:16px;'> Internal Records:</h3>";
?>
<p>
<?php
switch($seeAlsoType){
	case "subjects":
		if ($numfound==1){
			$word = 'Collection';
		}else $word = 'Collections';
		echo '<a href="javascript:void(0);" id="seeAlso_subjectNumFound"><span id="seealso-realnumfound">'.$numfound.'</span> '.$word.'</a> with matching subjects';
		//echo '<a href="javascript:void(0);" id="sa-subject">Click Here</a>';

		//echo 'Also related by subjects: <a href="javascript:void(0);" id="seeAlso_subjectNumFound"><span id="seealso-realnumfound">'. $numfound.'</span> '.$word.'</a>';
		break;
	case "identifiersParty":
		if ($numfound==1){
			$word = 'Party';
		}else $word = 'Parties';
		echo '<a href="javascript:void(0);" id="seeAlso_identifierNumFound"><span id="seealso-realnumfound">'.$numfound.'</span> '.$word.'</a> with matching identifiers';
		//echo 'Also related by identifiers: <a href="javascript:void(0);" id="seeAlso_identifierNumFound"><span id="seealso-realnumfound">'. $numfound.'</span> '.$word.'</a>';
		break;
	case "identifiersActivity":
		if ($numfound==1){
			$word = 'Activity';
		}else $word = 'Activities';
		echo '<a href="javascript:void(0);" id="seeAlso_identifierNumFound"><span id="seealso-realnumfound">'.$numfound.'</span> '.$word.'</a> with matching identifiers';
		//echo 'Also related by identifiers: <a href="javascript:void(0);" id="seeAlso_identifierNumFound"><span id="seealso-realnumfound">'. $numfound.'</span> '.$word.'</a>';
		break;
}


//echo 'Also related from Subjects: <a href="javascript:void(0);" id="seeAlso_subjectNumFound">'. $activityNumFound.' Collections</a>';
?>
</p>