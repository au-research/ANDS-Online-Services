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
// Include required files and initialisation.
require '../../_includes/init.php';
require '../orca_init.php';
// Page processing
// -----------------------------------------------------------------------------

$errorMessages = '';

if ( strtoupper(getPostedValue('verb')) == "ADD" )
{

	if( getPostedValue('trusted_ip_address') == '' )
	{ 
		$errorMessages .= "Trusted IP Address is a mandatory field.<br />";
		
	} 
	elseif (!valid_ip(getPostedValue('trusted_ip_address'))) 
	{
		$errorMessages .= "Invalid format IP address. Example: 127.0.0.1<br />";	
	}
	
	if( trim(getPostedValue('trusted_ip_description')) == '' )
	{ 
		$errorMessages .= "Description is a mandatory field.<br />";
	}
	
	if( strlen(getPostedValue('existing_app_id')) != 0 && 
	    strlen(getPostedValue('existing_app_id')) != 40 )
	{ 
		$errorMessages .= "Existing appId should either be empty or a 40-character string.<br />";
	}

	if( !$errorMessages )
	{

		
		// GET PID APPID from PIDS resource URL (e.g. http://devl.ands.org.au/pids/addClient?ip=127.0.0.1&desc=Test%20User
		$requestURI = $ePIDS_RESOURCE_URI . "addClient?ip=".urlencode(getPostedValue('trusted_ip_address'))."&desc=".urlencode(trim(getPostedValue('trusted_ip_description')));
		$requestURI .= (strlen(trim(getPostedValue('existing_app_id')))==40 
							? 
							"&appId=" . trim(getPostedValue('existing_app_id')) 
							: 
							''
						);
		
		$response = file_get_contents($requestURI);

		if (!$response) {
			$errorMessages = "Error whilst attempting to fetch from URI: " . $ePIDS_RESOURCE_URI . "<br/>";
		} else {
		
			$responseDOMDoc = new DOMDocument();
			$result = $responseDOMDoc->loadXML($response);
			if( $result )
			{
				$messageType = strtoupper($responseDOMDoc->getElementsByTagName("response")->item(0)->getAttribute("type"));
				if( $messageType == gPIDS_RESPONSE_SUCCESS )
				{
					
					$xPath = new DOMXPath($responseDOMDoc);
					$nodeList = $xPath->query("//property[@name='appId']");
					$appId = $nodeList->item(0)->getAttribute("value");
					
					if( strlen($appId) == 40 )
					{
						responseRedirect('list_trusted_pids_client.php?newAppId='.$appId);
					} 
					else 
					{
						$errorMessages .= "Could not extract appId. Status of request unknown.<br/>";
					}
					
				} elseif ( $messageType == gPIDS_RESPONSE_FAILURE ) {
					
					foreach ($responseDOMDoc->getElementsByTagName("response")->item(0)->getElementsByTagName("message") AS $message) {
					    $errorMessages .= esc($message->nodeValue) . "<br/>";
					}
				}
				
			} else {
				
				$errorMessages .= "Error whilst attempting to load XML response. Response could not be parsed.";
			}
		}		
	}
}


// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<form id="add_trusted_pids_client" action="add_trusted_pids_client.php" method="post">
<table class="formTable" summary="List Trusted PIDS Clients">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Add Trusted IP Source</td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText"><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } ?>
	<tbody class="formFields">
		<tr>
			<td>* IP Address:</td>
			<td><input type="text" name="trusted_ip_address" id="trusted_ip_address" size="15" maxlength="64" value="<?php printSafe(getPostedValue('trusted_ip_address')) ?>" /> 
			    <span class="formNotes">(Single address - no ranges)</span>
			</td>
		</tr>
		<tr>
			<td>* Description:</td>
			<td><input type="text" name="trusted_ip_description" id="trusted_ip_description" size="40" maxlength="255" value="<?php printSafe(getPostedValue('trusted_ip_description')) ?>" /></td>
		</tr>
		
		<tr>
			<td>Existing App ID:</td>
			<td><input type="text" name="existing_app_id" id="existing_app_id" size="40" maxlength="40" value="<?php printSafe((isset($_GET['appId']) ? $_GET['appId'] : getPostedValue('existing_app_id'))) ?>" /></td>
		</tr>

	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td><input type="submit" name="verb" value="Add" onclick="wcPleaseWait(true, 'Processing...')" />&nbsp;&nbsp;</td>
		</tr>
		<tr>
			<td></td>
			<td class="formNotes">
				Fields marked * are mandatory.<br />
			</td>
		</tr>
	</tbody>
</table>
</form>
<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '../../_includes/footer.php';
require '../../_includes/finish.php';


function valid_ip($ip) { 
    return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" . 
            "(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip); 
} 

function curl_file_get_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
    }

?>
