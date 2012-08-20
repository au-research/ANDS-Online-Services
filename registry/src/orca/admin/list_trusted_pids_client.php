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
		
// Fetch list of clients from PIDS resource URL (e.g. http://devl.ands.org.au/pids/listClients
$requestURI = $ePIDS_RESOURCE_URI . "listClients";
//$response = file_get_contents($requestURI);



// create curl resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, $requestURI);

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//VERY IMPORTANT, skip SSL

// $output contains the output string
$response = curl_exec($ch);
//var_dump($output);

// close curl resource to free up system resources
curl_close($ch);

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

			$displayResults = array();
			
			if (isset($_GET['appId']) && strlen($_GET['appId']) == 40) {
				$xPath = new DOMXPath($responseDOMDoc);
				$nodeList = $xPath->query("//client[@appId='".$_GET['appId']."']");
			} else {
				$xPath = new DOMXPath($responseDOMDoc);
				$nodeList = $xPath->query("//client");				
			}
			
			$displayResults = array();	
						
			foreach ($nodeList AS $node) {
				$displayResults[] = array(	"appId" => $node->getAttribute("appId"),
											"desc" => $node->getAttribute("desc"),
											"ip" => $node->getAttribute("ip")
										 );
			}
			
			if (sizeof($displayResults) === 0) 
			{
				$errorMessages .= "Zero results returned from PIDS Server.<br/>";
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
	


// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;"> 
	Listing all currently authorised IP addresses. Click on the appId to authorise an additional IP Address.
</p>
<table summary="List Trusted PIDS Clients">
	<thead>
		<tr>
			<td colspan="3">List Trusted IP Sources</td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText"><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } else { ?>
	<tbody>
		<tr>
			<th>appId</th>
			<th>IP Address</th>
			<th width="250px">Description</th>
		</tr>

		<?php foreach ($displayResults AS $result) { ?>
			<tr>
			
				<td><a href="add_trusted_pids_client.php?appId=<?php echo $result['appId']; ?>">
					<?php echo $result['appId']; ?>
				</a></td>
				<td><?php echo $result['ip']; ?></td>
				<td><?php echo $result['desc']; ?></td>
			</tr>	
		<?php } ?>
		
	</tbody>
	<?php } ?>
</table>

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

?>
