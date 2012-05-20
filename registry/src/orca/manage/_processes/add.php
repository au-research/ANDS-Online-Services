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
if (!IN_ORCA) die('No direct access to this file is permitted.');

error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set("display_errors", 1);
		if (isset($_GET['key']) && $draft = getDraftRegistryObject(rawurldecode($_GET['key']), $dataSourceValue)) 
		{
			$rifcs = new DomDocument();
			$rifcs->loadXML($draft[0]['rifcs']);
			$stripFromData = new DomDocument();
			$stripFromData->load('../_xsl/stripFormData.xsl');
			$proc = new XSLTProcessor();
			$proc->importStyleSheet($stripFromData);
			$registryObject = $proc->transformToDoc($rifcs);
			//print_pre($draft);
			$dataSourceKey = $draft[0]['registry_object_data_source'];
			$errorMessages = "";
			$deleteErrors = "";
	        $errors = error_get_last();
	   
			if( $errors )
			{
				$errorMessages .= "Document Load Error";
				$errorMessages .= "<div style=\"margin-top: 8px; color: #880000; height: 100px; width: 500px; padding: 0px; white-space: pre-wrap; overflow: auto; font-family: courier new, courier, monospace; font-size:9pt;\">";
				$errorMessages .= esc($errors['message']);
				$errorMessages .= "</div>\n";
			}
			            
			if( !$errorMessages )
			{
				// Validate it against the orca schema.
			    // libxml2.6 workaround (Save to local filesystem before validating)
			  
			    // Create temporary file and save manually created DOMDocument.
			    $tempFile = "/tmp/" . time() . '-' . rand() . '-document.tmp';
			    $registryObject->save($tempFile);
			    
			    // Create temporary DOMDocument and re-load content from file.
			    $registryObject = new DOMDocument();
			    $registryObject->load($tempFile);
			    
			    // Delete temporary file.
			    if (is_file($tempFile))
			    {
			      unlink($tempFile);
			    }
			  
				$result = $registryObject->schemaValidate(gRIF_SCHEMA_PATH); //xxx
				$errors = error_get_last();
				//print($dataSourceKey);
				//exit;
				
				if( $errors )
				{
					$errorMessages .= "Document Validation Error";
					$errorMessages .= "<div class=\"readonly\" style=\"margin-top: 8px; color: #880000; height: 100px; width: 500px; padding: 0px; white-space:pre-wrap; overflow: auto; font-family: courier new, courier, monospace;font-size: 9pt;\">";
					$errorMessages .= esc($errors['message']);
					$errorMessages .= "</div>\n";
				}
				else
               	{
					$importErrors = importRegistryObjects($registryObject,$dataSourceKey, $resultMessage, getLoggedInUser(), PUBLISHED, getThisOrcaUserIdentity(), null, true);       
					runQualityLevelCheckForRegistryObject($_GET['key'], $dataSourceKey);
					$result = addSolrIndex($_GET['key']);
					if( !$importErrors )
					{
						$deleteErrors = deleteDraftRegistryObject($dataSourceValue, esc($_GET['key']));
					}                                       
					if( $deleteErrors || $importErrors )
					{
						print("<p>ERROR$deleteErrors</p><p>$importErrors</p>");
					}
					else
					{
						//print("<p>RESULT OF SOLR INDEXING:.$result.ENDRSULT</p>");
						print("<script>$(window.location).attr('href','".eAPP_ROOT."orca/view.php?key=".esc($_GET['key'])."');</script>");
					}
				}
			}

		}
		else
		{       
			print("<p>WRONG draft key </p>"); 
		}