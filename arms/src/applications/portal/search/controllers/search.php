<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MX_Controller {

	function index(){
		$data['title']='Research Data Australia - Search';
		$data['scripts'] = array('search','infobox');
		$data['js_lib'] = array('google_map', 'range_slider','vocab_widget','qtip');

		$this->load->library('stats');
		$this->stats->registerPageView();		
		
		$this->load->view('search_layout', $data);
	}

	function filter(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = $this->solr_search($this->input->post('filters'), true);
		//return the result to the client
		echo json_encode($data);
	}

	function solr_search($filters, $include_facet = true){
		$this->load->library('solr');

		$page = 1; $start = 0;
		$pp = ( isset($filters['rows']) ? (int) $filters['rows'] : 15 );

		$this->solr->setOpt('rows', $pp);
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('q.alt', '*:*');
		$this->solr->setOpt('mm', '2'); //minimum should match optional clause
		$this->solr->setOpt('fl', '*, score'); //we'll get the score as well

		//if there's no query to search, eg. rda browsing
		if (!isset($filters["q"])){
			$this->solr->setOpt('q', '*:*');
			$this->solr->setOpt('sort', 'score desc, s_list_title asc');
		}

		//optional facets return, true for rda search
		if($include_facet){
			$facets = array(
				'class' => 'Class',
				'group' => 'Contributed By',
				'license_class' => 'Licence',
				'type' => 'Type',
			);
			foreach($facets as $facet=>$display){
				$this->solr->setFacetOpt('field', $facet);
			}
			$this->solr->setFacetOpt('mincount','1');
			$this->solr->setFacetOpt('limit','100');
			$this->solr->setFacetOpt('sort','count');
		}

		//boost
		$this->solr->setOpt('bq', 'id^1 group^0.8 display_title^0.5 list_title^0.5 fulltext^0.2 (*:* -group:("Australian Research Council"))^3  (*:* -group:("National Health and Medical Research Council"))^3');
		// $this->solr->setOpt('bq', '(*:* -group:("Australian Research Council"))^3  (*:* -group:("National Health and Medical Research Council"))^3');
		if($filters){
			foreach($filters as $key=>$value){
				$value = rawurldecode($value);
				switch($key){
					case 'rq':
						$this->solr->clearOpt('defType');//returning to the default deftype
						$this->solr->setOpt('q', $value);
					break;
					case 'q': 
						$value = escapeSolrValue($value);
						$data['search_term'] = $value;
						$this->solr->setOpt('q', 'fulltext:('.$value.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $value).')');
					break;
					case 'p': 
						$page = (int)$value;
						if($page>1){
							$start = $pp * ($page-1);
						}
						$this->solr->setOpt('start', $start);
						break;
					case 'class': 
						if($value!='all') $this->solr->setOpt('fq', '+class:('.$value.')');
						break;
					case 'group': 
						if($value!='all') $this->solr->setOpt('fq', '+group:("'.$value.'")');
						break;
					case 'type': 
						if($value!='all') $this->solr->setOpt('fq', '+type:("'.$value.'")');
						break;
					case 'subject_value_resolved': 
						$this->solr->setOpt('fq', '+subject_value_resolved:("'.$value.'")');
						break;
					case 's_subject_value_resolved': 
						$this->solr->setOpt('fq', '+s_subject_value_resolved:("'.$value.'")');
						break;
					case 'subject_vocab_uri':
						$this->solr->setOpt('fq', '+subject_vocab_uri:("'.$value.'")');
						break;
					case 'temporal':
						$date = explode('-', $value);
						$this->solr->setOpt('fq','+earliest_year:['.$date[0].' TO *]');
						$this->solr->setOpt('fq','+latest_year:[* TO '.$date[1].']');
						break;
					case 'license_class': 
						$this->solr->setOpt('fq','+license_class:("'.$value.'")');
						break;
					case 'spatial':
						$this->solr->setOpt('fq','+spatial_coverage_extents:"Intersects('.$value.')"');
						break;
					case 'map':
						$this->solr->setOpt('fq','+spatial_coverage_area_sum:[0.00001 TO *]');
						if (isset($filters['rows']) && is_numeric($filters['rows'])){
						    $this->solr->setOpt('rows', $filters['rows']);
						}else{
						    $this->solr->setOpt('rows', 1500);
						}
						$this->solr->setOpt('fl', 'id,spatial_coverage_area_sum,spatial_coverage_centres,spatial_coverage_extents,spatial_coverage_polygons');
						break;
				}
			}
		}

		$this->solr->executeSearch();

		//if no result is found, forsake the edismax and thus forsake the boost query and search again
		// unicode characters
		if($this->solr->getNumFound()==0){
			$this->solr->clearOpt('defType');
			$this->solr->clearOpt('bq');
			$this->solr->executeSearch();
		}

		//if still no result is found, do a fuzzy search, store the old search term and search again
		if($this->solr->getNumFound()==0){
			$new_search_term_array = explode(' ', $data['search_term']);
			$new_search_term='';
			foreach($new_search_term_array as $c ){
				$new_search_term .= $c.'~0.8 ';
			}
			// $new_search_term = $data['search_term'].'~0.7';
			$this->solr->setOpt('q', 'fulltext:('.$new_search_term.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $new_search_term).')');
			$this->solr->executeSearch();
			if($this->solr->getNumFound() > 0){
				$data['fuzzy_result'] = true;
			}
		}

		//give up, cry a lot
		if($this->solr->getNumFound()==0){
			$data['no_result'] = true;
		}else{
			//continue on life
			$data['has_result'] = true;
		}

		//continue on life
		
		/**
		 * Getting the results back
		 */
		$data['result'] = $this->solr->getResult();
		$data['numFound'] = $this->solr->getNumFound();
		$data['solr_header'] = $this->solr->getHeader();
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;

		/**
		 * House cleaning on the facet_results
		 */
		$data['facet_result'] = array();
		foreach($facets as $facet=>$display){
			$facet_values = array();
			$solr_facet_values = $this->solr->getFacetResult($facet);
			if(count($solr_facet_values)>0){
				if(isset($filters['facetsort']) && $filters['facetsort']=='alpha') uksort($solr_facet_values, "strnatcasecmp");
				foreach($solr_facet_values AS $title => $count){
					if($count>0){
						$facet_values[] = array(
							'title' => $title,
							'count' => $count
						);
					}
				}
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
				if($facet=='class'){
					$data['selected_tab'] = $facet;
				}
			}else if(isset($filters[$facet])){//for selected facet, always display this
				$facet_values[] = array('title'=>$filters[$facet], 'count'=>0);
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
			}
		}

		/**
		 * Pagination prep
		 * Page: {{page}}/{{totalPage}} |  <a href="#">First</a>  <span class="current">1</span>  <a href="#">2</a>  <a href="#">3</a>  <a href="#">4</a>  <a href="#">Last</a>
		 */
		$range = 3;
		$pagi = '';
		$pagi .= '<div class="page_navi">';
		$pagi .=  'Page: '.$page.'/'.ceil($data['numFound'] / $pp).'   |  ';
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="1">First</a>';
		// if($page > 1){
		// 	$pagi .=  '<a href="javascript:void(0);"> &lt;</a>';
		// }
		for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
			if (($x > 0) && ($x <= ceil($data['numFound'] / $pp))) { //if it's valid
				if($x==$page){//if we're on current
					$pagi .=  '<a href="javascript:;" class="current filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}else{//not current
					$pagi .=  '<a href="javascript:;" class="filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}
			}
		}
		// if not on last page, show Next
		// if($page < ceil($data['numFound'] / $pp)){
		// 	$pagi .=  '<a href="javascript:void(0);">&gt;</a>';
		// }
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="'.ceil($data['numFound'] / $pp).'">Last</a>';
		$pagi .=  '</div>';
		$data['pagination'] = $pagi;

		$data['options'] = $this->solr->getOptions();
		$data['facet_counts'] = $this->solr->getFacet();
		$data['fieldstrings'] = $this->solr->constructFieldString();




		return $data;
	}

	function suggest(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$search = $this->input->get('q');
		$terms = $this->stats->getSearchSuggestion($search);
		echo json_encode($terms);
	}

	function registerSearchTerm(){
		$search_term = $this->input->get('q');
		$this->stats->registerSearchTerm($this->input->post('term'),$this->input->post('num_found'));
	}

	function getAllSubjects($vocab_type){
		$filters = $this->input->post('filters');
		$subjects_categories = $this->config->item('subjects_categories');
		$list = $subjects_categories[$vocab_type]['list'];
		$result = array();
		foreach($list as $l){
			$result_type = $this->getAllSubjectsForType($l, $filters);
			$result_list = (isset($result_type['list']) ? $result_type['list'] : array());
			$result = array_merge($result, $result_list);
		}

		$azTree = array();
		$azTree['0-9']=array('subjects'=>array(), 'total'=>0, 'display'=>'0-9');
		foreach(range('A', 'Z') as $i){$azTree[$i]=array('subjects'=>array(), 'total'=>0, 'display'=>$i);}

		foreach($result as $r){
			if(ctype_alnum($r['value'])){
				$first = strtoupper($r['value'][0]);
				if(is_numeric($first)){$first='0-9';}
				$azTree[$first]['total']++;
				array_push($azTree[$first]['subjects'], $r);
			}
		}
		$data['azTree'] = $azTree;
		$this->load->view('subjectfacet-tree', $data);
	}

	function getAllSubjectsForType($type, $filters){
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('mm', '3');
		$this->solr->setOpt('q.alt', '*:*');
		$this->solr->setOpt('fl', '*, score');
		$this->solr->setOpt('qf', 'id^1 group^0.8 display_title^0.5 list_title^0.5 fulltext^0.2');
		$this->solr->setOpt('rows', '0');

		$this->solr->clearOpt('fq');

		if($filters){
            foreach($filters as $key=>$value){
                $value = urldecode($value);
                switch($key){
                    case 'q': 
                        $this->solr->setOpt('q', "+fulltext:(*" . $value . "*)");
                        break;
                    case 'class': 
                        if($value!='all') $this->solr->addQueryCondition('+class:("'.$value.'")');
                        break;
                    case 'group': 
                        $this->solr->addQueryCondition('+group:("'.$value.'")');
                        break;
                    case 'type': 
                        $this->solr->addQueryCondition('+type:'.$value);
                        break;
                    case 's_subject_value_resolved': 
						$this->solr->addQueryCondition('+s_subject_value_resolved:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'subject_vocab_uri':
						$this->solr->addQueryCondition('+subject_vocab_uri:("'.$value.'")');
						$filteredSearch = true;
						break;
					case 'temporal':
						$date = explode('-', $value);
						$this->solr->addQueryCondition('+earliest_year:['.$date[0].' TO *]');
						$this->solr->addQueryCondition('+latest_year:[* TO '.$date[1].']');
						$filteredSearch = true;
						break;
                    case 'license_class': 
                        $this->solr->addQueryCondition('+license_class:("'.$value.'")');
                        break;             
                    case 'spatial':
                        $this->solr->addQueryCondition('+spatial_coverage_extents:"Intersects('.$value.')"');
                        break;
                    case 'map':
						$this->solr->addQueryCondition('+spatial_coverage_area_sum:[0.00001 TO *]');
						break;
                }
            }
        }
        $this->solr->addQueryCondition('+subject_type:"'.$type.'"');
		$this->solr->setFacetOpt('pivot', 'subject_type,subject_value_resolved');
		$this->solr->setFacetOpt('sort', 'subject_value_resolved');
		$this->solr->setFacetOpt('limit', '25000');
		$content = $this->solr->executeSearch();
		$facets = $this->solr->getFacet();
		$facet_pivots = $facets->{'facet_pivot'}->{'subject_type,subject_value_resolved'};
		//echo json_encode($facet_pivots);
		$result = array();
		$result[$type] = array();
		
		foreach($facet_pivots as $p){
			if($p->{'value'}==$type){
				$result[$type] = array('count'=>$p->{'count'}, 'list'=>array());
				foreach($p->{'pivot'} as $pivot){
					array_push($result[$type]['list'], array('value'=>$pivot->{'value'}, 'count'=>$pivot->{'count'}));
				}
				$result[$type]['size'] = sizeof($result[$type]['list']);
				// echo json_encode($p->{'pivot'});
			}
		}
		return $result[$type];
	}

	function getsubjectfacet(){
		$filters = $this->input->post('filters');
		$data['subjectType'] = $this->input->post('subjectType');
		$this->load->view('subjectfacet', $data);
	}

	function getTopLevel(){
		$this->load->library('vocab');
		$filters = $this->input->post('filters');
		echo json_encode($this->vocab->getTopLevel('anzsrc-for', $filters));
	}
}