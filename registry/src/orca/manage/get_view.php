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
$ql = getQueryValue('ql');

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$rp = isset($_POST['rp']) ? $_POST['rp'] : 20;
$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : 'date_modified';
$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'desc';
$query = isset($_POST['query']) ? $_POST['query'] : false;
$qtype = isset($_POST['qtype']) ? $_POST['qtype'] : false;

switch($view){
	case "all": searchAllRecords();break;
	case "summary": summary();break;
	case "status_table":searchRecords($status);break;
	case "as_qa_table":searchRecords($status);break;
	case "qa_table":searchRecords($status);break;
	case "allKeys":allKeys($status);break;
	case "statusCount": statusCount($status);break;
	case "AllStatusAllQA": AllStatusAllQA($dataSourceKey);break;
}


function searchRecords($status){
	header("Content-type: application/json");

	global $dataSourceKey,$solr_url,$rp,$page,$sortname,$sortorder,$query, $qtype, $view, $ql;
	

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

	$add = '+status:("'.$status.'")';
	if($view=='status_table'){
		$add = '+status:("'.$status.'")';
	}elseif($view=='as_qa_table'){
		$add = '+quality_level:('.$ql.')';
	}elseif($view=='qa_table'){
		$add = '+status:("'.$status.'")'. ' +quality_level:('.$ql.')';
	}

	$q = '+data_source_key:("'.$dataSourceKey.'")'.$add ;


	//echo $q;
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

	//echo $content;

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
			$flagClass = 'icon59sOff';
		}else{
			$flagClass = 'icon59sOn';
		}
		$flagButton = '<a href="javascript:void(0);" class="smallIcon '.$flagClass.' tip flagToggle borderless" tip="Flag This Record"><span></span></a>';



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


		$qualityLevelStr = '<a href="javascript:;" class="smallIcon tip ql'.$doc->{'quality_level'}.'">'.$doc->{'quality_level'}.'<span></span></a>';


		$goldFlag = '';
		if(isset($doc->{'gold_status_flag'}) && ($doc->{'gold_status_flag'}==1)){
			$goldFlag = '<a href="javascript:void(0);" class="smallIcon icon28sOn tip borderless" tip="Gold Standard" style="float:right"><span></span></a>';
		}
		
		$entry = array(
					'id' => $doc->{'key'},
					'cell' => array(

							'<a href="'.$view_link.'">'.$doc->{'key'}.'</a>',
							$goldFlag.' '.$doc->{'list_title'},

							$date_modified,
							$doc->{'class'},
							$error_count,
							$qualityLevelStr,
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
STATUS COUNT
**/
function statusCount($status){
	header("Content-type: application/javascript");
	global $dataSourceKey, $solr_url;
	$q = '+data_source_key:("'.$dataSourceKey.'")';
	if($status!='All'){
		$q.='+status:("'.$status.'")';
	}
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1000', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'quality_level','facet.mincount'=>'1','facet.sort'=>'index'
	);
	//Call SOLR and ask for data
	$content = solr($solr_url, $fields);
	echo $content;
}

function AllStatusAllQA($dataSourceKey){
	header("Content-type: application/javascript");
	global $dataSourceKey, $solr_url;
	$q = '+data_source_key:("'.$dataSourceKey.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'class','facet.mincount'=>'1','facet.sort'=>'index'
	);
	//Call SOLR and ask for data
	$status_result = solr($solr_url, $fields);

	$status_result = json_decode($status_result);

	$statuses = $status_result->{'facet_counts'}->{'facet_fields'}->{'class'};

	$result = array();
	for($i=0;$i<sizeof($statuses)-1;$i=$i+2){
		$s = $statuses[$i];
		$s_num = $statuses[$i+1];
		$status_qa = getQAforClass($dataSourceKey, $s);
		$status_qa_array = array();
		for($j=0;$j<sizeof($status_qa)-1;$j=$j+2){
			$status_qa_array[$status_qa[$j]]=$status_qa[$j+1];
		}
		$result[$s]['label']=$s;
		$result[$s]['num']=$s_num;
		$result[$s]['qa']=$status_qa_array;
	}
	$result = json_encode($result);
	echo $result;

}

function getQAforClass($dataSourceKey, $class){
	global $dataSourceKey, $solr_url;
	$q = '+data_source_key:("'.$dataSourceKey.'") +class:("'.$class.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'quality_level','facet.mincount'=>'1','facet.sort'=>'index'
	);
	//Call SOLR and ask for data

	//echo $q;
	$qa_result = json_decode(solr($solr_url, $fields));

	return $qa_result->{'facet_counts'}->{'facet_fields'}->{'quality_level'};
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



?>