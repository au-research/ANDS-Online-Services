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
					$connDiv .= "<p class=".$entry[0]['class']."><a href='?id=".$entry[0]['registry_object_id']."'>".$entry[0]['title']."</a></p>";
				}
			}
		}
	}

	echo $connDiv;
?>
