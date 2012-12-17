<?php
	$connDiv = '';
	if (isset($connections_contents))
	{
		
		foreach($connections_contents as $data)
		{
			foreach($data as $entry)
			{
				if(isset($entry[0]['class']))
				{
					// Link connections to PUBLISHED objects to their SLUG for SEOness...
					if ($entry[0]['status'] == PUBLISHED)
					{
						$url = base_url() . $entry[0]['slug'];
					}
					else
					{
						$url = base_url() . "view/?id=" . $entry[0]['registry_object_id'];
					}

					$connDiv .= "<p class=".$entry[0]['class']."><a href='".$url."'>".$entry[0]['title']."</a></p>";
				}
			}
		}
	}

	echo $connDiv;
?>
