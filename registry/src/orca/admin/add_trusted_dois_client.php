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
require '../../dois/dois_orcainit.php';

// Page processing
// -----------------------------------------------------------------------------

$errorMessages = '';

if ( strtoupper(getPostedValue('verb')) == "ADD" )
{
	if( getPostedValue('client_name') == '' )
	{ 
		$errorMessages .= "Client name is a mandatory field.<br />";
		
	} 

	if( getPostedValue('ip_address') == '' )
	{ 
		$errorMessages .= "Client Ip is a mandatory field.<br />";
		
	}else{
		
		$iprange = explode(",",getPostedValue('ip_address'));

		if($iprange[1])
		{
			if(!doisValidIp($iprange[0]))
			{
				$errorMessages .= "Client Ip ".$iprange[0]."is a not a valid ip address.<br />";
			}	
			if(!doisValidIp($iprange[1]))
			{
				$errorMessages .= "Client Ip ".$iprange[1]."is a not a valid ip address.<br />";			
			}		
		}
		else{
			if(!doisValidIp(getPostedValue('ip_address')))
			{
				$errorMessages .= "Client Ip is a not a valid ip address.<br />";
			}
		}
	} 

	
	if( trim(getPostedValue('client_contact_name')) == '' )
	{ 
		$errorMessages .= "Contact name is a mandatory field.<br />";
	}

	if( trim(getPostedValue('client_contact_email')) == '' )
	{ 
		$errorMessages .= "Contact email is a mandatory field.<br />";
	}else {
		if(!doisValidEmail(getPostedValue('client_contact_email')))
		{
			$errorMessages .= "Client email is a not a valid email address.<br />";
		}
		
	}
	
	
	if( trim(getPostedValue('client_domain_list')) == '' )
	{ 
		$errorMessages .= "Domain list is a mandatory field.<br />";
	}else{		
		$domains = explode(",",trim(getPostedValue('client_domain_list'),","));
		foreach($domains as $client_domain){
			if(trim($client_domain)){
				if(!doisValidDomain(trim($client_domain)))
				{
					$errorMessages .= "<em>".$client_domain."</em> in domain list is not a valid domain.<br />";	
				}
			}
		}

	}
	
	if( trim(getPostedValue('datacite_prefix')) == '' )
	{ 
		$errorMessages .= "You must select a DOI prefix.<br />";
	}
	
	if( trim(getPostedValue('app_id')) == '' )
	{ 
		$app_id = sha1(getPostedValue('ip_address').getPostedValue('client_name'));
	}
	elseif( strlen(getPostedValue('app_id')) != 0 && 
	    strlen(getPostedValue('app_id')) != 40 )
	{ 
		$errorMessages .= "Application Id should either be empty or a 40-character string.<br />";
	}else{
		$app_id = getPostedValue('app_id');
	}	
	
	if( !$errorMessages )
	{
		//if no error message we want to add this client as a trusted client to mint dois
		//we need to insert them into the database
		$client = addDoisClient(getPostedValue('client_name'),getPostedValue('client_contact_name'),getPostedValue('client_contact_email'),getPostedValue('ip_address'),getPostedValue('datacite_prefix'),$app_id);	
	
		$client_id = getDoisClient(getPostedValue('ip_address'));

		//we now need to create this datacentre in datacite		
		//$addToDataCite = doisAddDatacentre("ANDS.CENTRE-".$client_id,getPostedValue('client_name'),getPostedValue('client_contact_name'),getPostedValue('client_contact_email'),getPostedValue('datacite_prefix'),$domains);
		
		//We now need to insert the domains to be registered with this client into the db	
		foreach($domains as $client_domain){
		
			if(trim($client_domain)){
				addDoisClientDomain($client_id,trim($client_domain));
			}			
			
		}	
		responseRedirect('list_trusted_dois_client.php?newAppId='.$app_id);
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';
// BEGIN: Page Content
// =============================================================================

?>
<script type="text/javascript" src="<?php print eAPP_ROOT ?>orca/_javascript/orca_dhtml.js"></script>
<form id="add_trusted_dois_client" action="add_trusted_dois_client.php" method="post">
<table class="formTable" summary="List Trusted PIDS Clients">
	<thead>
		<tr>
			<td>&nbsp;</td>
			<td>Add Trusted DOI Client</td>
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
			<td>* Name:</td>
			<td><input type="text" name="client_name" id="client_name" size="40" maxlength="64" value="<?php printSafe(getPostedValue('client_name')) ?>" /> 
			    
			</td>
		</tr>
		<tr>
			<td>* IP Address:</td>
			<td><input type="text" name="ip_address" id="ip_address" size="40" maxlength="64" value="<?php printSafe(getPostedValue('ip_address')) ?>" /> 
			    <span class="formNotes">(To supply a range of IP addesses, separate the 2 addresses with a comma)</span>
			</td>
		</tr>
		<tr>
			<td>* Contact Name:</td>
			<td><input type="text" name="client_contact_name" id="client_contact_name" size="40" maxlength="255" value="<?php printSafe(getPostedValue('client_contact_name')) ?>" /></td>
		</tr>
		<tr>
			<td>* Contact Email:</td>
			<td><input type="text" name="client_contact_email" id="client_contact_email" size="40" maxlength="255" value="<?php printSafe(getPostedValue('client_contact_email')) ?>" /></td>
		</tr>		
		<tr>
			<td>* Domain List :</td>
			<td><input type="text" name="client_domain_list" id="client_domain_list" size="40" maxlength="255" value="<?php printSafe(getPostedValue('client_domain_list')) ?>" />
			<span class="formNotes">(Comma delimited list)</span></td>
		</tr>
		<tr>
			<td>* DOI Prefix :</td>
			<td><select name="datacite_prefix" id="datacite_prefix" >
			<option value="">Select</option>
		<?php 
		foreach($gDOIS_PREFIX_TYPES as $prefix)
		{
			echo "<option value=".$prefix.">".$prefix."</option>";		
		}	
		?>		
			</select>
			</td>
		</tr>		
		 <?php 
		$gDOIS_PREFIX_TYPES
		
		?>
	<!--	<tr>
			<td>Application ID:</td>
			<td><input type="text" name="app_id" id="existing_app_id" size="40" maxlength="40" value="<?php printSafe((isset($_GET['appId']) ? $_GET['appId'] : getPostedValue('app_id'))) ?>" /></td>
		</tr>  -->
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
?>
