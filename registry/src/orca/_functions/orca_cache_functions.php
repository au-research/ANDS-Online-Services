<?php


error_reporting(E_ALL);

function generateExtendedRIFCS($registry_object_key)
{
	// "Extended RIFCS" contains values such as number of related links, display title, etc. 
	return getRegistryObjectXMLFromDB($registry_object_key, true, true);
}

function generateUniqueKeyHash($key, $is_datasource=false)
{
	// Generate an SHA1 hash of the registry object key
	$hash = sha1($key);
	// Regenerate a key if there is a collision with a key that isn't ours (unlikely)
	if ($is_datasource && $d = getDataSourceByHash($hash) && isset($d[0]) && $d[0]['data_source_key'] != $key)
	{
		// append today's time for more uniqueness
		$hash = sha1($key . time());
	} 
	else if (!$is_datasource && $r = getRegistryObjectByHash($hash) && isset($r[0]) && $r[0]['registry_object_key'] != $key)
	{
		// append today's time for more uniqueness
		$hash = sha1($key . time());
	}

	return $hash;
}

function generateRegistryObjectHashForKey($registry_object_key)
{
	return generateUniqueKeyHash($registry_object_key);	
}

function generateDataSourceHashForKey($data_source_key)
{
	return generateUniqueKeyHash($data_source_key, true);	
}

function checkCacheStructure($data_source_key, $registry_object_key='')
{
	//////////////////////////////////
	// Check cache directory and permissions
	//////////////////////////////////
	if (!is_dir(eCACHE_DIRECTORY))
	{
		echo "Cache directory (" . eCACHE_DIRECTORY . ") does not exist";
		return FALSE;
	}
	
	if (!is_writeable(eCACHE_DIRECTORY))
	{
		echo "Cache directory (" . eCACHE_DIRECTORY . ") is not writeable";
		return FALSE;
	}
	
	/////////////////////////////////
	// Check datasource directory and permissions
	/////////////////////////////////
	$data_source_hash = getDataSourceHashForKey($data_source_key);
	if (!$data_source_hash)
	{
		echo "Could not generate hash for $data_source_key";
		return FALSE;
	}
	
	if (!is_dir(eCACHE_DIRECTORY . "/" . $data_source_hash))
	{
		// Cache directory for datasource does not exist, try to create it
		if (!mkdir(eCACHE_DIRECTORY . "/" . $data_source_hash, eCACHE_PERMISSION))
		{
			echo "Could not create cache directory for $data_source_key";
			return FALSE;
		}
	}
	
	// Check permissions here too!
	if (!is_writeable(eCACHE_DIRECTORY . "/" . $data_source_hash))
	{
		echo "Cache directory for $data_source_key is not writeable";
		return FALSE;
	}	
	
	/////////////////////////////////////
	// If registry_obect_key specified
	/////////////////////////////////////
	if ($registry_object_key != '') 
	{
		// Check registry object directory and permissions
		$registry_object_hash = getRegistryObjectHashForKey($registry_object_key);
		if (!$registry_object_hash)
		{
			echo "Could not generate hash for $registry_object_key";
			return FALSE;
		}
		
		if (!is_dir(eCACHE_DIRECTORY . "/" . $data_source_hash . "/" . $registry_object_hash))
		{
			// Cache directory for registry object does not exist, try to create it
			if (!mkdir(eCACHE_DIRECTORY . "/" . $data_source_hash . "/" . $registry_object_hash, eCACHE_PERMISSION))
			{
				echo "Could not create cache directory for $registry_object_key";
				return FALSE;
			}
		}
		
		// Check permissions here too!
		if (!is_writeable(eCACHE_DIRECTORY . "/" . $data_source_hash . "/" . $registry_object_hash))
		{
			echo "Cache directory for $registry_object_key is not writeable";
			return FALSE;
		}	
		
		// Check the symlinks are valid (this might fail if there are no versions in the datasource)
		/*if (!updateSymLinktoLatest(eCACHE_DIRECTORY . "/" . $data_source_hash . "/" . $registry_object_hash))
		{
			// oh well... no error at this stage
		}*/
		
	}
	
	// Got this far? all must be good! return the directory path for this key
	return eCACHE_DIRECTORY . "/" . $data_source_hash . ($registry_object_key!='' ? "/" . $registry_object_hash . "/" : "");
}

// Get or create&update a hash for a registry object
function getRegistryObjectHashForKey($registry_object_key)
{
	// Get current hash from database 
	$hash = getRegistryObjectHash($registry_object_key);
	if ($hash !== FALSE)
	{
		if ($hash == '')
		{
			// If hash is empty, generate one and update the database entry
			$hash = generateRegistryObjectHashForKey($registry_object_key);
			if (!$hash)
			{
				// Something went wrong generating the hash
				echo "Something went wrong generating the Registry Object hash";
				return false;
			}
			else
			{
				// Update the database entry with this hash!
				updateRegistryObjectHash($registry_object_key, $hash);
				return $hash;
			}
		}
		else
		{
			// non-empty hash, this must be it!
			return $hash;
		}
	}
	else
	{
		// registry object could not be found
		echo "Registry Object could not be found";
		return FALSE;
	}
}

// Get or create&update a hash for a data source
function getDataSourceHashForKey($data_source_key)
{
	// Get current hash from database 
	$hash = getDataSourceHash($data_source_key);
	if ($hash !== FALSE)
	{
		if ($hash == '')
		{
			// If hash is empty, generate one and update the database entry
			$hash = generateDataSourceHashForKey($data_source_key);
			if (!$hash)
			{
				// Something went wrong generating the hash
				echo "Something went wrong generating the Data Source hash";
				return false;
			}
			else
			{
				// Update the database entry with this hash!
				updateDataSourceHash($data_source_key, $hash);
				return $hash;
			}
		}
		else
		{
			// non-empty hash, this must be it!
			return $hash;
		}
	}
	else
	{
		// registry object could not be found
		echo "Data Source could not be found";
		return FALSE;
	}
}



function updateSymLinktoLatest($path_relative_to_cache)
{
	// Check cache path has trailing slash
	if (substr($path_relative_to_cache, -1) != "/")
	{
		$path_relative_to_cache = $path_relative_to_cache . "/"; 
	}	

	// Check directory exists
	if (!is_dir($path_relative_to_cache) || !is_writeable($path_relative_to_cache))
	{
		echo "Error updating symlink: directory doesn't exist or permissions wrong";
		return FALSE;
	}
	
	// Get filename of latest file
	$file_array = scandir( $path_relative_to_cache );
	
	$most_recently_modified = "";
	$most_recently_modified_time = 0;
	foreach ($file_array AS $filename)
	{

		if ($filename != eCACHE_CURRENT_NAME && file_exists($path_relative_to_cache . $filename)) {
			$file_modified = filemtime($path_relative_to_cache . $filename);
			if ($file_modified >= $most_recently_modified_time)
			{
				$most_recently_modified = $filename;
				$most_recently_modified_time = $file_modified;
			}
		}
	}

	if ($most_recently_modified == "")
	{
		echo "No 'most recent' files could be found for $path_relative_to_cache";
		return FALSE;	
	}

	// Delete existing symlink & update to latest
	exec("cd ".$path_relative_to_cache."; rm -rf ".eCACHE_CURRENT_NAME."; ln -s ".$most_recently_modified." ".eCACHE_CURRENT_NAME); 
	// Update to latest
	
	if (!file_exists($path_relative_to_cache . eCACHE_CURRENT_NAME))
	{
		echo "Following Symlink failed...what went wrong????";
		return FALSE;
	}

	// Assume everything worked! yay :D
	return TRUE;
	
}

function writeCache($data_source_key, $registry_object_key, $payload)
{
	// Check cache structure
	$directory_path = checkCacheStructure($data_source_key, $registry_object_key);
	if (!$directory_path)
	{
		echo "Cache structure could not be built for cache writing";
		return FALSE;
	}
	
	// Append trailing slash if appropriate
	if (substr($directory_path,-1) != '/')
	{
		$directory_path .= "/";
	}
	
	// Check payload
	if (strlen($payload) > 0)
	{
		// Write file data
		$current_time = time();
		$handle = @fopen($directory_path . $current_time, "wb");
		if (!$handle)
		{
			echo "Could not open cache file in writable mode: " . $directory_path . $current_time;
			return FALSE;
		}
		else
		{
			// write the payload to the cache file
			if (fwrite($handle, $payload) == 0)
			{
				echo "Error: no bytes were written to cache file!";
				return FALSE;	
			}
			
			fclose($handle);
		}		
		
		// Update symlink
		if (!updateSymLinktoLatest($directory_path))
		{
			echo "Error: Symlink could not be updated?";
			return FALSE;
		}
		

		/*

		$ds_hash = getDataSourceHash($data_source_key);
		if (!$ds_hash)
		{
			echo "Could not get data source hash from database - this data source isn't cached yet?? Error!";
			return FALSE;
		}
		*/

		// Recompute the data_source cache file
		//exec("cd " . eCACHE_DIRECTORY . "; cat " . $ds_hash . "/*/" . eCACHE_CURRENT_NAME . " > " . $ds_hash . ".cache");
		// this causes issues with while loops through records!!! rather do on first load (this is quick!)
	}
	
	// if we got here, everything has probably worked!
	return TRUE;
	
}

function getCacheItems($data_source_key, $registry_object_key='', $version=eCACHE_CURRENT_NAME, $as_extended=false)
{
	global $gORCA_REGISTRY_OBJECT_WRAPPER, $gORCA_REGISTRY_OBJECT_WRAPPER_END;
	
	$payload = '';
	

	// Check cache structure
	$directory_path = checkCacheStructure($data_source_key, $registry_object_key);

	$ds_hash = getDataSourceHash($data_source_key);
	if (!$ds_hash)
	{
		//echo "Could not get data source hash from database - this data source isn't cached yet.";
		return FALSE;
	}
	
	// If no registry_object_key, then we want all from that data_source
	// Get a concatenated output of all payloads matching the version specified
	if ($registry_object_key == '')
	{
		
		$output = array();
		$payload = @file_get_contents(eCACHE_DIRECTORY . "/" . $ds_hash . ".cache");
		
		if ($payload === FALSE)
		{
			// Recompute the data_source cache file
			regenerateDataSourceCache($data_source_key);
			$payload = @file_get_contents(eCACHE_DIRECTORY . "/" . $ds_hash . ".cache");
		}
		
		// Some sanity checks on the payload
		if (strlen($payload) > 0 && substr_count($payload, "<registryObject") == 0)
		{
			// hmmm, something is there, but its not registry objects -- must be a bash error??
			//echo "Payload did not pass through sanity check!!";
			return FALSE;
		}
		
		// wrap payload
		//$payload = $gORCA_REGISTRY_OBJECT_WRAPPER . $payload . "\n" . $gORCA_REGISTRY_OBJECT_WRAPPER_END;
		
		// transform (if applicable - by default, records are stored in the "richer" extended format)
		if (!$as_extended)
		{
			$payload = stripExtendedRIFCS($payload);
		}
			
		// and return
		return 	$payload;
	}
	else
	{	 	
		// If registry_object_key
		$ro_hash = getRegistryObjectHash($registry_object_key);
		if (!$ro_hash)
		{
			// this registry object isn't cached yet?;
			return FALSE;
		}
	
		
		//$output = array();
		//exec("cd " . eCACHE_DIRECTORY . "; cat " . $ds_hash . "/" . $ro_hash . "/" . $version, $output);


		$payload = @file_get_contents(eCACHE_DIRECTORY . "/" . $ds_hash . "/" . $ro_hash . "/" . $version);


		// Some sanity checks on the payload
		if (strlen($payload) > 0 && substr_count($payload, "<registryObject") == 0)
		{
			// hmmm, something is there, but its not registry objects -- must be a bash error??
			echo "Payload did not pass through sanity check!!";
			return FALSE;
		}		

		// wrap payload
		//$payload = $gORCA_REGISTRY_OBJECT_WRAPPER . $payload . "\n" . $gORCA_REGISTRY_OBJECT_WRAPPER_END;
		
		// transform (if applicable - by default, records are stored in the "richer" extended format)
		if (!$as_extended)
		{
			$payload = stripExtendedRIFCS($payload);
		}
			
		
		// and return
		return 	$payload;
	
	}
	
	// Something must have gone awry?
	return FALSE;
}


function deleteCacheItem($data_source_key, $registry_object_key)
{
	// delete the symlink to "current" (analogous to deleting the registryObject
	$directory_path = checkCacheStructure($data_source_key, $registry_object_key);
	if ($directory_path)
	{
		exec("cd ".$directory_path."; rm -rf ".eCACHE_CURRENT_NAME.";");
	}

	regenerateDataSourceCache($data_source_key);
}


function regenerateDataSourceCache($data_source_key)
{
	if ($data_source_hash = getDataSourceHash($data_source_key))
	{
		// Recompute the data_source cache file
		exec("cd " . eCACHE_DIRECTORY . "; cat " . $data_source_hash . "/*/" . eCACHE_CURRENT_NAME . " > " . $data_source_hash . ".cache");
		return;
	}
	else
	{
		// something went wrong!!
		return FALSE;
	}
}

function wrapRegistryObjects($payload, $include_extRif = true)
{
	global $gORCA_REGISTRY_OBJECT_WRAPPER, $gORCA_REGISTRY_OBJECT_WRAPPER_END;
	// strip the wrapper elements of a cached registry object
	if (!$include_extRif)
	{
		return 	stripExtendedRIFCS($gORCA_REGISTRY_OBJECT_WRAPPER) . 
					$payload . "\n" . 
				stripExtendedRIFCS($gORCA_REGISTRY_OBJECT_WRAPPER_END);
	}
	else
	{
		return 	$gORCA_REGISTRY_OBJECT_WRAPPER . 
					$payload . "\n" . 
				$gORCA_REGISTRY_OBJECT_WRAPPER_END;
	}		
}

