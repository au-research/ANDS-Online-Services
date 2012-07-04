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
	$subjects = $content->{'facet_counts'}->{'facet_fields'}->{'subject_value_resolved'};



		$output = count($subjects);

if($output>0)
{
	echo '<div class="facet-content"><ul class="moreSubjects2">';
	for($i=0;$i<$output;$i=$i+2)
	{		
		if($subjects[$i+1]>0) echo '<li class="limit"><a href="'.base_url().'search#!/tab=All/group='.urlencode($group).'/subject='.$subjects[$i].'">'.$subjects[$i].' ('.number_format($subjects[$i+1]).')</a></li>';

	}

	echo "</ul></div>";
}
?>