<?php

	$connDiv = '';
	$conn = array();

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


						if(!isset($conn[$entry['class']]))
						{
							$conn[$entry['class']] = "<p class=".$entry['class']."><a href='".$url."'>".$entry['title']."</a></p>";
						}else{
							$conn[$entry['class']] .= "<p class=".$entry['class']."><a href='".$url."'>".$entry['title']."</a></p>";
						}
					}
				}
			}
		}

		foreach($conn as $connections => $value)
		{
			switch($connections){
				case "contributor":
					$heading = "<h3>Contributed by</h3>";
					break;
				case "party_one":
					$heading = "<h3>Researchers</h3>";
					break;	
				case "party_multi":
					$heading = "<h3>Research Groups</h3>";
					break;	
				case "activity":
					$heading = "<h3>Activities</h3>";
					break;	
				case "service":
					$heading = "<h3>Services</h3>";
					break;
				case "collection":
					$heading = "<h3>Collections</h3>";
					break;																						
			}
			$connDiv .= $heading;
			$connDiv .= $value;	
		}
	}

	// Only display if there are actually some connections to show...
	if ($connDiv)
	{
		echo "<h2>Connections</h2>";
		echo $connDiv;
		echo "<p></p>";
	}

?>
