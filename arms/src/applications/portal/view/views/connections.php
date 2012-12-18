<?php
	$connDiv = '';
	if (isset($connections_contents))
	{
		
		foreach($connections_contents as $classes)
		{
			foreach($classes as $classname => $class)
			{
				// XXX: handle count greater than X
				if (strpos($classname, "_count")) continue;

				foreach ($class AS $entry)
				{
					if(isset($entry['class']))
					{
						// Link connections to PUBLISHED objects to their SLUG for SEOness...
						if ($entry['status'] == PUBLISHED)
						{
							$url = base_url() . $entry['slug'];
						}
						else
						{
							$url = base_url() . "view/?id=" . $entry['registry_object_id'];
						}

						$connDiv .= "<p class=".$entry['class']."><a href='".$url."'>".$entry['title']."</a></p>";
					}
				}
			}
		}
	}

	echo $connDiv;
?>
