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
		redirect(registry_url());
	}

	public function test()
	{
		$this->load->model('registry_object/registry_objects','ro');
		$this->ro->clearAllFromDatasourceUnsafe(187);
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
				$data['native_format'] = $ro->getNativeFormat($revision);
				if($ro->getNativeFormat($revision) != 'rif')
				{
					$data['naitive_text'] = $ro->getNativeFormatData($revision);
				}
			}else {
				$data['viewing_revision'] = false;
				$data['rif_html'] = $ro->transformForHtml();
				$data['native_format'] = $ro->getNativeFormat();
				if($ro->getNativeFormat($revision) != 'rif')
				{
					$data['naitive_text'] = $ro->getNativeFormatData();
				}
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
		$response['title'] = 'QA Result';
		$scripts = preg_split('/(\)\;)|(\;\\n)/', $result, -1, PREG_SPLIT_NO_EMPTY);
		foreach($scripts as $script)
		{
			$matches = preg_split('/(\"\,\")|(\(\")|(\"\))/', $script.")", -1, PREG_SPLIT_NO_EMPTY);
			if(sizeof($matches) > 3)
				$response[$matches[0]][] = Array('field'=>$matches[1],'message'=>$matches[2],'qafield'=>$matches[3]);
			elseif(sizeof($matches) == 3)
				$response[$matches[0]][] = Array('field'=>$matches[1],'message'=>$matches[2]);
		}
		echo json_encode($response);
	}

	public function manage_table($data_source_id = false){
		acl_enforce('REGISTRY_USER');
		ds_acl_enforce($data_source_id);
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

	public function get_quality_view(){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($this->input->post('ro_id'));
		echo $ro->get_quality_text();
	}

	public function get_native_record($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$data['txt'] = $ro->getNativeFormatData($id);
		$jsonData = json_encode($data);
		echo $jsonData;
	}

	public function get_tag_menu(){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($this->input->post('ro_id'));
		$data['ro'] = $ro;
		$this->load->view('tagging_interface', $data);
	}

	public function tag($action){
		$this->load->model('registry_objects', 'ro');
		$ro_id = $this->input->post('ro_id');
		$tag = $this->input->post('tag');
		$ro = $this->ro->getByID($ro_id);
		$separator = ';;';
		if($action=='add' && $tag!=''){
			if($ro->tag){
				$tags = explode(';;', $ro->tag);
				array_push($tags, $tag);
				$ro->tag = implode(';;', $tags);
				$ro->save();
			}else{
				$ro->tag = $tag;
				$ro->save();
			}
		}else if($action=='remove'){
			$tags = explode(';;', $ro->tag);
			$key = array_search($tag,$tags);
			if($key!==false){
			    unset($tags[$key]);
			}
			$ro->tag = implode(';;', $tags);
			$ro->save();
		}
	}



	function update($all = false){
		$this->load->model('registry_objects', 'ro');
		$attributes = $this->input->post('attributes');
		if(!$all){
			$affected_ids = $this->input->post('affected_ids');
			$attributes = $this->input->post('attributes');
		}else{
			$data_source_id = $this->input->post('data_source_id');
			$select_all = $this->input->post('select_all');
			$ids = $this->ro->getByAttributeDatasource($data_source_id, 'status', $select_all, true, false);
			$affected_ids = array();
			foreach($ids as $id){
				array_push($affected_ids, $id['registry_object_id']);
			}
		}

		foreach($affected_ids as $id){
			$ro = $this->ro->getByID($id);
			foreach($attributes as $a){
				$ro->setAttribute($a['name'], $a['value']);
				if($ro->save()){
					echo 'update '.$ro->id.' set '.$a['name'].' to value:'.$a['value'];
				}else{
					echo 'failed';
				}
			}
		}
	}

	function delete(){
		$affected_ids = $this->input->post('affected_ids');
		$this->load->model('registry_objects', 'ro');
		foreach($affected_ids as $id){
			$ro = $this->ro->getByID($id);
			$this->ro->deleteRegistryObject($ro);
		}
	}

	function get_solr_doc($id){
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$ro->enrich();
		echo $ro->getExtRif();
		//exit();
		//$ro->enrich();
		$ro->update_quality_metadata();
		$solrDoc = $ro->transformForSOLR();

		//echo $solrDoc;
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