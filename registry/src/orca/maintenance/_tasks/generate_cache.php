<?php
echo "<h1>Regenerating registryObjects cache</h1>";

if (isset($_GET['hard']))
{
	exec("rm -rf ".eCACHE_DIRECTORY."/*");
	echo "Using <b>--hard</b> reset: Deleting all items in" . eCACHE_DIRECTORY . "<br/>";
}



$req_datasource=getQueryValue('data_source');


$ds = array();

if ($req_datasource) 
{
	$ds[] = array("data_source_key"=>$req_datasource);
}
elseif (isset($_GET['cache_all'])) 
{
	$ds = getDataSources(null, null); //add publish my data
}


echo "<h3>Caching registryObjects for " . count($ds) . " datasource(s)</h3><br/><br/>";
flush();ob_flush();

foreach($ds AS $datasource)
{	

	bench(1);
	$ro = getRegistryObjectKeysForDataSource($datasource['data_source_key']);
	echo "Getting all RegistryObject keys: ". bench(1) . "<br/><hr/><br/>";

	if (!$ro) continue;
	echo "Caching of " . count($ro) . " records started for " . $datasource['data_source_key'] . ": ";	
	flush();ob_flush();
	
	bench();
	$count = 0;
	$mult = 1;
	foreach ($ro AS $registry_object)
	{
		$count++;

		bench(1);
		$extendedRIFCS = generateExtendedRIFCS($registry_object['registry_object_key']);
		echo "<br/>Getting RIFCS for " . $registry_object['registry_object_key'] . ": " . bench(1) . "s<br/>";
		bench(1);
		writeCache($datasource['data_source_key'], $registry_object['registry_object_key'], $extendedRIFCS);
		echo "Writing cache for " . $registry_object['registry_object_key'] . ": " . bench(1) . "s<br/><br/>";

		if (ceil(count($ro)*$mult/10) == $count) { echo "."; flush();ob_flush(); $mult++; }
	}
		
	echo " complete! [ ".bench()."s ]<br/>";
	flush();ob_flush();
}
