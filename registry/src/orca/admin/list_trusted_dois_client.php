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
require '../dois_orcainit.php';
// Page processing
// -----------------------------------------------------------------------------

$errorMessages = '';
		
// Fetch list of clients from DOIS database
$clients = listDoisClients();

if (!$clients) {
	$errorMessages = "Error whilst attempting to fetch doi trusted clients list.<br/>";
} else {
	$clientNum = count($clients);
}		

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<p class="resultListHeader" style="color: #666666; text-align: left; border: 1px solid #888888; font-style: normal;"> 
	Listing all currently authorised IP addresses to access the DOI service. Click on the appId to authorise an additional IP Address.
</p>
<table summary="List Trusted DOIS Clients">
	<thead>
		<tr>
			<td colspan="6">List Trusted DOI Sources</td>
		</tr>
	</thead>	
	<?php if( $errorMessages ) { ?>
	<tbody>
		<tr>
			<td></td>
			<td class="errorText" colspan="5" ><?php print($errorMessages); ?></td>
		</tr>
	</tbody>
	<?php } else { ?>
	<tbody>
		<tr>
			<th>Client Id</th>		
			<th>appId</th>
			<th>IP Address</th>
			<th>Name</th>			
			<th >Contact Name</th>
			<th >Contact Email</th>			
		</tr>

		<?php foreach ($clients AS $client) { ?>
			<tr>
				<td><?php echo $client['client_id']; ?></td>				
				<td>
					<a href="add_trusted_dois_client.php?app_id=<?php echo $client['app_id']; ?>"><?php echo $client['app_id']; ?></a>
				</a></td>
				<td><?php echo $client['ip_address']; ?></td>
				<td><?php echo $client['client_name']; ?></td>				
				<td><?php echo $client['client_contact_name']; ?></td>
				<td><?php echo $client['client_contact_email']; ?></td>				
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
