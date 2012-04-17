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

//echo $solr_url;

$dataSourceKey = getQueryValue('ds');
$view = getQueryValue('view');
$status = getQueryValue('status');

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$rp = isset($_POST['rp']) ? $_POST['rp'] : 20;
$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : 'date_modified';
$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'desc';
$query = isset($_POST['query']) ? $_POST['query'] : false;
$qtype = isset($_POST['qtype']) ? $_POST['qtype'] : false;

switch($view){
	case "all": searchAllRecords();break;
	case "summary": summary();break;
	case "status":searchRecords($status);break;
	case "allKeys":allKeys($status);break;
}


function searchRecords($status){
	header("Content-type: application/json");

	global $dataSourceKey,$solr_url,$rp,$page,$sortname,$sortorder,$query, $qtype;
	
	/**
	Setting Up Variables
	**/
	$sort='';
	if($sortname!='undefined'){
		if($sortorder!='undefined'){
			$sort=''.$sortname.' '.$sortorder;
		}
	}
	$start = 0;
	if($page!=1) $start = ($page - 1) * $rp;

	$q = '+data_source_key:("'.$dataSourceKey.'") +status:("'.$status.'")';

	if($query){
		if($qtype!='key'){
			$q.='+'.$qtype.':("'.$query.'")';
		}else{
			$q.='+'.$qtype.':('.$query.')';
		}
	}

	//Construct the Query
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>$start,'rows'=>$rp, 'wt'=>'json',
		'fl'=>'*,score'
	);
	$fields['sort']=$sort;

	//Call SOLR and ask for data
	$content = solr($solr_url, $fields);


	//Decode data from SOLR, put them into encoded array and send back to flexigrid
	$json = json_decode($content);
	$numFound = $json->{'response'}->{'numFound'};
	$jsonData = array('page'=>$page,'total'=>$numFound,'rows'=>array());




	foreach($json->{'response'}->{'docs'} as $doc){

		/**
		Logic on buttons
		**/


		$buttons = array();
		$status = $doc->{'status'};

		$view_link = eAPP_ROOT.'orca/view.php?key='.esc(rawurlencode($doc->{'key'}));


		if(!in_array($status, array('PUBLISHED', 'APPROVED'))){//IS DRAFT, edit needs DS
			if(in_array($status, array('DRAFT', 'MORE_WORK_REQUIRED'))){//DRAFT and MORE_WORK_REQUIRED
				array_push($buttons,'ReadOnlyView');

				//additional logic for feed_type='harvest' here TODO
				//if harvest then alert else it's ok
				array_push($buttons,'EditRecord');
				array_push($buttons,'DeleteRecord');

				$view_link = eAPP_ROOT.'orca/manage/add_'.$doc->{'class'}.'_registry_object.php?readOnly&data_source='.rawurlencode($doc->{'data_source_key'}).'&key='.esc(rawurlencode($doc->{'key'}));
			}else{//SUBMITTED_FOR_ASSESSMENT and ASSESSMENT_IN_PROGRESS
				//if feed = harvest, readonly mode in view TODO
				array_push($buttons,'ReadOnlyView');
				$view_link = eAPP_ROOT.'orca/manage/add_'.$doc->{'class'}.'_registry_object.php?readOnly&data_source='.rawurlencode($doc->{'data_source_key'}).'&key='.esc(rawurlencode($doc->{'key'}));
				//cannot edit nor delete, disabled button?
			}	

		}else{//PUBLISHED and APPROVED
			array_push($buttons,'ViewRecord');
			//if feed = harvest, readonly mode in edit TODO
			array_push($buttons,'EditRecord');
			array_push($buttons,'DeleteRecord');
		}

		


		$btnStr='';$btnStrStr='';
		for($i=0;$i<sizeof($buttons);$i++){
			if(sizeof($buttons)==1){//has only 1 item, just icon
				if($buttons[$i]=='ReadOnlyView'){//the ONLY case, where the only option is to view it

					$btnStr.='<a href="'.$view_link.'" class="smallIcon icon6s tip" tip="View This Record in Read Only Mode"><span></span></a>';

				}
			}else{
				//button positioning
				$pos = '';
				if($i==0){
					$pos=' left ';
				}elseif($i==sizeof($buttons)-1){
					$pos=' right ';
				}else $pos=' middle ';


				//button string
				switch($buttons[$i]){
					case 'ReadOnlyView':

						$btnStr.='<a href="'.$view_link.'" class="smallIcon icon6s '.$pos.' tip" tip="View This Record in Read Only Mode"><span></span></a>';
						break;
					case 'ViewRecord':
						$btnStr.='<a href="'.$view_link.'" class="smallIcon icon6s '.$pos.' tip" tip="View This Record"><span></span></a>';

						break;
					case 'EditRecord':
						$btnStr.='<a href="'.eAPP_ROOT.'orca/manage/add_'.$doc->{'class'}.'_registry_object.php?data_source='.rawurlencode($doc->{'data_source_key'}).'&key='.esc(rawurlencode($doc->{'key'})).'" class="smallIcon icon187s '.$pos.' tip" tip="Edit This Record"><span></span></a>';
						break;
					case 'DeleteRecord':
						$btnStr.='<a href="'.eAPP_ROOT.'orca/manage/process_registry_object.php?task=delete&data_source='.rawurlencode($doc->{'data_source_key'}).'&key='.esc(rawurlencode($doc->{'key'})).'" class="smallIcon icon100s '.$pos.' tip deleteConfirm" tip="Delete This Record"><span></span></a>';
						break;
					
					default:$btnStr.='Unknown';
				}

			}
			$btnStrStr .=$buttons[$i];			
		}

		/*$viewButton ='<a href="#" class="smallIcon icon6s left tip" tip="View This Record In Read Only Mode"><span></span></a>';
		$buttons = $viewButton.'
					<a href="#" class="smallIcon icon187s middle tip" tip="Edit This Record"><span></span></a>
					<a href="#" class="smallIcon icon100s right tip" tip="Delete This Record"><span></span></a>';*/


		$flag = $doc->{'flag'};
		if($flag==0){
			$flagClass = 'icon28sOff';
		}else{
			$flagClass = 'icon28sOn';
		}
		$flagButton = '<a href="javascript:void(0);" class="smallIcon '.$flagClass.' tip flagToggle" tip="Flag This Record"><span></span></a>';



		$date_modified = $doc->{'date_modified'};
		if($date_modified = date_parse($doc->{'date_modified'})){
			$date_modified = date('g:i a, j M Y', strtotime($doc->{'date_modified'}));
		}

		$error_count = 'N/A';
		if(isset($doc->{'error_count'})){
			$error_count = $doc->{'error_count'};
		}
		
		$warning_count = 'N/A';
		if(isset($doc->{'warning_count'})){
			$warning_count = $doc->{'warning_count'};
		}
		
		$entry = array(
					'id' => $doc->{'key'},
					'cell' => array(

							'<a href="'.$view_link.'">'.$doc->{'key'}.'</a>',
							'<a href="'.$view_link.'">'.$doc->{'list_title'}.'</a>',

							$date_modified,
							$doc->{'class'},
							$error_count,
							$doc->{'quality_level'},
							$flagButton,
							$btnStr,
							$doc->{'status'}
							)
				);
		$jsonData['rows'][] = $entry;
	}

	echo json_encode($jsonData);
}




/**
RETURN A LIST OF KEYS
**/
function allKeys($status){
	global $dataSourceKey, $solr_url;
	$q = '+data_source_key:("'.$dataSourceKey.'") +status:("'.$status.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'200', 'wt'=>'json',
		'fl'=>'key'
	);
	$content = solr($solr_url, $fields);
	echo $content;
}

function summary(){
	global $dataSourceKey;
	global $solr_url;
	global $rp;

	//SEARCH for everything based on that data source, but we only want 1 rows and return just the key, for optimized performace
	$q = 'data_source_key:("'.$dataSourceKey.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key'
	);
	$fields_string='';
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }//build the string
	rtrim($fields_string,'&');

	//because what we are looking for is the facet of the statuses
	$fields_string .='&facet=true&facet.field=status&facet.limit=-1&facet.mincount=1';

	//execute and return the stuffs in weird JSON
	$content = executeSOLR($solr_url, $fields, $fields_string);
	$json = json_decode($content);

	//We are now extracting the valuable data out from json and put them into an array
	//of form $status['DRAFT'] => 4 pr something
	$i=0;$status=array();$placeholder = $json->{'facet_counts'}->{'facet_fields'}->{'status'};
	for($i=0;$i<sizeof($placeholder);$i+=2){
		$status[$placeholder[$i]] = $placeholder[$i+1];
	}
	
	//build the html return table
	$str = '';

	$str .='<table>';
	$str .='<th><td>Count</td></th>';
	$str .='<tbody>';
	foreach($status as $key=>$value){
		$str.= '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
	}
	$str .='</tbody></table>';

	echo $str;
	/*echo '<hr/>';
	//var_dump($status);
	var_dump($json->{'facet_counts'}->{'facet_fields'});*/
}

function executeSOLR($solr_url, $fields, $fields_string){
	$ch = curl_init();
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
	curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	$content = curl_exec($ch);//execute the curl
	return $content;
}

?>