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
importApplicationStylesheet(eAPP_ROOT.'orca/_styles/manage_my_records.css');
// Page processing
// -----------------------------------------------------------------------------

$data_source_key = urldecode(getQueryValue('data_source'));
$this_url = eAPP_ROOT . "orca/manage/my_tags.php?";

$errors = array();

// Get data sources which we have access to
$rawResults = getDataSources(null, null);
$dataSources = array();

if( $rawResults )
{
	foreach( $rawResults as $dataSource )
	{
		if( (userIsDataSourceRecordOwner($dataSource['record_owner']) || userIsORCA_QA()) )
		{
			$dataSources[] = $dataSource;
		}
	}
}

// Allow user to browse to the appropriate data source
if (!$data_source_key)
{
	if (count($dataSources) == 1)
	{
		header("Location: " . $this_url . "data_source=" . rawurlencode($dataSources[0]['data_source_key']));
		die();
	}
}

// -----------------------------------------------------------------------------
// Begin the XHTML response. Any redirects must occur before this point.
require '../../_includes/header.php';

//google chart
echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';

echo '<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/orca_dhtml.js"></script>
		<script type="text/javascript" src="'. eAPP_ROOT.'orca/_javascript/mmr_dhtml.js"></script>
		<input type="hidden" id="elementSourceURL" value="' . eAPP_ROOT . 'orca/manage/process_registry_object.php?" />';

//CHOSEN Javascript library for choosing data sources
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_javascript/chosen/chosen.css" />
		<script src="'. eAPP_ROOT.'orca/_javascript/chosen/chosen.jquery.js" type="text/javascript"></script>';


//FLEXIGRID
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_javascript/flexigrid/css/flexigrid.css" />
		<script src="'. eAPP_ROOT.'orca/_javascript/flexigrid/js/flexigrid.js" type="text/javascript"></script>';

//QTIP at COSI level
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'/_javascript/qtip2/jquery.qtip.css" />
		<script src="'. eAPP_ROOT.'/_javascript/qtip2/jquery.qtip.js" type="text/javascript"></script>';

//Specific MMR Styles
echo '<link rel="stylesheet" href="'. eAPP_ROOT.'orca/_styles/mmr.css" />';


echo '<h1>Manage My Tags</h1>';

if (!$data_source_key)
{
	displayMMRDataSourceSwitcher($dataSources);
	echo '<div id="last_ds"></div>';
}
else
{
	$dataSource = getDataSources($data_source_key, null);
	if(!(userIsDataSourceRecordOwner($dataSource[0]['record_owner']) || userIsORCA_QA()) )
	{
		die("<font color='red'>Error: Access Denied for Datasource</font>");
	}
	if (($dataSource && count($dataSource) === 1) || $data_source_key == "PUBLISH_MY_DATA" || $data_source_key =='ALL_DS_ORCA')
	{
		if (!$dataSource){
			if($data_source_key=='PUBLISH_MY_DATA'){
				$dataSource = array(
					'data_source_key' => 'PUBLISH_MY_DATA',
					'qa_flag' => 't',
					'auto_publish' => 'f',
				);
			}else if($data_source_key=='ALL_DS_ORCA'){
				$dataSource = array(
					'data_source_key' => 'ALL_DS_ORCA',
					'qa_flag' => 't',
					'auto_publish' => 't',
					'title' => 'All Data Sources'
				);
			}
		}

		else
		{
			$dataSource = array_pop($dataSource);
		}

		displayMMRDataSourceSwitcher($dataSources, $data_source_key);
	}

}

/*
 * function that I used (Minh)
 */

function displayMMRDataSourceSwitcher(array $dataSources = array(), $selected_key = '')
{
	if (userIsORCA_ADMIN())
	{
		$dataSources[] = array('data_source_key'=>'PUBLISH_MY_DATA', 'title'=>'Publish My Data (ORCA Admin View)');
		$dataSources[] = array('data_source_key'=>'ALL_DS_ORCA', 'title'=>'All Data Sources (ORCA Admin View)');
	}

	?>



		<form id="data_source_history_form" name="data_source_history_form" action="my_tags.php" method="get">
			<div id="select_ds_container">
				<?php if ($selected_key == ''):?>
					<div class="content_block">Select the Data Source you wish to manage:</div>
				<?php else:?>
					<div class="content_block">Managing My Tags for:</div>
				<?php endif;?>
				<div class="content_block">
					<select data-placeholder="Choose a Datasource" name="data_source" id="data_source" style="width:300px;" onchange="this.form.submit();" class="chzn-select" tab-index="2">
					<option value=""></option>
					<?php
						// Present the results.
						for( $i=0; $i < count($dataSources); $i++ ){
							$dataSourceKey = $dataSources[$i]['data_source_key'];
							$dataSourceTitle = $dataSources[$i]['title'];
							print("<option value=\"".$dataSourceKey."\"" . ($selected_key == $dataSourceKey ? " selected" : "").">".esc($dataSourceTitle)."</option>\n");
						}

					?>
					</select>
				</div>
				<div class="content_block" style="float:right;margin-top:-14px;">
					<a href="http://ands.org.au/resource/mmr-help-r8.pdf" id="cpgHelpButton" target="_blank"></a>
				</div>
			</div>

			<div class="clearfix"></div>


			<div id="mmr_datasource_information" class="hide">

			 <a href="" id="mmr_information_hide">Hide Information</a>
			</div>

		</form>

		<?php
}