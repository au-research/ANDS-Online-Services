<HTML>
	<HEAD>
		<TITLE>
			Australian National Data Service (ANDS) Software Repository
		</TITLE>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
		<script language="JavaScript">
			
			$(document).ready(function(){
				$('#Form_1_a').submit(function(){
					$('#Form_1_Submit').hide();
					$('#loading_image').show();
				});
				
				function hideform(form_id){
					$("#"+form_id).hide();
				}
			});
			
			
			
		</script>
		
		<style>	
			#form{padding:20px;border:1px solid green;margin:10px auto;}
			input {  
			    padding: 9px;  
			    border: solid 1px #E5E5E5;  
			    outline: 0;  
			    font: normal 13px/100% Verdana, Tahoma, sans-serif;  
			    width: 400px;  
			    background: #FFFFFF;  
			    }  
			    
			select {  
			    padding: 9px;  
			    border: solid 1px #E5E5E5;  
			    outline: 0;  
			    font: normal 13px/100% Verdana, Tahoma, sans-serif;  
			    width: 400px;  
			    background: #FFFFFF;  
			    } 
			  
			textarea {  
			    width: 100%;  
			    height: 300px;  
			    line-height: 150%; 
			    text-align:left;
			    }  
			  
			input:hover, textarea:hover,  
			input:focus, textarea:focus {  
			    border-color: #C9C9C9;  
			    }  
			  
	
			 label {
			 	padding:9px 0;
			    font-family: Arial, Verdana; 
			    display: block;
			    float: left;
			    margin-right:10px;
			    text-align: right;
			    width: 220px;
			    line-height: 25px;
			    font-size: 15px;
			    }
			.hide{display:none;}
		</style>
	</HEAD>
	
	<BODY>
		<CENTER>
			<H1>Australian National Data Service (ANDS) Software Repository</H1>
			<hr>
			<BR>
			
			<?php session_start(); 
				//error_reporting(-1);
					
					$server = "ands1.anu.edu.au";
					//$server = "localhost";
					$username = "anonymous";
					$password = "";
			
			?>
				<p>
				
				<form id="Form_Start" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		

					The following installation script will both guide you and assist you in setting up an ANDS research data portal.
					<br><p>
					Before you begin the installation please ensure you have the following products setup and configured correctly:
					<p>

					
						
					<table border="1">
					
						<tr><td>Apache (Required)</td>
										
						<?php

							if(! getModuleSetting('apache2handler','Apache API Version') == "") {
								echo "<td>installed</td><td>".getModuleSetting('apache2handler','Apache API Version');"</td></tr>";
								echo "<img src='http://services.ands.org.au/downloads/tick-icon.png'/>";
							} else {
								echo "<td><i>Required</i></td><td></td><img src='http://services.ands.org.au/downloads/cross_icon.gif'/></td></tr>";
							}
							
							
							echo "<tr><td>PHP (Required)</td>";
							
							if(! getModuleSetting('HTTP Headers Information','X-Powered-By') == "") {
									echo "<td>installed</td><td>".getModuleSetting('HTTP Headers Information','X-Powered-By');"</td></tr>";
									echo "<img src='http://services.ands.org.au/downloads/tick-icon.png'/>";
								} else {
									echo "<td><i>Required</i></td><td><img src='http://services.ands.org.au/downloads/cross_icon.gif'/></td></tr>";
								}
							
							echo "<tr><td>Postgres (Required)</td>";
							
							if(! getModuleSetting('pdo_pgsql','PostgreSQL(libpq) Version') == "") {
									echo "<td>installed</td><td>".getModuleSetting('pdo_pgsql','PostgreSQL(libpq) Version');"</td></tr>";
									echo "<img src='http://services.ands.org.au/downloads/tick-icon.png'/>";
								} else {
									echo "<td><i>Required</i></td><td><img src='http://services.ands.org.au/downloads/cross_icon.gif'/></td></tr>";
								}
							
							echo "<tr><td>Tomcat</td><td>Required</td><td> </td></tr>";
							echo "<tr><td>Tomcat - SOLR</td><td>Required</td><td> </td></tr>";
							echo "<tr><td>Tomcat - Harvester</td><td>Recommended</td><td> </td></tr>";

						?>
					</table>
					<p>
					
					<br>
					<input type="hidden" name="Submit" value="sent">
					<input type="submit" name="form_Start_Submit" id="form_Start_Submit"  value="Begin Installation" />
					<p>

				</form> 
				
					
			<?php

	//*********************************** Form 1 ***************************************************
	//*********************************** Select Package *******************************************
			
			
			if(isset($_POST['form_Start_Submit'])) 
			{
			?>
				<p>
				<form id="Form_1_a" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		
					<?php
					
			
					
					echo "<script>hideform('Form_Start');</script>";
					
						$connect = ftp_connect($server);
						$result = ftp_login($connect, $username, $password);
						//ftp_pasv($connect, false);
						
						$a = ftp_nlist($connect, ".");
						foreach($a as $value){
							$array[] = $value;
						}

						echo "<select name=\"rel\" id=\"rel\" size='10' >\n";
						
						foreach($array as $v){
							if ($rel == $v) {
								echo "<option value=\"$v\" selected=\"selected\">$v</option>\n";
							} else {
								echo "<option value=\"$v\">$v</option>\n";
							}
						}
						
						echo "</select>";
						echo "<br>";
						
						if (is_dir("./home")){
							echo "<p><br>";
							$content = file('./home/version.txt');
							echo "<i>Note: Your webserver currentlly contains </i><b>" . $content[0];  
							echo "<p>";
						} else {
							echo "<p><br>";
							echo "The system could not identify any previously installed packages on your webserver, please select a package to download"; 
							echo "<p>";
						} 
					?>
					
					<br>
					<input type="hidden" name="Submit" value="sent">
					<input type="submit" name="Form_1_Submit" id="Form_1_Submit"  value="Download Package"/>
					<img src='http://services.ands.org.au/downloads/loading36.gif' style="display:none;" id="loading_image"/>
					<p>

				</form> 
					
					
					<?php
			}
//*********************************** Form 2 ***************************************************	
//*********************************** Downloading Package *******************************************		
		
			if(isset($_POST['Form_1_Submit'])) 
			{
				$release = $_POST['rel'];
				
				echo "<form id='Form_2'>";
				echo "<script>hideform('Form_Start');</script>";

				$ftp_server = $server;
				$conn_id = ftp_connect ($ftp_server)
					or die("Couldn't connect to $ftp_server");
				   
				$login_result = ftp_login($conn_id, $username, $password);
				//ftp_pasv($ftp_server, false);
				
				if ((!$conn_id) || (!$login_result))
					die("FTP Connection Failed");

					
				if (is_dir("./home")){
					echo "<br></b>home folder already exists, please rename or delete the existing home folder before continuing";
						// maybe copy files from release folder to home dir if release is upgrade
					exit;
				} else {
					ftp_sync ($release);				
					
					
					ftp_close($conn_id); 
						
					sleep(1);   // change downloaded release dir name, for some reason you need to add sleep, other wise permission error will pop up
						rename ("./" . $release, "./home");	
				}
				
				echo "</form>";			

				
//*********************************** Form 3 ***************************************************
//*********************************** Package Downloaded *******************************************	
		?>
				<form id="Form_3" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			
					<?php
						echo "<script> hideform('Form_2'); </script>";
						$content = file('./home/version.txt');
						echo "<b>Download Complete</b> <p><b><i>" . $content[0] . "</i></b>, has been downloaded to your webserver root directory.";
						echo "<p><br>";
						echo "<hr>";
					?>

					<p>
					<input type='hidden' name='Continue' value='sent'>
					<input type='submit' name='form_3_Submit' id='form_3_Submit' value='Continue' />
				
				</form>
				
			<?php
			}
					
	//*********************************** Form 4 ***************************************************
	//*********************************** Package Setup *******************************************	
	
			if(isset($_POST['form_3_Submit'])) 
			{
			?>
				<form id="Form_4" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			
					<?php
						echo "<script>hideform('Form_Start');</script>";
						echo "Package Setup";
						echo "<br>";
						
					?>
					</CENTER>
				
					<fieldset>
						<legend>Application Environment</legend>
						<p><label>Server URL: </label><input type="text" value="<?php echo getModuleSetting('Apache Environment','HTTP_HOST'); ?>" name="host_domain"/></p>
						<p><label>Protocol: </label><select name="select" value="http">
							<option value="http" selected>http</option>
							<option value="https">https</option>
						</select><p>
						<label>Google Map API Key: </label><input type="text" value="gmapAPI" name="gmap_api"/><a href="http://code.google.com/android/add-ons/google-apis/maps-api-signup.html" target="_blank">Get Key</a></p>
					</fieldset>
					<br>

					<br>
					<fieldset>
						<legend>Tomcat - Harvester / SOLR </legend>
						<p><label>Tomcat URL:port </label><input type="text" value="http://<?php echo getModuleSetting('Apache Environment','SERVER_ADDR');?>:8888/" name="tomcat_uri"/></p>
						<p><label>SOLR URL:port </label><input type="text" value="http://<?php echo getModuleSetting('Apache Environment','SERVER_ADDR');?>:8983/solr" name="solr_url"/> in relation to Tomcat URL</p>
						<p><label>Harvester Directory:</label><input type="text" value="/harvester" name="harvester_url"/> in relation to Tomcat URL</p>
					</fieldset>

					<CENTER>
					<p>
					<input type='hidden' name='Continue' value='sent'>
					<input type='submit' name='form_4_Submit' id='form_4_Submit' value='Continue' />

				</form>
				
			<?php
				}
					
//*************************************** Form 5 **************************************************
//*********************************** Database test *******************************************	
					
			if(isset($_POST['form_4_Submit'])) 
			{
			?>

				<form id="Form_5" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

					<?php
					
						$_SESSION['host_domain'] = $_POST['host_domain'];
						$_SESSION['protocol'] = $_POST['select'];
						$_SESSION['gmap_api'] = $_POST['gmap_api'];
						$_SESSION['tomcat_uri'] = $_POST['tomcat_uri'];
						$_SESSION['solr_url'] = $_POST['solr_url'];
						$_SESSION['harvester_url'] = $_POST['harvester_url'];

						echo "<script>hideform('Form_Start');</script>";
						echo "Database Setup";
						echo "<br>";
						
					?>
					
					</CENTER>
					<fieldset>
						<legend>Database Environment</legend>
						<p><label>Postgres DB URL:</label><input type="text" name="address" value="<?php if(isset($_POST['address'])) echo $_POST['address']; ?>" /></br>
						<p><label>Postgres DB Port: </label><input type="text" name="db_prt" value="<?php if(isset($_POST['db_prt'])) echo $_POST['db_prt']; ?>" /></br>
						<p><label>Username:</label><input type="text" name="db_usr" value="<?php if(isset($_POST['db_usr'])) echo $_POST['db_usr']; ?>" /></br>
						<p><label>Password:</label><input type="password" name="db_pwd" value="<?php if(isset($_POST['db_pwd'])) echo $_POST['db_pwd']; ?>" /></br>

						<p>
						<input type='hidden' name='test_connection' value='sent'>
						<input type='submit' name='form_5_Test' id='form_5_Test' value='Test Connection' />
					</fieldset>
					
				</form>
				
			<?php
			}
					
//*************************************** Form 6 **************************************************	
//*********************************** Database Setup *******************************************	

			if(isset($_POST['form_5_Test'])) 
			{

				$connection = pg_connect("host=".$_POST['address']." port=".$_POST['db_prt']." user=".$_POST['db_usr']." password=".$_POST['db_pwd']); 
				if ($connection) {
					
			?>
			
					<form id="Form_6" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

						<?php
							echo "<script>hideform('Form_Start');</script>";
							echo "Database Setup";
							echo "<br>";
						?>
						
						</CENTER>
						
						<fieldset>
							<legend>Database Environment</legend>
							<p><label>Postgres DB URL:</label><input type="text" name="address" value="<?php if(isset($_POST['address'])) echo $_POST['address']; ?>" /></br>
							<p><label>Postgres DB Port: </label><input type="text" name="db_prt" value="<?php if(isset($_POST['db_prt'])) echo $_POST['db_prt']; ?>" /></br>
							<p><label>Username:</label><input type="text" name="db_usr" value="<?php if(isset($_POST['db_usr'])) echo $_POST['db_usr']; ?>" /></br>
							<p><label>Password:</label><input type="password" name="db_pwd" value="<?php if(isset($_POST['db_pwd'])) echo $_POST['db_pwd']; ?>" /></br>
							<p><br> Connection Successfull. <img src='http://services.ands.org.au/downloads/icon_successful.gif'/></p>
						</fieldset>
							
						<CENTER>
							
							<p>
							<input type='hidden' name='Continue' value='sent'>
							<input type='submit' name='form_6_Submit' id='form_6_Submit' value='Continue' />
							
						</CENTER>
						
					</form>
					
				<?php
					
				} else { // connection failed.

					echo "<form id='Form_7'>";
						
						echo "<script>$('#Form_Start').hide(); </script>";
						print pg_last_error($connection);
						print "Error Connecting to database";
					
					echo "</form>";

				}
					pg_close($connection);

			}
					
//*************************************** Form 7 **************************************************
//*********************************** Exec Package config *******************************************	
//*********************************** SQL Injection Choice *******************************************	
					
			if(isset($_POST['form_6_Submit'])) 
			{
						
				$_SESSION['address'] = $_POST['address'];
				$_SESSION['db_prt'] = $_POST['db_prt'];
				$_SESSION['db_usr'] = $_POST['db_usr'];
				$_SESSION['db_pwd'] = $_POST['db_pwd'];

			?>
			
				<form id="Form_7" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

					<?php
					
						echo "<script> hideform('Form_Start'); </script>";
						echo "SQL Injection";
						echo "<br>";
						
					?>
				
					</CENTER>
				
					<fieldset>
						<legend>SQL</legend>
						please select the form of SQL injection:
						<p>
						<input type='hidden' name='Continue' value='sent'>
						<input type='submit' name='form_7_Auto' id='form_7_Auto' value='Automated Injection' /><br><p>
						<input type='submit' name='form_7_Manual' id='form_7_Manual' value='Manual Injection' />
					</fieldset>
					
				</form>
				
			<?php
			}

//*************************************** Form 8 **************************************************
//*********************************** Auto SQl Injecrtion - Directory Select *******************************************	

			if(isset($_POST['form_7_Auto'])) 
			{
			?>

				<form id="Form_8" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

					<?php
						echo "<script>$('#Form_Start').hide(); </script>";
						echo "SQL Injection (Automated)";
						echo "<br>";
						
					?>
					
					</CENTER>
					
						<fieldset>
							<legend>SQL</legend>
							Please enter the full local location of your Postgres root directory. e.g. /opt/postgresql<br>
							<p><label></label>				
							 <p><label>PSQL Location:</label><input type="text" name="PSQL" value="<?php if(isset($_POST['PSQL'])) echo $_POST['PSQL']; ?>" /></br>
							<p>
						</fieldset>
						
					<CENTER>	
						<p>
						<input type='hidden' name='Inject' value='sent'>
						<input type='submit' name='form_8_Inject' id='form_8_Inject' value='Inject' />
					</CENTER>
					
				</form>
				
				
			<?php
			}

//*************************************** Form 9 **************************************************
//*********************************** Manual SQl Injecrtion *******************************************	

			if(isset($_POST['form_7_Manual'])) 
			{
			?>

				<form id="Form_9" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

					<?php
					
						echo "<script>$('#Form_Start').hide(); </script>";
						echo "SQL Injection (Manual)";
						echo "<br>";
							
					?>
					</CENTER>
					
					<fieldset>
						<legend>Step 1. Setup Users and Databases</legend>
						Connect to your postgres database and complete the following tasks:<br>
						<p>
						CREATE USER: webuser<br>
						CREATE USER: dba<br>
						CREATE USER: harvester<br>
						<p>
						CREATE DATABASE: dbs_cosi<br>
						CREATE DATABASE: dbs_orca<br>
						CREATE DATABASE: harvester

						<p>
					</fieldset>
					<br>
					<fieldset>
						<legend>COSI</legend>
						<textarea name="comments" cols="40" rows="5">
							
						<?php
							include('./home/install_files/SQL/cosi.sql');
						?>
						
						</textarea>

						<p>
					</fieldset>
					<br>
					<fieldset>
						<legend>ORCA</legend>
							<textarea name="comments" cols="40" rows="5">
							<?php
								include('./home/install_files/SQL/orca.sql');
							?>
							</textarea>

						<p>
					</fieldset>
					<br>
					<fieldset>
						<legend>Harvester</legend>
							<textarea name="comments" cols="40" rows="5">
							<?php
								include('./home/install_files/SQL/harvester.sql');
							?>
							</textarea>

						<p>
					</fieldset>
					
					<CENTER>		
						<p>
						<input type='hidden' name='Continue' value='sent'>
						<input type='submit' name='form_9_Submit' id='form_9_Submit' value='Continue' />
					</CENTER>
				</form>
				
				
			<?php
			}

//*************************************** Form 10 **************************************************	
//*********************************** Auto SQl Injecrtion*******************************************	
			
			
			if(isset($_POST['form_8_Inject'])) 
			{
			?>

				<form id="Form_10" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

					<?php
						echo "<script>$('#Form_Start').hide(); </script>";
						echo "Injecting SQL";
						echo "<br>";
						
						$_SESSION['PSQL'] = $_POST['PSQL'];

					?>
					
					</CENTER>
					
					<fieldset>
						<legend>SQL inject</legend>
															
						<?php
							echo '<pre>';
							
							if(php_uname("s") == 'Linux'){
								$last_line = system($_SESSION['PSQL'].'/bin/psql -h '.$_SESSION['address'].' -U '.$_SESSION['db_usr'].' -f '.getcwd().'/home/install_files/SQL/dbs.sql', $retval);
							} else {
								//$last_line = system($_SESSION['PSQL'].'\bin\psql.exe -h '.$_SESSION['address'].' -U '.$_SESSION['db_usr'].' -f '.getcwd().'\home\install_files\SQL\dbs.sql', $retval);
								echo "windows exec dont work, fix me";
							}
							
							echo '</pre><hr />Return value: ' . $retval; 
						?>
						
						<p>
					</fieldset>
						
					<CENTER>
						<?php
						if($retval==0)
						{	
						?>	
							<p>
							<input type='hidden' name='Continue' value='sent'>
							<input type='submit' name='form_10_Submit' id='form_10_Submit' value='Continue' />
						<?php
						} else {
							
							?>	
							<p>
							<input type='hidden' name='Continue_manual' value='sent'>
							<input type='submit' name='form_10_Manual' id='form_10_Manual' value='proceed to manual injection' />
							<?php
						}
						?>
					</CENTER>	
					
				</form>
				
				
			<?php
			}
	
//*************************************** Form 11 **************************************************
//*********************************** RDA User config *******************************************	
	
			if(isset($_POST['form_10_Submit']) || isset($_POST['form_9_Submit'])) 
			{
			?>

				<form id="Form_11" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

					<?php
						echo "<script> hideform('Form_Start'); </script>";
						echo "Portal Configuration";
						echo "<br>";
									
					?>
					
					</CENTER>
					
					<p><label>Application Title:</label><input type="text" name="app_title" value="Online Servies" /></br>
					<p><label>Application Instance Title: </label><input type="text" name="inst_title" value="Research Data Portal" /></br>
					<p><label>Short Instance Title: </label><input type="text" name="inst_short" value="RPD" /></br>
					<p><label>Conatct Email:</label><input type="text" name="contact_email" value="" /></br>
					<p><label>Conatct Name:</label><input type="text" name="contact_name" value="" /></br>

					<CENTER>	
						<p>
						<input type='hidden' name='Continue' value='sent'>
						<input type='submit' name='form_11_Submit' id='form_11_Submit' value='Continue' />
					</CENTER>
					
				</form>

			<?php					
			}
					
//*************************************** Form 12 **************************************************
//*********************************** Send All data to application files *******************************************	
	
			if(isset($_POST['form_11_Submit'])) 
			{
			
				$_SESSION['app_title'] = $_POST['app_title'];
				$_SESSION['inst_title'] = $_POST['inst_title'];
				$_SESSION['inst_short'] = $_POST['inst_short'];
				$_SESSION['contact_email'] = $_POST['contact_email'];
				$_SESSION['contact_name'] = $_POST['contact_name'];
				
			?>
			
			</center>
				<form id="Form_12" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

				<pre>
				Your installation is just about complete, just a few more things to do before you can begin to use your new 
				Research Data portal<br><p>
				
				Just complete the following few steps:<br><p>

				1. Edit the Postgres pg_hba.conf to allow trusted access from the web <br>
				server for the 'webuser' and 'dba' accounts. <br>

				insert the following lines: <br>

				host	all		all			127.0.0.1/32	trust <br>
				host    all		webuser			127.0.0.1/32	trust <br>
				host    all		dba			127.0.0.1/32	trust <br>


				* Note: ensure you alter the above ip addresses to match your servers requirements.  <br>

				Now restart your Postgres server.  <br>
				
				

				Congratulations you have now setup and installed your Research Data Portal. Press Continue to proceed to the Research Data home page.
				</pre>

					<?php

						echo "<script> hideform('Form_Start'); </script>";

						$config_files = array(
										'home/_includes/_environment/application_env.php',
										'home/_includes/_environment/database_env.php',
										'home/orca/_includes/init.php',
										'home/orca/rda/application/config/config.php',
										'home/orca/rda/application/config/database.php',
												);
						
						install($_SESSION, $config_files);
			
					?>

					<CENTER>		
						<p>
						<input type='hidden' name='Complete' value='sent'>
						<input type='submit' name='form_12_Submit' id='form_12_Submit' value='Continue' />
					</CENTER>
					
				</form>
				
			<?php
			}
					
//*********************************** Functions ************************************************

//*********************************** FTP Sync ************************************************				
			function ftp_sync ($dir) {

				global $conn_id;

				if ($dir != ".") {
					if (ftp_chdir($conn_id, $dir) == false) {
						echo ("Change Dir Failed: $dir<BR>\r\n");
						return;
					}
					if (!(is_dir($dir)))
						mkdir($dir);
					chdir ($dir);
				}

				$contents = ftp_nlist($conn_id, ".");
				foreach ($contents as $file) {
			   
					if ($file == '.' || $file == '..')
						continue;
				   
					if (@ftp_chdir($conn_id, $file)) {
						ftp_chdir ($conn_id, "..");
						ftp_sync ($file);
					}
					else
						ftp_get($conn_id, $file, $file, get_ftp_mode($file));
				}
				   
				ftp_chdir ($conn_id, "..");
				chdir ("..");

			} 
			
//*********************************** get_ftp_mode ************************************************
			function get_ftp_mode($file)
			{   
				$path_parts = pathinfo($file);
			   
				if (!isset($path_parts['extension'])) return FTP_BINARY;
				switch (strtolower($path_parts['extension'])) {
					case 'am':case 'asp':case 'bat':case 'c':case 'cfm':case 'cgi':case 'conf':
					case 'cpp':case 'css':case 'dhtml':case 'diz':case 'h':case 'hpp':case 'htm':
					case 'html':case 'in':case 'inc':case 'js':case 'm4':case 'mak':case 'nfs':
					case 'nsi':case 'pas':case 'patch':case 'php':case 'php3':case 'php4':case 'php5':
					case 'phtml':case 'pl':case 'po':case 'py':case 'qmail':case 'sh':case 'shtml':
					case 'sql':case 'tcl':case 'tpl':case 'txt':case 'vbs':case 'xml':case 'xrc':
						return FTP_ASCII;
				}
				return FTP_BINARY;
			}
					
//*********************************** Start Install (send data to files) ************************************************					
			function install($post, $config_files){
				
				//var_dump($_SESSION);
				echo "<br><p>";
				//var_dump($post);
			
				//application environment
				changeConfig($config_files[0],'{host_domain}', $post['host_domain']);
				changeConfig($config_files[0],'{protocol}', $post['protocol']);
				changeConfig($config_files[0],'{app_title}', $post['app_title']);
				changeConfig($config_files[0],'{inst_title}', $post['inst_title']);
				changeConfig($config_files[0],'{inst_short}', $post['inst_short']);
				changeConfig($config_files[0],'{contact_email}', $post['contact_email']);
				changeConfig($config_files[0],'{contact_name}', $post['contact_name']);
				
				changeConfig($config_files[1],'{cosi_db_host}', $post['address']);
				changeConfig($config_files[1],'{orca_db_host}', $post['address']);
				
				changeConfig($config_files[2],'{host_domain}', $post['host_domain']);
				changeConfig($config_files[2],'{harvester_uri}', $post['harvester_url']);
				changeConfig($config_files[2],'{solr_url}', $post['solr_url']);
				changeConfig($config_files[2],'{gmap_api}', $post['gmap_api']);
				
				changeConfig($config_files[3],'{host_domain}', $post['host_domain']); // need to remove any http in string before passing.
				changeConfig($config_files[3],'{solr_url}', $post['solr_url']);
				
				changeConfig($config_files[4],'{host_domain}', $post['host_domain']);
				
			}

//*********************************** Install (send data to files) ************************************************	
			function changeConfig($file_name, $what, $with){
				
				$file = $file_name;
				chmod($file_name, 0777); 
				clearstatcache();
				$file_permission = substr(sprintf('%o', fileperms($file)), -4);
				//echo $file_permission;
				if(is_readable($file) && ($file_permission=='0777')){
					$lines = file($file);
					$all_lines = implode('',$lines);
					$entry = str_replace($what,$with,$all_lines);
					//echo $entry;
					$fp = fopen($file,'w'); 
					if(is_writable($file)){
						$fw = fwrite($fp,$entry);
						fclose($fp);
						//return "<span class=\"success\">Successfully changed <b>$file</b>, updated <b>$what</b> with <b>$with</b></span>";
					}else{
						return "<span class=\"error\">Unable to write to $file: $file_permission</span>";
					}
				}else{
					return "<span class=\"error\">Unable to write to $file: $file_permission</span>";
				}
			}
			
//************************************* parse php modules from phpinfo *************************************************
			function parsePHPModules() {
			 ob_start();
			 phpinfo(INFO_MODULES);
			 $s = ob_get_contents();
			 ob_end_clean();
			 
			 $s = strip_tags($s,'<h2><th><td>');
			 $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
			 $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
			 $vTmp = preg_split('/(<h2>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
			 $vModules = array();
			 for ($i=1;$i<count($vTmp);$i++) {
			  if (preg_match('/<h2>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
			   $vName = trim($vMat[1]);
			   $vTmp2 = explode("\n",$vTmp[$i+1]);
			   foreach ($vTmp2 AS $vOne) {
				$vPat = '<info>([^<]+)<\/info>';
				$vPat3 = "/$vPat\s*$vPat\s*$vPat/";
				$vPat2 = "/$vPat\s*$vPat/";
				if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
				 $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
				} elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
				 $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
				}
			   }
			  }
			 }
			 return $vModules;
			} 
			
			
			function getModuleSetting($pModuleName,$pSetting) {
			 $vModules = parsePHPModules();
			 return $vModules[$pModuleName][$pSetting];
			} 

				
			?>

	<BODY>
</HTML>


