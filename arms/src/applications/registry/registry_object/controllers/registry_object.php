<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Registry Object controller
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/registryobject
 * 
 */
class Registry_object extends MX_Controller {

	public function index(){
		$this->manage();
	}


	public function testRecordSuite(){
		echo "<pre>";

		$this->load->model('registry_object/registry_objects','ro');
		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID(13);

		$pub_ro = $this->ro->getPublishedByKey("apps.ands.org.au/party-2");
		$draft_ro = $this->ro->getDraftByKey("apps.ands.org.au/party-2");

		if ($pub_ro)
		{
	//		echo "<font color=red>" . $this->ro->cloneToDraft($pub_ro) . "</font>";
			
	//		print_pre(htmlentities($pub_ro->getRif()));
		//	$this->ro->deleteRegistryObject($pub_ro);
		//	$pub_ro->status = DRAFT;
		//	$pub_ro->save();
		}

		if($draft_ro)
		{
	//		$this->ro->deleteRegistryObject($draft_ro);
	//		$draft_ro->status = PUBLISHED;
	//		$draft_ro->save();
		}

		//$draft_ro->save();
		//$ro->status = DRAFT;
		//$ro->save();
		echo $pub_ro;
		echo "<hr/>";
		echo $draft_ro;

	}

	
	public function manage_table($data_source_id = false){
		$data['title'] = 'Manage My Records';

		$this->load->model('data_source/data_sources', 'ds');
		if($data_source_id){
			$data_source = $this->ds->getByID($data_source_id);
			if(!$data_source) show_error("Unable to retrieve data source id = ".$data_source_id, 404);
			
			$data_source->updateStats();//TODO: XXX

			//$data['data_source'] = $data_source;
			$data['data_source'] = array(
				'title'=>$data_source->title,
				'id'=>$data_source->id,
				'count_total'=>$data_source->count_total,
				'count_APPROVED'=>$data_source->count_APPROVED,
				'count_SUBMITTED_FOR_ASSESSMENT'=>$data_source->count_SUBMITTED_FOR_ASSESSMENT,
				'count_PUBLISHED'=>$data_source->count_PUBLISHED
			);

			//MMR
			//$this->load->model('registry_object/registry_objects', 'ro');
			//$ros = $this->ro->getByDataSourceID($data_source_id);

		}else{
			//showing all registry objects for all datasource
			//TODO: check for privileges
			$this->load->model('maintenance/maintenance_stat', 'mm');
			$total = $this->mm->getTotalRegistryObjectsCount('db');
			$data['data_source'] = array(
				'title'=>'Viewing All Registry Object',
				'id'=>'0',
				'count_total'=>$total,
				'count_APPROVED'=>0,
				'count_SUBMITTED_FOR_ASSESSMENT'=>0,
				'count_PUBLISHED'=>0
			);
			//show_error('No Data Source ID provided. use all data source view for relevant roles');
			
		}
		$data['scripts'] = array('manage_my_record');
		$data['js_lib'] = array('core', 'tinymce', 'datepicker', 'dataTables');


		$this->load->view("manage_my_record", $data);
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
	public function manage($data_source_id=false){
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
		$data['less']=array('mmr_hopper');
		$data['scripts'] = array('hopper');
		$data['js_lib'] = array('core');
		$this->load->view("manage_my_record_hopper", $data);
	}

	public function get_mmr_data($data_source_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');
		$data_source = $this->ds->getByID($data_source_id);

		foreach($data_source->attributes as $attrib=>$value){
			$jsonData['ds'][$attrib] = $value->value;
		}

		$qa = $data_source->qa_flag=='t' ? true : false;
		$auto_published = $data_source->auto_published=='t' ? true: false;


		$jsonData['valid_statuses'] = array('MORE_WORK_REQUIRED', 'DRAFT', 'PUBLISHED');
		if($qa) {
			array_push($jsonData['valid_statuses'], 'SUBMITTED_FOR_ASSESSMENT', 'ASSESSMENT_IN_PROGRESS');
		}
		if(!$auto_published){
			array_push($jsonData['valid_statuses'], 'APPROVED');	
		}

		$jsonData['statuses'] = array();
		foreach($jsonData['valid_statuses'] as $s){
			$no_match = false;
			$args = array();
			$st = array('display_name'=>str_replace('_', ' ', $s), 'name'=>$s);
			switch($s){
				case 'DRAFT':
					$st['ds_count']=$data_source->count_DRAFT;
					if($qa){
						$st['connectTo']='SUBMITTED_FOR_ASSESSMENT';
					}else{
						if(!$auto_published){
							$st['connectTo']='APPROVED';
						}else{
							$st['connectTo']='PUBLISHED';
						}
					}
					break;
				case 'MORE_WORK_REQUIRED':
					$st['ds_count']=$data_source->count_MORE_WORK_REQUIRED;
					$st['connectTo']='DRAFT';
					break;
				case 'SUBMITTED_FOR_ASSESSMENT':
					$st['ds_count']=$data_source->count_SUBMITTED_FOR_ASSESSMENT;
					$st['connectTo']='ASSESSMENT_IN_PROGRESS';
					break;
				case 'ASSESSMENT_IN_PROGRESS':
					$st['ds_count']=$data_source->count_ASSESSMENT_IN_PROGRESS;
					$st['connectTo']='APPROVED';
					break;
				case 'APPROVED':
					$st['ds_count']=$data_source->count_APPROVED;
					$st['connectTo']='PUBLISHED';
					break;
				case 'PUBLISHED':
					$st['ds_count']=$data_source->count_PUBLISHED;
					$st['connectTo']='';
					break;
			}
			$filters = $this->input->post('filters');

			$args['sort'] = isset($filters['sort']) ? $filters['sort'] : array('updated'=>'desc');
			$args['search'] = isset($filters['search']) ? $filters['search'] : false;
			$args['filter'] = array('status'=>$s);

			$white_list = array('title', 'class', 'key', 'status', 'slug', 'record_owner');
			$filtered_ids = array();
			$filtered = array();
			if(isset($filters['filter'])){
				foreach($filters['filter'] as $key=>$value){
					if(!in_array($key, $white_list)){
						$list = $this->ro->getByAttributeDatasource($data_source_id, $key, $value, false, false);
						if($list){
							$filtered_ids = array_merge($filtered_ids, $list);
						}else{
							$no_match = true;
						}
					}else{
						$f[$key] = $value;
						$args['filter'][$key] = $value;
					}
				}
				foreach($filtered_ids as $k){
					array_push($filtered, $k['registry_object_id']);
				}
			}
			$args['filtered_id']=$filtered;

			if(!$no_match){
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
				$st['hasMore'] = $ros['hasMore'];
				$st['ds_id'] = $data_source_id;
			}else{
				$st['count']=0;
			}
			$jsonData['statuses'][$s] = $st;
		}
		$jsonData['filters'] = $filters;
		echo json_encode($jsonData);
	}



	public function get_more_mmr_data(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
			
		$args['filter'] = array('status'=>$this->input->post('status'));
		$filters = array(
			'ds_id'=>$this->input->post('ds_id'),
			'limit'=>10,
			'offset'=>$this->input->post('offset'),
			'args'=>$args
		);

		$results = $this->get_ros($filters);
		if($results){
			echo json_encode($results);
		}
	}

	public function get_ros($filters, $getCount=false){
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
				array_push($results['items'], array(
						'id'=>$registry_object->id, 
							'title'=>$registry_object->title,
							'status'=>$registry_object->status,
							'class'=>$registry_object->class,
							'quality_level'=>$registry_object->quality_level,
							'updated'=>timeAgo($registry_object->updated),
							'error_count'=>$registry_object->error_count,
							'warning_count'=>$registry_object->warning_count,
							'data_source_id'=>$registry_object->data_source_id,
							'flag'=>$registry_object->flag
						));
			}
		}else return false;
		if(sizeof($ros)<$filters['limit']){
			$results['hasMore']=false;
		}else{
			$results['hasMore']=true;
		}
		return $results;
	}


	public function view($ro_id, $revision=''){
		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($ro_id);
		if($ro){
			$this->load->model('data_source/data_sources', 'ds');
			$ds = $this->ds->getByID($ro->data_source_id);

			$data['scripts'] = array('view_registry_object');
			$data['js_lib'] = array('core','prettyprint');
			$data['title'] = $ro->title;
			$data['ro'] = $ro;
			$data['ds'] = $ds;

			$data['revision'] = $revision;

			if($revision!=''){
				$data['viewing_revision'] = true;
				$data['rif_html'] = $ro->transformForHtml($revision);
			}else {
				$data['viewing_revision'] = false;
				$data['rif_html'] = $ro->transformForHtml();
			}

			$data['revisions'] = $ro->getAllRevisions();
			$this->load->view('registry_object_index', $data);
		}else{
			show_404('Unable to Find Registry Object ID: '.$ro_id);
		}
	}

	public function preview($ro_id, $format='html'){
		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($ro_id);
		$data['ro']=$ro;
		if($format=='pane'){
			$this->load->view('registry_object_preview_pane', $data);
		}
	}

	public function add(){
		$data['title'] = 'Add Registry Objects';
		$data['scripts'] = array('add_registry_object');
		$data['js_lib'] = array('core','prettyprint');
		$this->load->view("add_registry_object", $data);
	}

	public function edit($registry_object_id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($registry_object_id);
		$data['extrif'] = $ro->getExtRif();
		$data['content'] = $ro->transformCustomForFORM($data['extrif']);
		$data['title'] = 'Edit: '.$ro->title;
		$data['scripts'] = array('add_registry_object');
		$data['js_lib'] = array('core', 'tinymce', 'datepicker', 'prettyprint');
		$this->load->view("add_registry_object", $data);
	}

	public function validate($registry_object_id){
		$xml = $this->input->post('xml');
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($registry_object_id);
		$result = $ro->transformForQA(wrapRegistryObjects($xml));
		echo $result;
	}

	public function getData($data_source_id, $filter='', $value=''){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$jsonData = array();
		$jsonData['aaData'] = array();

		//ahmagerd shorthand
		$limit = ($this->input->post('iDisplayLength') ? (int) $this->input->post('iDisplayLength') : 10);
		$offset = ($this->input->post('iDisplayStart') ? (int) $this->input->post('iDisplayStart') : 0);

		//filters
		$filters = array();
		$filters['filter'] = $filter!='' ? array($filter=>$value) : false;
		$filters['search'] = ($this->input->post('sSearch') ? $this->input->post('sSearch') : false);

		//sort
		/*$filters['sort'] = array();
		$aColumns=array('key', 'title', 'status');
		for($i=0; $i<intval($this->input->post('iSortingCols')); $i++){//black magic
			if($this->input->post('bSortable_'.intval($this->input->post('iSortCol_'.$i)))=='true'){
				$filters['sort'][] = array(
					$aColumns[intval($this->db->escape_str($this->input->post('iSortCol_'.$i)))] => $this->db->escape_str($this->input->post('sSortDir_'.$i))
				);
			}
        }*/

        $this->load->model('data_source/data_sources', 'ds');
        $data_source = $this->ds->getByID($data_source_id);

		//Get Registry Objects
		$this->load->model('registry_object/registry_objects', 'ro');
		if($data_source_id >0) {
			$ros = $this->ro->getByDataSourceID($data_source_id,$limit,$offset,$filters);
			$total = (int) $data_source->count_total;
		}else{
			$this->load->model('registry_object/registry_objects', 'ro');
			$ros = $this->ro->getAll($limit, $offset, $filters);
			$this->load->model('maintenance/maintenance_stat', 'mm');
			$total = $this->mm->getTotalRegistryObjectsCount('db');
		}

		if($ros){
			foreach($ros as $ro){
				$jsonData['aaData'][] = array(
					'key'=>anchor('registry_object/view/'.$ro->registry_object_id, $ro->key),
					'id'=>$ro->registry_object_id,
					'Title'=>$ro->list_title,
					'Status'=>$ro->status,
					'Options'=>'Options'
				);
			}
		}

		//Data Source
		$this->load->model('data_source/data_sources', 'ds');
		$data_source = $this->ds->getByID($data_source_id);

		$jsonData['sEcho']=(int)$this->input->post('sEcho');
		$jsonData['iTotalRecords'] = $total;
		$hasFilter = false;
		$jsonData['iTotalDisplayRecords'] = $filters['search'] ? sizeof($ros) : $total;
		$jsonData['filters'] = $filters;

        echo json_encode($jsonData);
	}


	/**
	 * Get A Record
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param registry object ID
	 * @return [JSON] of a single registry object
	 * 
	 */
	public function get_record($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$data['xml'] = $ro->getRif();
		$data['extrif'] = $ro->getExtRif();
		//$data['view'] = $ro->transformForHtml();
		$data['id'] = $ro->id;
		$data['title'] = $ro->getAttribute('list_title');
		$data['attributes'] = $ro->getAttributes();
		$data['revisions'] = $ro->getAllRevisions();

		//preview link for iframe in preview, show published view if published, show draft preview if in draft
		$data['preview_link'] = 'http://demo.ands.org.au/'.$ro->slug;

		$jsonData = array();
		$jsonData['status'] = 'OK';
		$jsonData['ro'] = $data;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}


	function update(){
		$affected_ids = $this->input->post('affected_ids');
		$attributes = $this->input->post('attributes');
		$this->load->model('registry_objects', 'ro');
		foreach($affected_ids as $id){
			$ro = $this->ro->getByID($id);
			foreach($attributes as $a){
				echo 'update '.$id.' set '.$a['name'].' to value:'.$a['value'];
				if($a['name']=='status') $ro->status = $a['value'];
				$ro->save();
			}
		}
	}

	function get_solr_doc($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$ro->enrich();
		//echo $ro->getExtRif();
		//exit();
		//$ro->enrich();
		$ro->update_quality_metadata();
		$solrDoc = $ro->transformForSOLR();

		echo $solrDoc;
	}


	//-----------DEPRECATED AFTER THIS LINE -----------------------//

	/**
	 * Get the edit form of a Record
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param registry object ID
	 * @return [HTML] transformed form from extrif
	 * 
	 */

	public function get_edit_form($id){
		// ro is the alias for the registry object model
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$data['extrif'] = $ro->getExtRif();
		
		$data['preview_link'] = 'http://demo.ands.org.au/'.$ro->slug;
		$data['transform'] = $ro->transformForFORM();
		echo $data['transform'];
		//$this->load->view('registry_object_edit', $data);
	}


	/**
	 * Get the edit form of a Record
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param registry object ID, [POST] custom RIFCS
	 * @return [HTML] transformed form from extrif
	 * 
	 */
	public function get_edit_form_custom($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$rifcs = $this->input->post('rifcs');
		
		$data['transform'] = $ro->transformCustomForFORM($rifcs);
		echo $data['transform'];
	}

	/**
	 * Get a list of records based on the filters
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @package ands/registryobject
	 * @param [POST] Filters(Fields), [POST] sorts, [POST] page
	 * @return [JSON] results of the search
	 * @todo ACL, reponse error handling
	 */
	public function get_records(){
		$fields = $this->input->post('fields');
		$sorts = $this->input->post('sorts');
		$page = $this->input->post('page');

		//Construct the search query
		$q = '';$i = 0;//counter
		if($fields){
			foreach($fields as $field=>$val){
				if($i!=0)$q.=' AND ';
				
				if($field=='list_title'){
					$q .=$field.':(*'.$val.'*)';
				}else{
					$q .=$field.':('.$val.')';
				}
				$i++;
			}
		}
		if($q=='')$q='*:*';

		//Calculate the start and row based on the page, row will be 15 by default
		$start = 0; $row = 15;
		if($page!=1) $start = ($page - 1) * $row;

		//Fire the SOLR search
		/*$this->load->model('solr');
		$fields = array(
			'q'=>$q,'start'=>$start,'indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>$row
		);
		if($sorts && $sorts!=''){
			$fields['sort']=$sorts;
		}
		$facets = '&facet=true&facet.sort=index&facet.mincount=1&facet.field=class&facet.field=status&facet.field=quality_level';
		$solr_search_result = $this->solr->fireSearch($fields, $facets);*/
		
		$this->load->library('solr');
		$this->solr->setOpt('q',$q);
		$this->solr->setOpt('start',$start);
		$this->solr->setOpt('rows',$row);
		$this->solr->setOpt('sort',$sorts);
		$this->solr->setOpt('q',$q);
		$this->solr->setFacetOpt('field', 'class');
		$solr_search_result = $this->solr->executeSearch();

		//Analyze the result
		$solr_header = $solr_search_result->{'responseHeader'};
		$solr_response = $solr_search_result->{'response'};
		$num_found = $solr_response->{'numFound'};
		$facet_fields = $solr_search_result->{'facet_counts'}->{'facet_fields'};


		//Construct the return [JSON] array
		$jsonData = array();

		$items = array();
		if($num_found>0){
			$jsonData['no_more'] = false;
			$solr_result = $solr_response->{'docs'};
			//echo '<pre>';
			foreach($solr_result as $doc){
				$item = array();

				//get all stuffs in there so that we don't miss anything
				foreach($doc as $key=>$attrib){
					$item[$key] = $attrib;
				}

				//fix multi-valued description
				//LOGIC: only if there's a description if there's a brief, use it, if there's none, use first one
				if(isset($doc->{'description_value'})){
					foreach($doc->{'description_type'} as $key=>$type){
						if($type=='brief'){//use it
							$item['description'] = $doc->{'description_value'}[$key];
						}
					}
					if(!isset($item['description'])){
						$item['description'] = $doc->{'description_value'}[0];
					}
				}
				if(!isset($item['description'])){
					$item['description'] = '';
				}
				array_push($items, $item);
			}
			//var_dump($items);
		}else{
			$jsonData['no_more'] = true;//there is no more data, tell the client that
		}

		//Construct the Facet JSON bit
		$facets = array();
		foreach($facet_fields as $field=>$array){
			for($i=0;$i<sizeof($array)-1;$i=$i+2){
				$field_name = $array[$i];
				$value = $array[$i+1];
				$facets[$field][$field_name] = $value;
			}
		}
		
		//Putting them all together and return
		$jsonData['status'] = 'OK';
		$jsonData['q'] = $solr_header;
		$jsonData['items'] = $items;
		$jsonData['num_found'] = $num_found;
		$jsonData['facets'] = $facets;

		$jsonData = json_encode($jsonData);
		echo $jsonData;
		
	}
}