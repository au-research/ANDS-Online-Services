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
require '_includes/init.php';
// Page processing
// -----------------------------------------------------------------------------
$formErrors = false;
$formErrorMessage = '';

$errRoleID = '';
$errPassphrase= '';

$authenticationMessage = '';
$authenticated = false;

$role_id = '';

$loginMethod = getQueryValue('loginmethod');

// Check the login method.
// =============================================================================
if( $eShibbolethAuthDomain != '' && $eShibbolethSessionInitiator != '' && $loginMethod == 'shibboleth' )
{
	responseRedirect("https://" . eHOST ."/Shibboleth.sso/".esc($eShibbolethSessionInitiator.'?target='.urlencode(eAPP_ROOT.'login.php?page='.urlencode(getQueryValue('page')))));
}

// Check posted credentials destined for either built-in or LDAP authentication.
// =============================================================================
if( $_POST )
{
	$role_id = getPostedValue('role_id');
	$passphrase = getPostedValue('passphrase');
	
	if( $role_id == '' )
	{
		$formErrors = true;
		$formErrorMessage .= "Please enter a User ID.\n";
		$errRoleID = ' class="errorText"';
	}
	
	if( $passphrase == '' )
	{
		$formErrors = true;
		$formErrorMessage .= "Please enter a Passphrase.\n";
		$errPassphrase = ' class="errorText"';
	}
	
	if( !$formErrors )
	{
		$userName = '';
		$authDomain = '';
		$authenticated = authenticate($role_id, $passphrase, $authenticationMessage, $userName, $authDomain);
		if( !$authenticated )
		{
			$formErrorMessage = $authenticationMessage;
		}
		else
		{
			startSession($role_id, $userName, $authDomain);
			if( getPostedValue('page') != '' )
			{
				// Take them back to the intended destination.
				responseRedirect(getPostedValue('page'));
			}
		}
	}
}
else
{
// Check HTTP headers for shibboleth authentication.
// =============================================================================

	if( getQueryValue('shib') == 2 )
	{
		$formErrorMessage = "LOGIN FAILED\nYour shibboleth identity provider does not \nuniquely identify you in the federation [100].\n";
	}

	// Check if we need to start a new session after authentication from a shibboleth IDP.
	if( haveShibbolethAttributes() && !getQueryValue('logout') && isAuthenticationServiceEnabled(gAUTHENTICATION_SHIBBOLETH) && $loginMethod != 'local' )
	{
		$this_role_id = getShibbolethUserId();
		if( $this_role_id == '' )
		{
			// End the local shibboleth session (there is no means of doing a global shibboleth logout) 
			// passing a directive to return back here.
			responseRedirect("https://" . eHOST .'/Shibboleth.sso/Logout?return='.eAPP_ROOT.'login.php?shib=2');
		}
		else
		{
			// Check that they're not a COSI user that has been disabled.
			if( isCosiUser($this_role_id) && !isRoleEnabled($this_role_id) )
			{
				$formErrorMessage = "LOGIN FAILED\nUser not enabled [105].\n";
			}
			else
			{
				$authenticated = true;

				$userName = getRoleName($this_role_id);
				if( !$userName )
				{
					// They're not in COSI users so try to get the name from the shibboleth data.
					$userName = getShibbolethUserName();
				}
				else
				{
					updateLastLogin($this_role_id);
				}
				startSession($this_role_id, $userName, $eShibbolethAuthDomain);
				if( getQueryValue('page') != '' )
				{
					// Take them back to the intended destination.
					responseRedirect(getQueryValue('page'));
				}
			}	
		}
	}
	elseif( haveShibbolethAttributes() && !getQueryValue('logout') && $loginMethod != 'local' )
	{
		$formErrorMessage = "LOGIN FAILED\nService unavailable [110].\n";
	}
	else
	{
// Logout processing.
// =============================================================================

		// We're logging the user out.
		// Check for the expired cosi session flag.
		$expired = '';
		if( getQueryValue('expired') )
		{
			$formErrorMessage = "Your session has expired due to $gSessionTimeoutMinutes minutes of inactivity.";
			$expired = urlencode("&expired=1");
		}
		
		// Check if we're ending a shibboleth session too.
		if( haveShibbolethAttributes() && $loginMethod != 'local' )
		{	
			// End the local shibboleth session (there is no means of doing a global shibboleth logout) 
			// passing a directive to return back here.
			responseRedirect("https://" . eHOST .'/Shibboleth.sso/Logout?return='.eAPP_ROOT.'login.php?shib=1'.$expired);
		}
		// End the cosi session.
		endSession();
	}
}
// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '_includes/header.php';
// BEGIN: Page Content
// =============================================================================
?>
<table class="formTable" summary="layout" style="margin: 0px;">
	<thead>
		<tr>
			<td style="width: 100px;"></td>
			<td>Login</td>
		</tr>
	</thead>
	<tbody><tr><td></td><td></td></tr></tbody>
</table>

<?php if( getQueryValue('shib') == 1 ){ ?>
	<table class="formTable" summary="layout" style="margin: 0px;">
		<tbody>
			<tr>
				<td style="width: 100px;"></td>
				<td>To completely logout your Shibboleth session<br />
					you must quit your web browser.
				</td>
			</tr>
		</tbody>
	</table>
<?php } ?>

<?php if( !$authenticated ){ ?>	
	<?php if( $eShibbolethAuthDomain != '' && $eShibbolethSessionInitiator != '' ){ ?>
			<form id="login_method_form" action="login.php?" method="get">
				<table class="formTable" summary="Login Method Form" style="margin: 0px;">
					<?php if( $formErrorMessage != '' && $loginMethod != 'local' ){ ?>
						<tbody>
							<tr>
								<td></td>
								<td class="errorText"><?php printSafeWithBreaks($formErrorMessage) ?></td>
							</tr>
						</tbody>
					<?php } ?>
					<tbody class="formFields">
						<tr>
							<td style="width: 100px;"></td>
							<td>
								<input type="hidden" name="page" value="<?php printSafe(getQueryValue('page')) ?>" />
								<select name="loginmethod" onchange="submit(true);">
								<?php
									setChosen('loginmethod', '', gITEM_SELECT);
									print("<option value=\"\"$gChosen>Select a login method</option>\n");
									setChosen('loginmethod', 'shibboleth', gITEM_SELECT);
									print("<option value=\"shibboleth\"$gChosen>Login using Australian Access Federation (AAF) credentials</option>\n");
									setChosen('loginmethod', 'local', gITEM_SELECT);
									print("<option value=\"local\"$gChosen>Login using local credentials</option>\n");
								?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php } ?>
		
		<?php if( $eShibbolethAuthDomain == '' || $eShibbolethSessionInitiator == '' || $loginMethod == 'local' ){ ?>
			<form id="login_form" action="login.php?loginmethod=local" method="post">
				<table class="formTable" summary="Local Login Form" style="margin: 0px;">
					<?php if( $formErrorMessage != '' ){ ?>
						<tbody>
							<tr>
								<td></td>
								<td class="errorText"><?php printSafeWithBreaks($formErrorMessage) ?></td>
							</tr>
						</tbody>
					<?php } ?>
					<tbody class="formFields">
						<tr>
							<td style="width: 100px;"<?php print $errRoleID ?>>User ID:</td>
							<td><input type="text" name="role_id" size="25" maxlength="255" value="<?php printSafe($role_id) ?>" /></td>
						</tr>
						<tr>
							<td<?php print $errPassphrase ?>>Passphrase:</td>
							<td><input type="password" name="passphrase" size="25" maxlength="255" value="" /></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="hidden" name="page" value="<?php printSafe(getQueryValue('page')) ?>" /><input type="submit" name="action" value="Login" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php } ?>	
<?php } else { ?>
	<!-- WHY is even a TABLLEEE?!-->
	<table class="formTable" summary="layout" style="margin: 0px;">
		<tbody>
			<tr>
				<td style="width: 100px;"></td>
				<td><p>You are now logged in as <b><?php printSafe(getSessionVar(sNAME))?></b><br />
				using the authentication provider's identifier of <b><?php printSafe(getSessionVar(sROLE_ID)) ?></b>.</p>
				
				<p>You can <i>Logout</i> using the link at the upper left.</p></td>
			</tr>
			<tr>
				<td style="width:100px"></td>
				<td>
					<?php 
//Chrome Frame message
// We test the user browser and the presence of the Chrome Frame plug-in
$message = '<div style="padding: 10px;border: 1px solid #444;margin: 0px -10px;">
			The ANDS Registry is optimised for use in modern web browsers (such as <a href="http://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox</a>, <a href="https://www.google.com/intl/en/chrome/browser/" target="_blank">Google Chrome</a> and <a href="http://windows.microsoft.com/en-AU/internet-explorer/products/ie/home" target="_blank">Internet Explorer 9+</a>).<br/>
			We have detected that you may be using an <b>older</b> browser. <br/>
			<br/>
			Whilst we strongly recommend upgrading to one of the modern browsers listed above, <br/>
			you can also experience similar performance by enabling the <a href="http://www.google.com/chromeframe/?user=true&quickenable=true?redirect=true" target="_blank">Google Chrome extension for Internet Explorer</a><sub>[1]</sub>.<br/> 
			<br/>
			ANDS continues to support Internet Explorer 7 & 8,0 however some features may degrade in performance when using these browsers.<br/>
			<br/>
			Please contact <a href="mailto:services@ands.org.au">services@ands.org.au</a> for further information.<br/>
			<br/>
			<sub>[1] Google Chrome Frame is a free extension that seamlessly brings many features of modern browsers to Internet Explorer. <br/>
			Google Chrome Frame can usually be enabled on systems where upgrading to a modern browser is not possible (such as corporate networks). <br/>
			Google Chrome is used by more than 30% of the world\'s internet users and provides state-of-the-art web technologies and improved browser security. <br/></sub>

			</div>';
if ( stristr($_SERVER['HTTP_USER_AGENT'], 'chromeframe') ) {
	//chrome frame is installed
}elseif ( strstr($_SERVER['HTTP_USER_AGENT'], '; MSIE 6') ) {
	print $message;
} elseif ( strstr($_SERVER['HTTP_USER_AGENT'], '; MSIE 7') ) {
	print $message;
} elseif ( strstr($_SERVER['HTTP_USER_AGENT'], '; MSIE 8' ) ) {
	print $message;
} elseif ( strstr($_SERVER['HTTP_USER_AGENT'], '; MSIE 9' ) ) {
	//print $message;
} else {//good browser
	//nothing to do here, move along
	//print $message;
}
					?>
				</td>
			</tr>
		</tbody>
	</table>	
<?php } ?>



<?php
// =============================================================================
// END: Page Content
// Complete the XHTML response.
require '_includes/footer.php';
require '_includes/finish.php';
?>
