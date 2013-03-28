<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Core Data Source controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Data_source extends MX_Controller {

	/**
	 * Manage My Datasources (MMR version for Data sources)
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 
	 * @todo ACL on which data source you have access to
	 * @return [HTML] output
	 */
	
	public function index(){
		//$this->output->enable_profiler(TRUE);
		acl_enforce('REGISTRY_USER');
		$data['title'] = 'Manage My Datasources';
		$data['small_title'] = '';

		$this->load->model("data_sources","ds");
	 	$dataSources = $this->ds->getOwnedDataSources();//get everything  XXX: getOwnedDataSources
		//$dataSources = $this->ds->getOwnedDataSources();
		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		$data['scripts'] = array('data_sources');
		$data['js_lib'] = array('core', 'graph', 'datepicker','vocab_widget');

		$this->load->view("data_source_index", $data);
	}

	/**
	 * Same as index
	 */
	public function manage(){
		$this->index();
	}

	/**
	 * Sets the slugs for all datasources
	 * 
	 * 
	 * @author Liz Woods
	 * @param [
	 * @todo ACL on which data source you have access to, error handling
	 * @return 
	 */
	public function setDatasourceSlugs(){

		$this->load->model("data_sources","ds");
	 	$dataSources = $this->ds->getAll(0,0);//get everything  XXX: getOwnedDataSources
		foreach($dataSources as $ds){
			$ds->setSlug($ds->title);
			$ds->save();
		}	
		 	
	}

	/**
	 * Manage My Records (MMR Screen)
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param data_source_id | optional
	 * @return [HTML] output
	 */
	public function manage_records($data_source_id=false){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);
		$data['title'] = 'Manage My Records';
		$this->load->model('data_source/data_sources', 'ds');
		if($data_source_id){
			$data_source = $this->ds->getByID($data_source_id);
			if(!$data_source) show_error("Unable to retrieve data source id = ".$data_source_id, 404);
			$data_source->updateStats();//TODO: XXX
			$data['ds'] = $data_source;
		}else{
			throw new Exception("Data Source must be provided");
		}
		$data['less']=array('mmr');
		$data['scripts'] = array('mmr');
		$data['js_lib'] = array('core');
		$this->load->view("manage_my_record", $data);
	}

	public function manage_deleted_records($data_source_id=false, $offset=0, $limit=10){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);
		$data['title'] = 'Manage Deleted Records';
		$data['scripts'] = array('ds_history');
		$data['js_lib'] = array('core','prettyprint');

		$this->load->model("data_source/data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		$deletedRecords = array();
		$data['ds'] = $this->ds->getByID($data_source_id);
		$ids = $this->ro->getDeletedRegistryObjects($data_source_id);
		$data['record_count'] = sizeof($ids);
		if(sizeof($ids) > 0){
			
			foreach($ids as $idx=>$ro){
				try{
					$deletedRecords[$ro['key']][$idx] = array('title'=>$ro['title'],'key'=>$ro['key'],'id'=>$ro['id'],'record_data'=>wrapRegistryObjects($ro['record_data']), 'deleted_date'=>timeAgo($ro['deleted']));
				}catch(Exception $e){
					throw Exception($e);
				}
				if($idx % 100 == 0){
					unset($ro);
					gc_collect_cycles();
				}
			}
		}
		$data['record_count'] = sizeof($deletedRecords);
		$data['deleted_records'] = array_slice($deletedRecords, $offset, $limit);
		$data['offset'] = $offset;
		$data['limit'] = $limit;
		$this->load->view('manage_deleted_records', $data);
	}



	/**
	 * Get MMR AJAX data for MMR
	 *
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  [int] 	$data_source_id
	 * @return [json]   
	 */
	public function get_mmr_data($data_source_id){

		//administrative and loading stuffs
		acl_enforce('REGISTRY_USER');
		// ds_acl_enforce($data_source_id);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		//getting the data source and parse into the jsondata array
		$data_source = $this->ds->getByID($data_source_id);
		foreach($data_source->attributes as $attrib=>$value){
			$jsonData['ds'][$attrib] = $value->value;
		}

		//QA and Auto Publish check, valid_statuses are populated accordingly
		$qa = $data_source->qa_flag=='t' ? true : false;
		$auto_published = $data_source->auto_published=='t' ? true: false;
		$jsonData['valid_statuses'] = array('MORE_WORK_REQUIRED', 'DRAFT', 'PUBLISHED');
		if($qa) {
			array_push($jsonData['valid_statuses'], 'SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS');
		}
		if(!$auto_published){
			array_push($jsonData['valid_statuses'], 'APPROVED');	
		}

		$filters = $this->input->post('filters');
		if(isset($filters['filter']['status'])) $jsonData['valid_statuses'] = array($filters['filter']['status']);

		//statuses is the main result array
		$jsonData['statuses'] = array();
		foreach($jsonData['valid_statuses'] as $s){

			//declarations
		
			$args = array();//array for filtering
			$no_match = false; //check match on filter 
			
			$st = array('display_name'=>str_replace('_', ' ', $s), 'name'=>$s, 'menu'=>array());
			array_push($st['menu'], array('action'=>'select_all', 'display'=>'Select All'));
			array_push($st['menu'], array('action'=>'select', 'display'=>'Select'));
			array_push($st['menu'], array('action'=>'view', 'display'=>'<i class="icon icon-eye-open"></i> View this Registry Object'));
			array_push($st['menu'], array('action'=>'edit', 'display'=>'<i class="icon icon-edit"></i> Edit this Registry Object'));
			array_push($st['menu'], array('action'=>'flag', 'display'=>'Flag'));
			array_push($st['menu'], array('action'=>'set_gold_status_flag', 'display'=>'Gold Standard'));
			switch($s){
				case 'DRAFT':
					$st['ds_count']=$data_source->count_DRAFT;
					if($qa){
						$st['connectTo']='SUBMITTED_FOR_ASSESSMENT';
						array_push($st['menu'], array('action'=>'to_submit', 'display'=>'Submit for Assessment'));
					}else{
						if(!$auto_published){
							$st['connectTo']='APPROVED';
							array_push($st['menu'], array('action'=>'to_approve', 'display'=>'Approve'));
						}else{
							$st['connectTo']='PUBLISHED';
							array_push($st['menu'], array('action'=>'to_publish', 'display'=>'Publish'));
						}
					}
					break;
				case 'MORE_WORK_REQUIRED':
					$st['ds_count']=$data_source->count_MORE_WORK_REQUIRED;
					$st['connectTo']='DRAFT';
					array_push($st['menu'], array('action'=>'to_draft', 'display'=>'Move to Draft'));
					break;
				case 'SUBMITTED_FOR_ASSESSMENT':
					$st['ds_count']=$data_source->count_SUBMITTED_FOR_ASSESSMENT;
					$st['connectTo']='ASSESSMENT_IN_PROGRESS';
					array_push($st['menu'], array('action'=>'to_assess', 'display'=>'Asessment In Progress'));
					break;
				case 'ASSESSMENT_IN_PROGRESS':
					$st['ds_count']=$data_source->count_ASSESSMENT_IN_PROGRESS;
					$st['connectTo']='APPROVED';
					array_push($st['menu'], array('action'=>'to_approve', 'display'=>'Approve'));
					array_push($st['menu'], array('action'=>'to_moreworkrequired', 'display'=>'More Work Required'));
					break;
				case 'APPROVED':
					$st['ds_count']=$data_source->count_APPROVED;
					$st['connectTo']='PUBLISHED';
					array_push($st['menu'], array('action'=>'to_publish', 'display'=>'Publish'));
					break;
				case 'PUBLISHED':
					$st['ds_count']=$data_source->count_PUBLISHED;
					array_push($st['menu'], array('action'=>'to_draft', 'display'=>'Move to Draft'));
					$st['connectTo']='';
					break;
			}
			array_push($st['menu'], array('action'=>'delete', 'display'=>'Delete'));
			

			$args['sort'] = isset($filters['sort']) ? $filters['sort'] : array('updated'=>'desc');
			$args['search'] = isset($filters['search']) ? $filters['search'] : false;
			$args['or_filter'] = isset($filters['or_filter']) ? $filters['or_filter'] : false;
			$args['filter'] = array('status'=>$s);
			$args['filter'] = isset($filters['filter']) ? array_merge($filters['filter'], array('status'=>$s)) : array('status'=>$s);

			
			$offset = 0;
			$limit = 20;

			$st['offset'] = $offset+$limit;

			$filter = array(
				'ds_id'=>$data_source_id,
				'limit'=>20,
				'offset'=>0,
				'args'=>$args
			);
			$ros = $this->get_ros($filter);
			$st['items']=$ros['items'];
			$st['count']=$this->get_ros($filter, true);
			if($st['count']==0) $st['noResult']=true;
			$st['hasMore'] = $ros['hasMore'];
			$st['ds_id'] = $data_source_id;
			
			$jsonData['statuses'][$s] = $st;
		}
		$jsonData['filters'] = $filters;
		echo json_encode($jsonData);
	}

	public function get_more_mmr_data(){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($this->input->post('ds_id'));
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
			
		$args['filter'] = array('status'=>$this->input->post('status'));
		$filters = array(
			'ds_id'=>$this->input->post('ds_id'),
			'limit'=>10,
			'offset'=>$this->input->post('offset'),
			'args'=>$args
		);

		$results = $this->get_ros($filters, false);
		if($results){
			echo json_encode($results);
		}else echo json_encode(array('noMore'=>true));
	}

	private function get_ros($filters, $getCount=false){
		$results['items'] = array();
		$this->load->model('registry_object/registry_objects', 'ro');
		if(!$getCount){
			$ros = $this->ro->getByDataSourceID($filters['ds_id'], $filters['limit'], $filters['offset'], $filters['args'], false);
		}else{
			return sizeof($ros = $this->ro->getByDataSourceID($filters['ds_id'], 0, 0, $filters['args'], false));
		}
		if($ros){
			foreach($ros as $r){
				$registry_object = $this->ro->getByID($r['registry_object_id']);
				
				$item = array(
						'id'=>$registry_object->id, 
						'key'=>$registry_object->key,
						'title'=>$registry_object->title,
						'status'=>$registry_object->status,
						'class'=>$registry_object->class,
						'updated'=>timeAgo($registry_object->updated),
						'error_count'=>$registry_object->error_count,
						'warning_count'=>$registry_object->warning_count,
						'data_source_id'=>$registry_object->data_source_id,
						);
				if($item['error_count']>0) $item['has_error'] = true;
				if($registry_object->flag=='t') $item['has_flag'] = true;
				if($registry_object->gold_status_flag=='t'){
					$item['has_gold'] = true;
				}else{
					$item['quality_level'] = $registry_object->quality_level;
				}
				array_push($results['items'], $item);
			}
		}else return false;
		if(sizeof($ros)<$filters['limit']){
			$results['hasMore']=false;
		}else{
			$results['hasMore']=true;
		}
		return $results;
	}

	public function get_mmr_menu(){
		// header('Cache-Control: no-cache, must-revalidate');
		// header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		$data_source_id = $this->input->post('data_source_id');
		$status = $this->input->post('status');
		$selecting_status = $this->input->post('selecting_status') ? $this->input->post('selecting_status') : false;
		$affected_ids = $this->input->post('affected_ids') ? $this->input->post('affected_ids') : array();

		$data_source = $this->ds->getByID($data_source_id);


		if($selecting_status!=$status){
			$affected_ids=array();
		}

		$menu = array();
		if(sizeof($affected_ids) == 0){
			$menu['nothing'] = 'Select a Registry Object';
		}else if(sizeof($affected_ids) == 1){
			$menu['edit'] = 'Edit Registry Object';
			$menu['view'] = 'View Registry Object';
		}

		$hasFlag = false;
		$hasGold = false;
		foreach($affected_ids as $id){
			$ro = $this->ro->getByID($id);
			if($ro->flag=='t') $hasFlag = true;
			if($ro->gold_status_flag=='t') $hasGold = true;
		}

		if($hasFlag) $menu['un_flag'] = 'Remove Flag';
		if($hasGold) $menu['un_set_gold_status_flag'] = 'Remove Gold Status';

		//QA and Auto Publish check
		$qa = $data_source->qa_flag=='t' ? true : false;
		$auto_published = $data_source->auto_published=='t' ? true: false;

		if(sizeof($affected_ids)>=1){
			$menu['flag'] = 'Flag';
			switch($status){
				case 'DRAFT':
					if($qa){
						$menu['to_submit'] = 'Submit for Assessment';
					}else{
						if(!$auto_published){
							$menu['to_approve'] = 'Approve';
						}else{
							$menu['to_publish'] = 'Publish';
						}
					}
					break;
				case 'MORE_WORK_REQUIRED':
					$menu['to_draft'] = 'Move to Draft';
					break;
				case 'SUBMITTED_FOR_ASSESSMENT':
					$menu['to_assess'] = 'Assessment In Progress';
					break;
				case 'ASSESSMENT_IN_PROGRESS':
					$menu['to_approve'] = 'Approve';
					$menu['to_moreworkrequired'] = 'More Work Required';
					break;
				case 'APPROVED':
					$menu['to_publish'] = 'Publish';
					break;
				case 'PUBLISHED':
					$menu['to_draft'] = 'Move to Draft';
					$menu['set_gold_status_flag'] = 'Set Gold Status';
					break;
			}
			$menu['delete'] = 'Delete Registry Object';
		}



		$html = '';
		$html .='<ul class="nav nav-tabs nav-stacked">';
		foreach($menu as $action=>$display){
			$html .='<li><a tabindex="-1" href="javascript:;" class="op" action="'.$action.'" status="'.$status.'">'.$display.'</a></li>';
		}
		$html .='</ul>';
		echo $html;
		
	}

	/**
	 * Get a list of data sources
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] page
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] results of the search
	 */
	public function getDataSources($page=1){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");

		//Limit and Offset calculated based on the page
		$limit = 16;
		$offset = ($page-1) * $limit;

		//$dataSources = $this->ds->getAll($limit, $offset);
		$dataSources = $this->ds->getOwnedDataSources();

		$this->load->model("registry_object/registry_objects", "ro");

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;

			$item['counts'] = array();
			foreach ($this->ro->valid_status AS $status){
				if($ds->getAttribute("count_$status")>0){
					array_push($item['counts'], array('status' => $status, 'count' =>$ds->getAttribute("count_$status"), 'name'=>readable($status)));
				}
			}

			$item['qlcounts'] = array();
			foreach ($this->ro->valid_levels AS $level){
				array_push($item['qlcounts'], array('level' => $level, 'count' =>$ds->getAttribute("count_level_$level")));
			}

			$item['classcounts'] = array();
			foreach($this->ro->valid_classes as $class){
				if($ds->getAttribute("count_$class")>0)array_push($item['classcounts'], array('class' => $class, 'count' =>$ds->getAttribute("count_$class"),'name'=>readable($class)));
			}

			$item['key']=$ds->key;
			$item['record_owner']=$ds->record_owner;
			$item['notes']=$ds->notes;

			array_push($items, $item);
		}
		
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	/**
	 * Get a single data source
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] Data Source ID
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] of a single data source
	 */
	public function getDataSource($id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		$dataSource = $this->ds->getByID($id);

		foreach($dataSource->attributes as $attrib=>$value){
			$jsonData['item'][$attrib] = $value->value;
		}

		$jsonData['item']['statuscounts'] = array();
		foreach ($this->ro->valid_status AS $status)
		{
			// Hide some fields if there are no registry objects for that status
			if ($dataSource->getAttribute("count_$status") != 0 OR in_array($status, array(DRAFT, PUBLISHED))){
				array_push($jsonData['item']['statuscounts'], array('status' => $status, 'count' =>$dataSource->getAttribute("count_$status"),'name'=>readable($status)));
			}
		}

		$jsonData['item']['qlcounts'] = array();
		foreach ($this->ro->valid_levels AS $level){
			array_push($jsonData['item']['qlcounts'], array('level' => $level, 'count' =>$dataSource->getAttribute("count_level_$level")));
		}

		$jsonData['item']['classcounts'] = array();
		foreach($this->ro->valid_classes as $class){
			array_push($jsonData['item']['classcounts'], array('class' => $class, 'count' =>$dataSource->getAttribute("count_$class"),'name'=>readable($class)));
		}
		
		$harvesterStatus = $dataSource->getHarvesterStatus();
		//$date = new DateTime($harvesterStatus['next_harvest']);
		//$date = $date->format('Y-m-d H:i:s');
		$jsonData['item']['harvester_status'] = $harvesterStatus;
		//array(
		//	'status'=>$harvesterStatus['status'],
		//	'next_harvest'=> $date
		//);

		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}


	public function add(){
		$this->load->model('data_sources', 'ds');
		$ds = $this->ds->create($this->input->post('key'), url_title($this->input->post('title')));
		$ds->setAttribute('title', $this->input->post('title'));
		$ds->setAttribute('record_owner', $this->input->post('record_owner'));
		foreach($ds->stockAttributes as $key=>$value)
		{
			if(!isset($ds->attributes[$key]))
			$ds->setAttribute($key, $value);
		}
		foreach($ds->extendedAttributes as $key=>$value)
		{
			if(!isset($ds->attributes[$key]))			
			$ds->setAttribute($key, $value);
		}	
		foreach($ds->harvesterParams as $key=>$value)
		{
			if(!isset($ds->attributes[$key]))			
			$ds->setAttribute($key, $value);
		}			
		$ds->save();
		$ds->updateStats();
		echo $ds->id;
	}

public function getContributorGroupsEdit()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		date_default_timezone_set('Australia/Canberra');

		$POST = $this->input->post();
		$items = array();
		
		if (isset($POST['id'])){
			$id = (int) $this->input->post('id');
		}	

		$this->load->model("data_sources","ds");
		$dataSource = $this->ds->getByID($id);
		//print($dataSource->attributes['institution_pages']->value);
		if(isset($dataSource->attributes['institution_pages']->value))
		{
			$contributorPages = $dataSource->attributes['institution_pages']->value;
		} else {
			$contributorPages = 0;			
		}
		if (isset($POST['inst_pages'])){
			$contributorPages = (int) $this->input->post('inst_pages');
		}	
		switch($contributorPages)
		{
			case 0:
				$jsonData['contributorPages'] = "Pages are not managed";	
				break;
			case 1:
				$jsonData['contributorPages'] = "Pages are automatically managed";	
				break;
			case 2:
				$jsonData['contributorPages'] = "Pages are manually managed";;	
				break;
		}

		$dataSourceGroups = $dataSource->get_groups();
		if(sizeof($dataSourceGroups) > 0){
			foreach($dataSourceGroups as $group){
				$item = array();
				$group_contributor = array();
				$item['group'] = $group;
				$group_contributor = $dataSource->get_group_contributor($group);
				if($contributorPages=="1")
				{
					if(isset($group_contributor["key"]))
					{
						if($group_contributor["authorative_data_source_id"]==$id)
						{	
							//echo "contributor:".$group ." is the key and ".$group_contributor["key"]." is the got key";
							if($group_contributor["key"]=="Contributor:".$group)
							{
								$item['contributor_page'] = "<a href='../registry_object/view/".$group_contributor["registry_object_id"]."'> ".$group_contributor["key"]."</a>";
							}else{
								$item['contributor_page'] = 'Page will be auto generated on save';
							}
						}else{
							$item['contributor_page'] = $group_contributor["key"]."(<em>Managed by another datasource</em>)";
						}
					}else{
						$item['contributor_page'] = 'Page will be auto generated on save';
					}	
				}
				else if($contributorPages=="2")
				{
					if(isset($group_contributor["key"]))
					{
						if($group_contributor["authorative_data_source_id"]==$id)
						{
							$item['contributor_page'] = "<input type='text' name='".$group."' value='".$group_contributor["key"]."'/>";
						}else{
							$item['contributor_page'] = $group_contributor["key"]."(<em>Managed by another datasource</em>)";
						}
					}else{
						$item['contributor_page'] = "<input type='text' name='".$group."' value=''/>";
					}
				
				}else{
					$item['contributor_page'] = "";
				}			
				array_push($items, $item);
			}
			$jsonData['status'] = 'OK';
			$jsonData['items'] = $items;
		}		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}	
	public function getContributorGroups()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		date_default_timezone_set('Australia/Canberra');

		$POST = $this->input->post();
		$items = array();
		
		if (isset($POST['id'])){
			$id = (int) $this->input->post('id');
		}	
		$this->load->model("data_sources","ds");
		$dataSource = $this->ds->getByID($id);
		//print($dataSource->attributes['institution_pages']->value);
		if(isset($dataSource->attributes['institution_pages']->value))
		{
			$contributorPages = $dataSource->attributes['institution_pages']->value;
		} else {
			$contributorPages = 0;			
		}

		switch($contributorPages)
		{
			case 0:
				$jsonData['contributorPages'] = "Pages are not managed";	
				break;
			case 1:
				$jsonData['contributorPages'] = "Pages are automatically managed";	
				break;
			case 2:
				$jsonData['contributorPages'] = "Pages are manually managed";;	
				break;
		}

		$dataSourceGroups = $dataSource->get_groups();
		if(sizeof($dataSourceGroups) > 0){
			foreach($dataSourceGroups as $group){

				$item = array();
				$group_contributor = array();
				$item['group'] = $group;
				$group_contributor = $dataSource->get_group_contributor($group);
				if(isset($group_contributor["key"]))
				{
					if($group_contributor["authorative_data_source_id"]==$id)
					{
						$theAnchor = anchor('registry_object/view/'.$group_contributor["registry_object_id"]);
						$item['contributor_page'] = "<a href='../registry_object/view/".$group_contributor["registry_object_id"]."'> ".$group_contributor["key"]."</a>";
					}else{
						$item['contributor_page'] = $group_contributor["key"]."(<em>Managed by another datasource</em>)";
					}
				}else{
					$item['contributor_page'] = '<em>Not managed</em>';
				}
				
				array_push($items, $item);
			}
			$jsonData['status'] = 'OK';
			$jsonData['items'] = $items;
		}		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}


	/**
	 * getDataSourceLogs
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] data_source_id [POST] offset [POST] count [POST] log_id
	 * 
	 * @return [json] [logs for the data source]
	 */
	public function getDataSourceLogs(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		// date_default_timezone_set('Australia/Canberra');//???

		$this->load->model('data_sources', 'ds');

		$post = $this->input->post();

		$id = isset($post['id']) ? $post['id'] : 0; //data source id
		if($id==0) {
			throw new Exception('Datasource ID must be provided');
			exit();
		}
		$offset = isset($post['offset']) ? (int) $post['offset'] : 0;
		$count = isset($post['count']) ? (int) $post['count'] : 10;
		$logid = isset($post['logid']) ? (int) $post['logid'] : null;
		$log_class = isset($post['log_class']) ? $post['log_class'] : 'all';
		$log_type = isset($post['log_type']) ? $post['log_type'] : 'all';

		$jsonData = array();
		$dataSource = $this->ds->getByID($id);
		$dataSourceLogs = $dataSource->get_logs($offset, $count, $logid, $log_class, $log_type);
		$jsonData['log_size'] = $dataSource->get_log_size($log_type);

		if($jsonData['log_size'] > ($offset + $count)){
			$jsonData['next_offset'] = $offset + $count;
			$jsonData['hasMore'] = true;
		}else{
			$jsonData['next_offset'] = 'all';
			$jsonData['hasMore'] = false;
		}

		$items = array();
		if(sizeof($dataSourceLogs) > 0){
			foreach($dataSourceLogs as $log){
				$item = array();
				$item['type'] = $log['type'];
				$item['log_snippet'] = first_line($log['log']);
				$item['log'] = $log['log'];
				$item['id'] = $log['id'];
				$item['date_modified'] = timeAgo($log['date_modified']);
				$item['harvester_error_type'] = $log['harvester_error_type'];				
				array_push($items, $item);
			}
		}
		$jsonData['count'] = $count;
		$jsonData['items'] = $items;

		echo json_encode($jsonData);
	}
	


	

	public function cancelHarvestRequest(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		// date_default_timezone_set('Australia/Canberra');//???

		$this->load->model('data_sources', 'ds');
		$jsonData = array();
		$post = $this->input->post();
		$id = isset($post['id']) ? $post['id'] : 0; //data source id
		$harvest_id = isset($post['harvest_id']) ? $post['harvest_id'] : 0; //data source id
		if($harvest_id==0 || $id == 0) {
			//throw new Exception('Datasource ID must be provided');
			//exit();
			$jsonData['log'] = $post;
		}


		$dataSource = $this->ds->getByID($id);
		$jsonData['data_source_id'] = $id;
		$jsonData['harvest_id'] = $harvest_id;
		$jsonData['log'] = $dataSource->cancelHarvestRequest($harvest_id, true);

		echo json_encode($jsonData);
	}
	/**
	 * Save a data source
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [POST] Data Source ID [POST] attributes
	 * @todo ACL on which data source you have access to, error handling, new attributes
	 * @return [JSON] result of the saving [VOID] 
	 */
	public function updateDataSource(){
		
		$jsonData = array();
		$dataSource = NULL;
		$id = NULL; 
		
		
		$jsonData['status'] = 'OK';
		$POST = $this->input->post();
		//print("<pre>");
		//print_r($POST);
		//print("</pre>");

		if (isset($POST['data_source_id'])){
			$id = (int) $this->input->post('data_source_id');
		}
		
		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		
		if ($id == 0) {
			 $jsonData['status'] = "ERROR: Invalid data source ID"; 
		}
		else 
		{
			$dataSource = $this->ds->getByID($id);
		}

		$resetHarvest = false;

		// XXX: This doesn't handle "new" attribute creation? Probably need a whilelist to allow new values to be posted. //**whitelist**//
		if ($dataSource)
		{

			$valid_attributes = array_merge(array_keys($dataSource->attributes()), array_keys($dataSource->harvesterParams));
			$valid_attributes = array_merge($valid_attributes, array_keys($dataSource->primaryRelationship));
			$valid_attributes = array_merge($valid_attributes, array_keys($dataSource->institutionPages));
			$valid_attributes = array_merge($valid_attributes, array_keys($dataSource->stockAttributes));
			$valid_attributes = array_merge($valid_attributes, array_keys($dataSource->extendedAttributes));
			$valid_attributes = array_unique($valid_attributes);

			foreach($valid_attributes as $attrib){	
				$new_value = null;

				if (isset($POST[$attrib])){					

					$new_value = trim($this->input->post($attrib));

				}
				else if(in_array($attrib, $dataSource->harvesterParams))
				{
					$new_value = '';	
				}
				else if(in_array($attrib, $dataSource->primaryRelationship)){
					$new_value = '';				
				}	
				if($this->input->post('save_relationships')=='false')
				{
					switch($attrib){
						case 'class_1':
						case 'primary_key_1':
						case 'service_rel_1':
						case 'activity_rel_1':
						case 'collection_rel_1':
						case 'party_rel_1':
						case 'class_2':
						case 'primary_key_2':
						case 'service_rel_2':
						case 'activity_rel_2':
						case 'collection_rel_2':
						case 'party_rel_2':		
							$new_value = '';
							break;
						default:
							break;
					}
				
				}


			/*	this push to nla functionality has been removed as NLA aren't using it and the ds admins were getting confused

				if($this->input->post('push_to_nla')=='false')
				{
					switch($attrib){
						case 'isil_value':
							$new_value = '';
							break;
						default:
							break;	
					}				
				} 

			*/

				if($new_value=='true') $new_value=DB_TRUE;
				if($new_value=='false'){$new_value=DB_FALSE;} 

				if($new_value != $dataSource->{$attrib} && in_array($attrib, $dataSource->harvesterParams))
				{
				   $resetHarvest = true;
				} 

				if (!is_null($new_value))
				{
					$dataSource->{$attrib} = $new_value;


					if($new_value == '' && $new_value != $dataSource->{$attrib} && in_array($attrib, $dataSource->harvesterParams))
					{
						$dataSource->unsetAttribute($attrib);
					}
					else{
						$dataSource->setAttribute($attrib, $new_value);
					}

					if($attrib=='institution_pages')
					{
						$dataSource->setContributorPages($new_value,$POST);
					}

				}
				$dataSource->updateStats();	
			}		
	
			$dataSource->save();

			if($resetHarvest)
			{
				$dataSource->requestHarvest();
			}
		}
		
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}
	
	/**
	 * Trigger harvest
	 */
	function triggerHarvest()
	{
		$jsonData = array("status"=>"ERROR");

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		if ($this->input->post('data_source_id')){

			$id = (int) $this->input->post('data_source_id');
	
			if ($id == 0) {
				 $jsonData['message'] = "ERROR: Invalid data source ID"; 
			}
			else 
			{
				$dataSource = $this->ds->getByID($id);
				$dataSource->requestHarvest();
				$jsonData['status'] = "OK";
			}
		}
		
		echo json_encode($jsonData);
	}
	
	/**
	 * Importing (Ben's import from URL)
	 * 
	 * 
	 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
	 * @param [POST] URL to the source
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] result of the saving [VOID] 
	 */
	function importFromURLtoDataSource()
	{
		$this->load->library('importer');
		$this->load->model('data_source/data_sources', 'ds');		
		$data_source = $this->ds->getByID($this->input->post('data_source_id'));	
		$slogTitle =  'Import from URL completed successfully'.NL;	
		$elogTitle = 'An error occurred whilst importing from the specified URL'.NL;
		$log = 'IMPORT LOG' . NL;
		//$log .= 'URI: ' . $this->input->post('url') . NL;
		$log .= 'Harvest Method: Direct import from URL' . NL;
		
		$url = $this->input->post('url');
		$log .= "URL: ".$url.NL;
		if (!preg_match("/^https?:\/\/.*/",$url))
		{
			$data_source->append_log($elogTitle.$log.NL."URL must be valid http:// or https:// resource. Please try again.", HARVEST_ERROR, "importer","DOCUMENT_LOAD_ERROR");
			echo json_encode(array("response"=>"failure", "message"=>"URL must be valid http:// or https:// resource. Please try again.", "log"=>substr($elogTitle.$log,0, 1000)));
			return;	
		}
		
		$xml = @file_get_contents($this->input->post('url'));
		if (strlen($xml) == 0)
		{
			$data_source->append_log($elogTitle.$log.NL."Unable to retrieve any content from the specified URL", HARVEST_ERROR, "importer","DOCUMENT_LOAD_ERROR");			
			echo json_encode(array("response"=>"failure", "message"=>"Unable to retrieve any content from the specified URL", "log"=>substr($elogTitle.$log,0, 1000)));
			// todo: http error?
			return;	
		}
		
		try
		{ 



			$this->importer->setXML($xml);

			if ($data_source->provider_type != RIFCS_SCHEME)
			{
				$this->importer->setCrosswalk($data_source->provider_type);
			}

			$this->importer->setDatasource($data_source);
			$this->importer->commit();


			if ($error_log = $this->importer->getErrors())
			{
				$data_source->append_log($elogTitle.$log.$error_log, HARVEST_ERROR ,"HARVEST_ERROR");
			}
			else{
				$data_source->append_log($slogTitle.$log.$this->importer->getMessages(), HARVEST_INFO,"HARVEST_INFO");
			}

		}
		catch (Exception $e)
		{
			
			$log .= "CRITICAL IMPORT ERROR [HARVEST COULD NOT CONTINUE]" . NL;
			$log .= $e->getMessage();
			$data_source->append_log($log, HARVEST_ERROR, "importer","IMPORT_ERROR");				
			echo json_encode(array("response"=>"failure", "message"=>"An error occured whilst importing from this URL", "log"=>substr($log,0, 1000)));
			return;	
		}	
	
		echo json_encode(array("response"=>"success", "message"=>"Import completed successfully!", "log"=>$log));	
			
	}

	/**
	 * Importing (Ben's import from XML Paste)
	 * 
	 * 
	 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
	 * @param [POST] xml A blob of XML data to parse and import
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] result of the saving [VOID] 
	 */
	function importFromXMLPasteToDataSource()
	{
		$this->load->library('importer');
		

		$xml = $this->input->post('xml');
		$slogTitle =  'Import from XML content completed successfully'.NL;	
		$elogTitle = 'An error occurred whilst importing from the specified XML'.NL;
		$log = 'IMPORT LOG' . NL;
		$log .= 'Harvest Method: Direct import from XML content' . NL;
		$log .= strlen($xml) . ' characters received...' . NL;

		if (strlen($xml) == 0)
		{
			$data_source->append_log($elogTitle.$log.NL ."Unable to retrieve any content from the specified XML", HARVEST_ERROR, "importer","IMPORT_ERROR");		
			echo json_encode(array("response"=>"failure", "message"=>"Unable to retrieve any content from the specified XML", "log"=>substr($elogTitle.$log,0, 1000)));
			return;	
		}
		

		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID($this->input->post('data_source_id'));

		$xml=stripXMLHeader($xml);
		if ($data_source->provider_type && $data_source->provider_type != RIFCS_SCHEME)
		{
			$this->importer->setCrosswalk($data_source->provider_type);
		}
		else if (strpos($xml, "<registryObjects") === FALSE)
		{
			$xml = wrapRegistryObjects($xml);
		}

		try
		{ 

			$this->importer->setXML($xml);

			$this->importer->setDatasource($data_source);
			$this->importer->commit();


			if ($error_log = $this->importer->getErrors())
			{
				$data_source->append_log($elogTitle.$log.NL.$error_log,  HARVEST_ERROR, "importer", "HARVEST_ERROR" );
			}
			else{
				$log .= "IMPORT COMPLETED" . NL;
				$log .= "====================" . NL;
				$log .= $this->importer->getMessages();
				$data_source->append_log($slogTitle.$log.NL,  HARVEST_INFO, "importer", "HARVEST_INFO" );
			}



			// data source log append...
			
		}
		catch (Exception $e)
		{
			
			$log .= "CRITICAL IMPORT ERROR [HARVEST COULD NOT CONTINUE]" . NL;
			$log .= $e->getMessage();

			$data_source->append_log($elogTitle.$log, HARVEST_ERROR, "importer","IMPORT_ERROR");		
			echo json_encode(array("response"=>"failure", "message"=>"An error occured whilst importing from the specified XML", "log"=>substr($elogTitle.$log,0, 1000)));
			return;	
		}	
		
	
		echo json_encode(array("response"=>"success", "message"=>"Import completed successfully!", "log"=>$log));	
			
	}

	/**
	 * Importing (Leo's reinstate based on ... Ben's import from XML Paste)
	 * 
	 * 
	 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
	 * @param [POST] xml A blob of XML data to parse and import
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] result of the saving [VOID] 
	 */
	function reinstateRecordforDataSource()
	{
		$this->load->library('importer');

		$deletedRegistryObjectId = $this->input->post('deleted_registry_object_id');

		$xml = $this->input->post('xml');

		$log = 'REINSTATE RECORD LOG' . NL;
		$log .= 'deleted Registry Object ID: '.$deletedRegistryObjectId . NL;
		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID($this->input->post('data_source_id'));
		$this->load->model("registry_object/registry_objects", "ro");

		$deletedRo = $this->ro->getDeletedRegistryObject($deletedRegistryObjectId);
		if($deletedRo)
		{
		$xml = wrapRegistryObjects($deletedRo[0]['record_data']);
		}
		else{
			$log .= 'record is missing' . NL;
			echo json_encode(array("response"=>"failure", "message"=>"Record is missing", "log"=>$log));
			return;
		}
		try
		{ 

			$this->importer->setXML($xml);

			$this->importer->setDatasource($data_source);
			$this->importer->commit();


			if ($error_log = $this->importer->getErrors())
			{
				$log .= NL . "ERRORS DURING IMPORT" . NL;
				$log .= "====================" . NL ;
				$log .= $error_log;
			}

			$log .= "IMPORT COMPLETED" . NL;
			$log .= "====================" . NL;
			$log .= $this->importer->getMessages();

			// data source log append...
			$this->ro->removeDeletedRegistryObject($deletedRegistryObjectId);
			$data_source->append_log($log, ($error_log ? HARVEST_ERROR : null),"registry_object");
		}
		catch (Exception $e)
		{
			
			$log .= "CRITICAL IMPORT ERROR [IMPORT COULD NOT CONTINUE]" . NL;
			$log .= $e->getMessage();
			$data_source->append_log($log, HARVEST_ERROR ,"registry_object");
			echo json_encode(array("response"=>"failure", "message"=>"An error occured whilst importing from the specified XML", "log"=>$log));
			return;	
		}	
		
	
		echo json_encode(array("response"=>"success", "message"=>"Import completed successfully!", "log"=>$log));	
			
	}


	public function testHarvest()
	{
		header('Content-type: application/json');
		$jsonData = array();
		$dataSource = NULL;
		$id = NULL; 


		$jsonData['status'] = 'OK';
		$POST = $this->input->post();
		if (isset($POST['data_source_id'])){
			$id = (int) $this->input->post('data_source_id');
		}

		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		if ($id == 0) {
			 $jsonData['status'] = "ERROR: Invalid data source ID"; 
		}
		else 
		{
			$dataSource = $this->ds->getByID($id);
		}

		// XXX: This doesn't handle "new" attribute creation? Probably need a whitelist to allow new values to be posted. //**whitelist**//
		if ($dataSource)
		{
			$dataSourceURI = $this->input->post("uri");
			$providerType = $this->input->post("provider_type");
			$OAISet = $this->input->post("oai_set");
			$harvestMethod = $this->input->post("harvest_method");
			$harvestDate = $this->input->post("harvest_date");
			$harvestFrequency = $this->input->post("harvest_frequency");
			$advancedHarvestingMethod = $this->input->post("advanced_harvesting_mode");	
			$nextHarvest = $harvestDate;
			$jsonData['logid'] = $dataSource->requestHarvest('','',$dataSourceURI, $providerType, $OAISet, $harvestMethod, $harvestDate, $harvestFrequency, $advancedHarvestingMethod, $nextHarvest, true);				
		}

		$jsonData = json_encode($jsonData);
		echo $jsonData;			
	}
	

	public function putHarvestData()
	{
		$POST = $this->input->post();
		$done = false;
		$mode = false;
		header("Content-Type: text/xml; charset=UTF-8", true);
		date_default_timezone_set('Australia/Canberra');

		$responseType = 'error';
		$message = 'THANK YOU';
		$logMsg = 'Harvest completed successfully';
		$logMsgErr = 'An error occurred whilst trying to harvest records';
		$harvestId = false;
		if (isset($POST['harvestid'])){
			$harvestId = (int) $this->input->post('harvestid');
		}
		if($harvestId)
		{
		$this->load->model("data_sources","ds");
		$dataSource = $this->ds->getByHarvestID($harvestId);




			if (isset($POST['content'])){
				$data =  $this->input->post('content');
			}
			if (isset($POST['errmsg'])){
				$errmsg =  $this->input->post('errmsg');
			}
			if (isset($POST['done'])){
				$done =  strtoupper($this->input->post('done'));
			}
			if (isset($POST['date'])){
				$nextHarvestDate =  $this->input->post('date');
			}
			if (isset($POST['mode'])){
				$mode =  strtoupper($this->input->post('mode'));
			}

			if($mode == 'TEST')
			{
				$logMsg = 'Test harvest completed successfully';
				$logMsgErr = 'An error occurred whilst testing harvester settings';
			}

			$logMsg .= ' (harvestID: '.$harvestId.')';
			$logMsgErr .= ' (harvestID: '.$harvestId.')';
			//$dataSource->append_log("HARVESTER TRYING TO PUT DATA:".NL." Completed: ".$done.NL." mode: ".$mode , HARVEST_MSG, "harvester","HARVESTER_INFO");
			if($errmsg)
			{
				$dataSource->append_log($logMsgErr.NL."HARVESTER RESPONDED UNEXPECTEDLY: ".$errmsg, HARVEST_ERROR, "harvester","HARVESTER_ERROR");
			}
			else
			{	
	
				$this->load->library('importer');	

				$this->load->model('data_source/data_sources', 'ds');
				$rifcsXml = '';
				// xxx: this won't work with crosswalk!
				
				$xml = simplexml_load_string(utf8_encode(str_replace("&", "&amp;", $data)), "SimpleXMLElement", LIBXML_NOENT);

				if ($xml === false)
				{
					$exception_message = "Could not parse Registry Object XML" . NL;
					foreach(libxml_get_errors() as $error) {
        				$exception_message .= NL.$error->message;
        			}
					$log = "Document Load Error: ".$exception_message.NL;
					$dataSource->append_log($logMsgErr.NL.$log.NL."CRITICAL ERROR: Could not Load XML from OAI feed. Check your provider.".NL.$exception_message, HARVEST_ERROR, "harvester","HARVESTER_ERROR");					
				}
				else
				{
					$rifcsXml = $this->importer->getRifcsFromFeed($data);
					if (strpos($rifcsXml, 'registryObject ') === FALSE)
					{
						//$dataSource->append_log("CRITICAL ERROR: Could not extract data from OAI feed. Check your provider.", HARVEST_ERROR, "harvester");
						$dataSource->append_log($logMsgErr.NL."CRITICAL ERROR: Could not extract data from OAI feed. Check your provider.", HARVEST_ERROR, "harvester","HARVESTER_ERROR");					
						//	$dataSource->append_log($rifcsXml, HARVEST_ERROR, "harvester");	
					}
					else
					{

						$this->importer->setXML($rifcsXml);

						if ($dataSource->provider_type != RIFCS_SCHEME)
						{
							$this->importer->setCrosswalk($dataSource->provider_type);
						}

						$this->importer->setHarvestID($harvestId);
						$this->importer->setDatasource($dataSource);

						if ($done != TRUE)
						{
							$this->importer->setPartialCommitOnly(TRUE);
						}
						else
						{
							$this->importer->setPartialCommitOnly(FALSE);
						}


						if ($mode == "HARVEST")
						{
							try
							{
								$this->importer->commit();

								if($this->importer->getErrors())
								{
									$dataSource->append_log($logMsgErr.NL.$this->importer->getMessages().NL.$this->importer->getErrors(), HARVEST_ERROR, "harvester", "HARVESTER_ERROR");	
								}
								else
								{
									$dataSource->append_log($logMsg.NL.$this->importer->getMessages(), HARVEST_INFO, "harvester", "HARVESTER_INFO");	
								}
								
								$dataSource->updateStats();
								$responseType = 'success';
							}
							catch (Exception $e)
							{
								$dataSource->append_log($logMsgErr.NL."CRITICAL ERROR: " . NL . $e->getMessages() . NL . $this->importer->getErrors(), HARVEST_ERROR, "harvester","HARVESTER_ERROR");	
							}
						}
						else{
							$dataSource->append_log($logMsg, HARVEST_MSG, "harvester", "HARVESTER_INFO");	
						}	
					}
				}
			}
			if($done == TRUE || $mode != "HARVEST")
			{
				// TODO: make up a better way to display log for multiple OAI chunks
				//if($mode == "HARVEST")
				//$dataSource->append_log($logMsg.NL."IMPORT COMPLETED", HARVEST_MSG, "harvester","HARVESTER_INFO");	

				//$dataSource->deleteHarvestRequest($harvestId);
				$dataSource->cancelHarvestRequest($harvestId,false);
				if($dataSource->advanced_harvest_mode == 'REFRESH')
				{
					$dataSource->append_log($logMsg.NL."HARVEST MODE REFRESH: ".NL."new Harvest ID: ".$harvestId, HARVEST_MSG, "harvester","HARVESTER_INFO");
					$dataSource->deleteOldRecords($harvestId);
				} 
			}

			if($done == TRUE && $nextHarvestDate)
			{
				$dataSource->requestHarvest(null,null,null,null,null,null,$nextHarvestDate);
				//reschedule!
			}
			
		}
		else
		{
			$message = "Missing harvestid param";
		}


		print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
		print('<response type="'.$responseType.'">'."\n");
		print('<timestamp>'.date("Y-m-d H:i:s").'</timestamp>'."\n");
		print("<message>".$message."</message>\n");
		print("</response>");
	}



	function getContributorPages()
	{
		$POST = $this->input->post();
		print_r($POST);
				print('<?xml version="1.0" encoding="UTF-8"?>'."\n");
		print('<response type="">'."\n");
		print('<timestamp>'.date("Y-m-d H:i:s").'</timestamp>'."\n");
		print("<message> we need to get the contibutor groups and the pages if required</message>\n");
		print("</response>");
		return " we need to get the contibutor groups and the pages if required";

	}


	function exportDataSource($id)
	{
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		$as = 'xml';
		$classtring = '';
		$statusstring = '';
		//$classtring = 'activitycollectionserviceparty';
		$data = json_decode($this->input->get('data'));
		foreach($data as $param)
		{
			if($param->name == 'ro_class')
				$classtring .= $param->value;
			if($param->name == 'as')
				$as = $param->value;
			if($param->name == 'ro_status')
				$statusstring .= $param->value;
		}
		$this->load->model("data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");
		$dataSource = $this->ds->getByID($id);
		$dsSlug = $dataSource->getAttribute('slug');
		$rifcs = '';
		$ids = $this->ro->getIDsByDataSourceID($id, false, 'All');
		if($ids)
		{
			$i = 0;
			foreach($ids as $idx => $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($ro && (strpos($classtring, $ro->class) !== false) && (strpos($statusstring, $ro->status) !== false))
					{
						$rifcs .= $ro->getRif().NL;
					}
				}catch (Exception $e){}

				if ($idx % 100 == 0)
				{
					unset($ro);
					gc_collect_cycles();
				}
			}
		}
		if($as == 'file')
		{
		    $this->load->helper('download');
		    force_download($dsSlug.'-RIF-CS-Export.xml', wrapRegistryObjects($rifcs));
		}
		else
		{
		 	header('Cache-Control: no-cache, must-revalidate');
		 	header('Content-type: application/xml');
		 	echo wrapRegistryObjects($rifcs);
		 }
	}

	/* Leo's quality report */
	function quality_report($id){
		//$data['report'] = $this->getDataSourceReport($id);
		$data['title'] = 'Datasource Report';
		$data['scripts'] = array();
		$data['js_lib'] = array('core');

		$this->load->model("data_source/data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		$report = array();
		$data['ds'] = $this->ds->getByID($id);
		$ids = $this->ro->getIDsByDataSourceID($id, false, 'All');

		if($ids){
			$data['record_count'] = sizeof($ids);
			$problems=0;
			foreach($ids as $idx=>$ro_id){
				try{
					$ro=$this->ro->getByID($ro_id);
					$report[$ro_id] = array('quality_level'=>$ro->quality_level,'title'=>$ro->title,'status'=>$ro->status,'id'=>$ro->id,'report'=>$ro ? $ro->getMetadata('quality_html') : '');
				}catch(Exception $e){
					throw Exception($e);
				}
				if($idx % 100 == 0){
					unset($ro);
					gc_collect_cycles();
				}
			}
		}
		$data['report'] = $report;
		$this->load->view('report', $data);
	}


	/* Ben's chart report */
	function report($id){
		//$data['report'] = $this->getDataSourceReport($id);
		$data['title'] = 'Datasource Report';
		$data['scripts'] = array('ds_chart');
		$data['js_lib'] = array('core','googleapi');
		$data['less']=array('charts');

		$this->load->model("data_source/data_sources","ds");
		$this->load->model("registry_object/registry_objects", "ro");

		$data['status_tabs'] = Registry_objects::$statuses;
		$data['ds'] = $this->ds->getByID($id);

		$this->load->view('chart_report', $data);
	}


	function getDataSourceReport($id){
		
		$dataSource = $this->ds->getByID($id);
		$ids = $this->ro->getIDsByDataSourceID($id, false, 'All');
		$report = "<h3>QUALITY REPORT FOR ".$dataSource->title."</h3>";
		$j = 0;
		$qa_report = '';
		if($ids)
		{
			$report .= "<h4>record count :".sizeof($ids)."</h4>";
			$i = 0;
			foreach($ids as $idx => $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($ro)
					{
						$text = $ro->getMetadata('quality_html');
						if($text && $text != '')
						{
							//var_dump($text);
							$j++;
							$qa_report .= "<a id='".$ro_id. "'>".$ro->title."</a><br/>" .$text ."<br/>";
							$qa_report .= "<br/>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~<br/>";
						}
					}
				}catch (Exception $e){}

				if ($idx % 100 == 0)
				{
					unset($ro);
					gc_collect_cycles();
				}
			}
			$report .= "<h4>records with issues :".$j."</h4>";
			$report .= $qa_report;
		}
		echo $report;

	}

	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}
