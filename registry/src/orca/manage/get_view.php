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
$key = getQueryValue('key');

$manual_publish = getQueryValue('manual_publish');
$ds_qa_flag = getQueryValue('ds_qa_flag');

if($ds_qa_flag=='false')$ds_qa_flag=false;
if($manual_publish=='false')$manual_publish=false;

if($dataSourceKey == 'ALL_DS_ORCA')
{
	$ds_qa_flag = true;	
	$manual_publish = true;	
}


$result = array();
if($ds_qa_flag){
	if($manual_publish){
		$result = array('PUBLISHED'=>0, 'APPROVED'=>0,  'SUBMITTED_FOR_ASSESSMENT'=>0, 'ASSESSMENT_IN_PROGRESS'=>0,'DRAFT'=>0, 'MORE_WORK_REQUIRED'=>0);
	}else{//auto publish
		$result = array('PUBLISHED'=>0,  'SUBMITTED_FOR_ASSESSMENT'=>0, 'ASSESSMENT_IN_PROGRESS'=>0,'DRAFT'=>0, 'MORE_WORK_REQUIRED'=>0);
	}
}else{
	if($manual_publish){
		$result = array('PUBLISHED'=>0, 'APPROVED'=>0, 'DRAFT'=>0);
	}else{//auto publish
		$result = array('PUBLISHED'=>0, 'DRAFT'=>0);
	}
}

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$rp = isset($_POST['rp']) ? $_POST['rp'] : 20;
$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : 'date_modified';
$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'desc';
$query = isset($_POST['query']) ? $_POST['query'] : false;
$qtype = isset($_POST['qtype']) ? $_POST['qtype'] : false;



switch($view){
	case "all": searchAllRecords();break;
	case "summary": summary($dataSourceKey);break;
	case "AllStatus": allStatus($dataSourceKey);break;
	case "status_table":searchRecords($status);break;
	case "as_qa_table":searchRecords($status);break;
	case "qa_table":searchRecords($status);break;
	case "allKeys":allKeys($status);break;
	case "statusCount": statusCount($status);break;
	case "AllStatusAllQA": AllStatusAllQA($dataSourceKey);break;
	case "StatusAllQA":StatusAllQA($status, $dataSourceKey);break;
	case "tipQA": tipQA($key, $ql);break;
	case "tipError": tipError($key, $dataSourceKey);break;
	case "getAllStat": getAllStat();break;
	case "getSummary": getSummary();break;
}

function summary($dataSourceKey){
	global $solr_url, $ds_qa_flag, $manual_publish, $result;

	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'")';
	}else{
		$q = '*:*';
	}

	//Construct the Query
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'class','facet.mincount'=>'1','facet.sort'=>'index'
	);
	//$fields['facet']=$sort;

	//Call SOLR and ask for data
	$classes = solr($solr_url, $fields);
	$classes_json = json_decode($classes);

	$numFound = $classes_json->{'response'}->{'numFound'};
	$classes_json = $classes_json->{'facet_counts'}->{'facet_fields'}->{'class'};
	$classes = array();
	for($i=0;$i<sizeof($classes_json);$i=$i+2){
		$classes[$classes_json[$i]] = $classes_json[$i+1];
	}
	//var_dump($classes);

	$jsonData = array('page'=>'1','total'=>$numFound,'rows'=>array());


	$statuses = getAllStatus($dataSourceKey);
	//$result = array('PUBLISHED'=>0, 'APPROVED'=>0, 'ASSESSMENT_IN_PROGRESS'=>0, 'SUBMITTED_FOR_ASSESSMENT'=>0, 'DRAFT'=>0, 'MORE_WORK_REQUIRED'=>0);



	foreach($statuses as $status=>$num){
		foreach($result as $index=>$r){
			if($index==$status) $result[$index] = $num;
		}
	}
	//var_dump($result);

	//var_dump($statuses);
	foreach($classes as $class=>$num){
		$entry = array(
					'id' => ucfirst($class),
					'cell' => array(ucfirst($class))
				);
		foreach($result as $status=>$num){
			$entry['cell'][] = getStatusCountForClass($status, $class, $dataSourceKey);
		}
		$jsonData['rows'][] = $entry;
	}

	$jsonData = json_encode($jsonData);
	echo $jsonData;
}

function AllStatus($dataSourceKey){
	header("Content-type: application/json; charset=UTF-8");
	global $solr_url, $ds_qa_flag, $manual_publish, $result;
	$statuses = getAllStatus($dataSourceKey);
	//var_dump($statuses);

	foreach($statuses as $status=>$num){
		foreach($result as $index=>$r){
			if($index==$status) $result[$index] = $num;
		}
	}
	//var_dump($result);
	$result = json_encode($result);
	echo $result;
}

function getStatusCountForClass($status, $class, $dataSourceKey){
	global $solr_url;
	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'")';
	}else{
		$q = '*:*';
	}
	$q.=' +status:("'.$status.'") +class:("'.$class.'")';
	//Construct the Query
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key'
	);
	//$fields['facet']='&facet=true&facet.field=status';

	//Call SOLR and ask for data
	$content = solr($solr_url, $fields);
	$json = json_decode($content);
	$numFound = $json->{'response'}->{'numFound'};
	return $numFound;
}

function getAllStatus($dataSourceKey){
	global $solr_url;

	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'")';
	}else{
		$q = '*:*';
	}

	//Construct the Query
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'status','facet.mincount'=>'1','facet.sort'=>'index'
	);
	//$fields['facet']='&facet=true&facet.field=status';

	//Call SOLR and ask for data
	$content = solr($solr_url, $fields);
	$json = json_decode($content);
	$statuses_json = $json->{'facet_counts'}->{'facet_fields'}->{'status'};
	$statuses = array();
	for($i=0;$i<sizeof($statuses_json);$i=$i+2){
		$statuses[$statuses_json[$i]] = $statuses_json[$i+1];
	}
	return $statuses;
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

	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'")'.$add ;
	}else{
		$q = $add;
	}

	//echo $q;
	if($query){
		if($qtype!='key'){//not key
			$q .= "($qtype:($query) OR $qtype:($query*) OR $qtype:($query~)) ";
		}else{//key search
			$q .= "($qtype:($query) OR $qtype:($query*) OR $qtype:($query~)) ";
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


		$draftStatus = '';
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
			$draftStatus = 'draft';
		}else{//PUBLISHED and APPROVED
			array_push($buttons,'ViewRecord');
			//if feed = harvest, readonly mode in edit TODO
			array_push($buttons,'EditRecord');
			array_push($buttons,'DeleteRecord');
			$draftStatus = 'not-draft';
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
						$btnStr.='<a href="javascript:;" class="smallIcon icon100s '.$pos.' tip deleteConfirm" tip="Delete This Record" key="'.rawurlencode($doc->{'key'}).'" ds="'.rawurlencode($doc->{'data_source_key'}).'" draftStatus="'.$draftStatus.'"><span></span></a>';
						//$btnStr.='<a href="'.eAPP_ROOT.'orca/manage/process_registry_object.php?task=delete&data_source='.rawurlencode($doc->{'data_source_key'}).'&key='.esc(rawurlencode($doc->{'key'})).'" class="smallIcon icon100s '.$pos.' tip deleteConfirm" tip="Delete This Record"><span></span></a>';
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
			if(isset($doc->{'last_modified_by'})){
				$date_modified .= '<br/><small>'.$doc->{'last_modified_by'}.'</small>';
			}
		}

		$error_count = 'N/A';
		if(isset($doc->{'error_count'})){
			$error_count = $doc->{'error_count'};
		}

		if($error_count==0){
			$error_count = '';
		}else{
			$error_count = '<span class="hide">'.$doc->{'error_count'}.'</span><img src="'.eAPP_ROOT.'orca/_images/error_icon.png" key="'.$doc->{'key'}.'" dsKey="'.$doc->{'data_source_key'}.'" status="'.$doc->{'status'}.'" class="tipError"/>';
		}

		$warning_count = 'N/A';
		if(isset($doc->{'warning_count'})){
			$warning_count = $doc->{'warning_count'};
		}


		$qualityLevelStr = '<center><a href="javascript:;" dsKey="'.$doc->{'data_source_key'}.'" status="'.$doc->{'status'}.'" level="'.$doc->{'quality_level'}.'" key="'.$doc->{'key'}.'" class="smallIcon tipQA ql'.$doc->{'quality_level'}.'" style="float:none; width:24px; padding:4px 2px 2px 3px;">'.$doc->{'quality_level'}.'<span></span></a></center>';


		$goldFlag = '';
		if(isset($doc->{'gold_status_flag'}) && ($doc->{'gold_status_flag'}==1)){
			//$goldFlag = '<a href="javascript:void(0);" class="smallIcon icon28sOn tip borderless" tip="Gold Standard" style="float:right"><span></span></a>';
			$qualityLevelStr = '<center><a href="javascript:;" dsKey="'.$doc->{'data_source_key'}.'" status="'.$doc->{'status'}.'" level="'.$doc->{'quality_level'}.'" key="'.$doc->{'key'}.'" class="smallIcon tipQA icon28sOn borderless" style="float:none; width:20px;"><span></span></a></center>';
		}

		//manually assessed
		$manually_assessed_flag = '';
		if(isset($doc->{'manually_assessed_flag'})){
			if($doc->{'manually_assessed_flag'}=='1'){
				$manually_assessed_flag = 'yes';
			}else $manually_assessed_flag = 'no';
		}else{
			$manually_assessed_flag = 'no';
		}

		$entry = array(
					'id' => $doc->{'key'},
					'cell' => array(
							'<img class="check_box_img" src="'.eAPP_ROOT.'orca/_images/checkbox_no.png"/>',
							'<a href="'.$view_link.'" class="tip" tip="'.$doc->{'key'}.'">'.$doc->{'key'}.'</a>',
							$doc->{'list_title'},

							$date_modified,
							ucfirst($doc->{'class'}),
							$error_count,
							$qualityLevelStr,
							$flagButton,
							$btnStr,
							$doc->{'status'},
							ucfirst($doc->{'feed_type'}),
							ucfirst($manually_assessed_flag)
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
	header("Content-type: application/json; charset=UTF-8");
	global $dataSourceKey, $solr_url;
	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'")';
	}else{
		$q = '';
	}
	//$q = '+data_source_key:("'.$dataSourceKey.'")';
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

function StatusAllQA($status, $dataSourceKey){
	header("Content-type: application/json; charset=UTF-8");
	global $dataSourceKey, $solr_url;
	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'")';
	}else{
		$q = '*:*';
	}
	if($status!='All') $q.='+status:("'.$status.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'class','facet.mincount'=>'1','facet.sort'=>'index'
	);
	//Call SOLR and ask for data
	$status_result = solr($solr_url, $fields);

	$status_result = json_decode($status_result);
	//var_dump($status_result);

	$classes = $status_result->{'facet_counts'}->{'facet_fields'}->{'class'};

	$result = array();
	for($i=0;$i<sizeof($classes)-1;$i=$i+2){
		$c = $classes[$i];
		$c_num = $classes[$i+1];
		$class_qa = getQAforClass($dataSourceKey, $c, $status);
		//var_dump($class_qa);
		$class_qa_array = array();
		for($j=0;$j<sizeof($class_qa)-1;$j=$j+2){
			$class_qa_array[$class_qa[$j]]=$class_qa[$j+1];

			//if result >

		}
		$result[$c]['label']=ucfirst($c);
		$result[$c]['num']=$c_num;

		for($j=0;$j<=4;$j++){
			if(!isset($class_qa_array[$j]))$class_qa_array[$j]=0;
		}
		ksort($class_qa_array);
		//$class_qa_array = json_encode($class_qa_array);
		$result[$c]['qa']=$class_qa_array;
	}
	//echo '<hr/>';
	//var_dump($result);
	$result = json_encode($result);
	echo $result;
}


function getQAforClass($dataSourceKey, $class, $status='All'){
	global $dataSourceKey, $solr_url;
	if($dataSourceKey!='ALL_DS_ORCA'){
		$q = '+data_source_key:("'.$dataSourceKey.'") +class:("'.$class.'")';
	}else{
		$q = '+class:("'.$class.'")';
	}
	if($status!='All') $q.=' +status:("'.$status.'")';
	$fields = array(
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
		'fl'=>'key', 'facet'=>'true', 'facet.field'=>'quality_level','facet.mincount'=>'0','facet.sort'=>'index'
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
		'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'20000', 'wt'=>'json',
		'fl'=>'key, error_count'
	);
	$content = solr($solr_url, $fields);
	echo $content;
}

function tipQA($key, $level){
	global $dataSourceKey, $status;
	// Don't show QA for Gold Standard records
	if ($level == 5){
		$t = '<div class="qa_container success" qld="5"><div class="">The following record has been verified as an exemplary record by the ANDS Metadata Assessment Group.</div></div>';
	}
	else{
		$t = getQualityTestResult($key, $dataSourceKey, $status);
	}
	echo $t;
	return;
	//echo 'getting qa for key='.$key.' and level='.$level.'<a href="">asdfadsfasd</a>';
}

function tipError($key, $dataSourceKey){
	global $status;
	echo getQualityTestResult($key, $dataSourceKey, $status, false);
}

function getAllStat(){
	header("Content-type: application/json; charset=UTF-8");
	global $dataSourceKey;
	global $status;
	$result = array();
	if($dataSourceKey=='ALL_DS_ORCA') $dataSourceKey='';
	$stats = getDataSourceStats($dataSourceKey, $status);
    if($stats)
    {
	$qa_levels = array();
	foreach($stats as $item){
		//foreach($stat_item as $item){
			//var_dump($item);
			$ds_key = $item['ds_key'];
			$ds_title = getDataSourceTitle($ds_key);
			$title = $ds_title[0]['title'];
			$ro_class = $item['ro_class'];
			if($ro_class == 'Collection')
				$ro_class = "Collections";
			elseif($ro_class == 'Party')
				$ro_class = "Parties";
			elseif($ro_class == 'Activity')
				$ro_class = "Activities";
			elseif($ro_class == 'Service')
				$ro_class = "Services";
			$ro_status = $item['status'];
			$qa_level = $item['qa_level'];
			if(!in_array($qa_level, $qa_levels)){
				array_push($qa_levels, intval($qa_level));
				asort($qa_levels);
			}
			
			$count = $item['count'];
			if($dataSourceKey==''){
				if(isset($result[$title][$qa_level])){
					$result[$title][$qa_level] += $count;
				}else{
					$result[$title][$qa_level] = $count;
				}
			}else{
				if(isset($result[$ro_class][$qa_level])){
					$result[$ro_class][$qa_level] += $count;
				}else{
					$result[$ro_class][$qa_level] = $count;
				}
			}
		//}
	}
	/*
	 * $result['columns'] = ['dskey', 'qa1, 'qa2', qa3']
	 * $result['rows] = ['uni1', '1', '23', '1']
	 * $result['rows] = ['uni2', '0', '12', '0']
	 */
	
	//var_dump($qa_levels);
	
	
	$rows = array();
	foreach($result as $class=>$qa){
		$qa_array = array();
		array_push($qa_array, $class);
		foreach($qa_levels as $qa_level){
			if(isset($result[$class][$qa_level])){
				array_push($qa_array, intval($result[$class][$qa_level]));
			}else{
				array_push($qa_array, 0);
			}
		}
		array_push($rows, $qa_array);
	}
	
	
	$qa_levels = array_reverse($qa_levels);
	if($dataSourceKey!=''){
		array_push($qa_levels, 'class');
	}else array_push($qa_levels, 'data source title');
	$qa_levels = array_reverse($qa_levels);
	
	$columns = $qa_levels;
	
	//var_dump($columns);
	//var_dump($rows);
	
	$real_result['columns']=$columns;
	$real_result['rows']= $rows;
	
	$real_result = json_encode($real_result);
	echo $real_result;
    }
    else {
    	return '';
    }
}

function getSummary()
{
	header("Content-type: application/json; charset=UTF-8");
	global $dataSourceKey;
	global $status;
	global $result;
	if($dataSourceKey=='ALL_DS_ORCA'){
		$dataSourceKey='';
		$result = array('Collections'=>0, 'Parties'=>0, 'Activities'=>0, 'Services'=>0);
	}
	$stats = getDataSourceSummary($dataSourceKey, $status);
	
	
	//var_dump($stats);
	$statColumns = array();
	$total = 0;
	if($stats)
	{

	foreach($stats as $item){
		//foreach($stat_item as $item){
			//var_dump($item);
			$ds_key = $item['ds_key'];
			$ds_title = getDataSourceTitle($ds_key);
			$title = $ds_title[0]['title'];
			$ro_class = $item['ro_class'];
			if($ro_class == 'Collection')
				$ro_class = "Collections";
			elseif($ro_class == 'Party')
				$ro_class = "Parties";
			elseif($ro_class == 'Activity')
				$ro_class = "Activities";
			elseif($ro_class == 'Service')
				$ro_class = "Services";		
			$status = $item['status'];
			$count = $item['count'];
			$total += $count;
			if($dataSourceKey==''){
				if(isset($statResult[$title])){
					$statResult[$title][$ro_class] += $count;
				}
				else{
					$statResult[$title] = $result;
					$statResult[$title][$ro_class] = $count;
				}
			}else{
				if(isset($statResult[$ro_class])){
					$statResult[$ro_class][$status] += $count;
				}else{
					$statResult[$ro_class] = $result;
					$statResult[$ro_class][$status] = $count;
				}
			}
		}	
		$jsonData = array('page'=>'1','total'=>$total,'rows'=>array());
		ksort($statResult);
		foreach($statResult as $title=>$array){
	
			$entry = array(
						'id' => ucfirst($title),
						'cell' => array(ucfirst($title))
					);
			
			foreach($array as $a){
				$entry['cell'][] = $a;
			}
					
			
			$jsonData['rows'][] = $entry;
			//var_dump($array);
			
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	else
	{
		$jsonData = array('page'=>'1','total'=>$total,'rows'=>array());
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
}
?>