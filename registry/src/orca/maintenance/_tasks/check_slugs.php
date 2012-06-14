<?php
echo "<h1>Checking SLUGs</h1>";

$ds = getDataSources(null, null); //add publish my data
echo "<h3>Checking registryObjects SLUGs for " . count($ds) . " datasource(s)</h3><br/><br/>";
flush();ob_flush();

$slugs_updated = 0;
foreach($ds AS $datasource)
{
	$ro = getRegistryObjectKeysForDataSource($datasource['data_source_key']);

	if (!$ro) continue;
	echo "Checking SLUGs on " . count($ro) . " records started for " . $datasource['data_source_key'] . ": ";
	flush();ob_flush();

	bench();
	$count = 0;
	$mult = 1;
	foreach ($ro AS $registry_object)
	{
		$count++;

		if ($registry_object['url_slug'] == '')
		{
			updateRegistryObjectSLUG($registry_object['registry_object_key'], $registry_object['display_title']);
			$slugs_updated++;
			echo "!";
		}


		if (ceil(count($ro)*$mult/10) == $count) {
			echo "."; flush();ob_flush(); $mult++;
		}
	}

	echo " complete! [ ".bench()."s ]<br/>";
	flush();ob_flush();
}

echo "<h2>Updated " . $slugs_updated . " SLUG(s)</h2>";