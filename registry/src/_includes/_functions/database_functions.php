<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/
// Connection Globals
// -----------------------------------------------------------------------------
// Array to hold database connections. Populated on successful connection
// in function openDatabaseConnection.
$gDatabaseConnections = array();

// Database Functions
// -----------------------------------------------------------------------------
function openDatabaseConnection(&$connectionReference, $connectionString)
{
	global $gDatabaseConnections;
	
	$connectionReference = pg_connect($connectionString);
	if( !$connectionReference )
	{
		print "CONNECTION ERROR";
		exit;
	}
	else
	{
		// Register this connection.
		$gDatabaseConnections[$connectionString] = $connectionReference;
	}
}

function closeDatabaseConnection(&$connectionReference)
{
	if( $connectionReference )
	{
		pg_close($connectionReference);
	}
}

function closeDatabaseConnections()
{
	global $gDatabaseConnections;
	
	// Check registered connections and close if required.
	foreach( $gDatabaseConnections as $connectionString => $connectionReference )
	{
		if( $connectionReference )
		{
			closeDatabaseConnection($connectionReference);
		}
	}
}

function executeQuery($cnn, $strQuery, $params=null)
{
    $resultSet = false;
    if ( $params == null )
    {
       $result = pg_query($cnn, $strQuery);
    }
    else
    {
    	$result = pg_query_params($cnn, $strQuery, $params);
    }
    
    if( $result )
    {
        $resultSet = pg_fetch_all($result);
        pg_free_result($result);
    }
    return $resultSet;
}

function executeUpdateQuery($cnn, $strQuery, $params)
{
   	for($i=0;$i<count($params);$i++)
   	{
   		if($params[$i]!=null){
   			$params[$i]=trim($params[$i]); 
   		}	
    }	
	$result = pg_query_params($cnn, $strQuery, $params);

    return $result;
}

function pgsqlBool($pgsqlstringval)
{
	$bool = false;
	if( strtoupper($pgsqlstringval) == 'T' )
	{
		$bool = true;
	}
	if( strtoupper($pgsqlstringval) == 'TRUE' )
	{
		$bool = true;
	}
	if( strtoupper($pgsqlstringval) == 'Y' )
	{
		$bool = true;
	}
	if( strtoupper($pgsqlstringval) == 'YES' )
	{
		$bool = true;
	}
	if( $pgsqlstringval == '1' )
	{
		$bool = true;
	}
	return $bool;
}

function getParams($additional_array, $internal_array, $totalNumParams)
{
	/* 
	 * Note that if your form used checkboxes or mulitple select lists, then
	 * there will be sub-arrays in the post data, and these will be put into
	 * arrays within the params array. This will then break a straight call
	 * to executeQuery. So, extra work will need to be done to process these
	 * values (because they are effectively many values for the same key/field).
	 */
	$params = null;
	if( $additional_array || $internal_array )
	{
		$params = array();
		$i = 0;
	}
	if( $additional_array )
	{
		foreach($additional_array as $key => $value)
		{
			if( $i < $totalNumParams )
			{
				if( is_array($value) )
				{
					$subparams = array();
					$j = 0;
					foreach( $value as $subvalue )
					{
						$subparams[$j] = $subvalue;
						$j++;
					}
					$params[$i] = $subparams;
					$subparams = null;
				}
				else
				{
					$params[$i] = $value;
				}
				$i++;
			}
		}
	}
		
	if( $internal_array )
	{
		foreach($internal_array as $key => $value)
		{
			if( $i < $totalNumParams )
			{
				if( is_array($value) )
				{
					$subparams = array();
					$j = 0;
					foreach( $value as $subvalue )
					{
						$subparams[$j] = $subvalue;
						$j++;
					}
					$params[$i] = $subparams;
					$subparams = null;
				}
				else
				{
					$params[$i] = $value;
				}
				$i++;				
			}
		}
	}
	
	return $params;
}
?>