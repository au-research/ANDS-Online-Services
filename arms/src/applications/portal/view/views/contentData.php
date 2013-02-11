<?php
	$contentDiv = '';
	$subjectDiv = '';

	if (isset($contentData['contents']['contents']))
	{

		foreach($contentData['contents']['contents'] as $content=> $value)
		{



							$url = base_url() . "view/?id=an anchor" ;
					
							$contentDiv .= "<p><a href='".$url."'>".$content." " .$value."</a></p>";
				

		}
	}

	// Only display if there are actually some contemt to show...
	if ($contentDiv)
	{
		echo "<h3>Registry Contents</h3>";
		echo $contentDiv;
		echo "<p></p>";
	}

	if (isset($contentData['contents']['subjects']))
	{

		foreach($contentData['contents']['subjects'] as $subject=> $value)
		{



							$url = base_url() . "view/?id=an anchor" ;
					
							$subjectDiv .= "<p><a href='".$url."'>".$subject." " .$value."</a></p>";
				

		}
	}

	// Only display if there are actually some contemt to show...
	if ($subjectDiv)
	{
		echo "<h3>Subjects Covered</h3>";
		echo $subjectDiv;
		echo "<p></p>";
	}

	
?>
