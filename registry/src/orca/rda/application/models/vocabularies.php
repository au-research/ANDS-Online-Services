<?php
/**
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
***************************************************************************
*
**/
?>
<?php
class Vocabularies extends CI_Model {

	var $search_params = '';
    function __construct() {
        parent::__construct();
    }

    //primary function to get the tree containing everything
    function getBigTree($vocabs, $broader=array(), $params='', $for='browse'){
    	//var_dump($params);
    	if($for=='browse'){
    		$bigTree = '';
	    	$bigTree .='<div id="vocab-browser">';
				$bigTree .='<ul>';
					$bigTree .='<li id="rootNode">';
					$bigTree .='<a href="#">Vocabularies</a>';
					$bigTree .='<ul>';
			    	//var_dump($vocabs);
			    	$order = 1; //additional parameter to add to each tree for identification purpose
			    	foreach($vocabs as $vocab=>$vocab_uri){
			    		//var_dump($vocab);
			    		if($tree = $this->getTree($vocab, $vocab_uri, $order, $params)){
			    			$bigTree .= $tree;
			    			$order++;
			    		}else{
			    			$bigTree .='<li><a>Invalid Vocabulary URL</a></li>';
			    		}
			    	}
			    	$bigTree.='</ul>';
			    	$bigTree.='</li>';
		    	$bigTree.='</ul>';
	    	$bigTree.='</div>';
	    	return $bigTree;
    	}else{
    		$bigTree = '';
	    	$bigTree .='<div id="vocab-browser">';
	    	$bigTree .='<ul>';
	    	foreach($vocabs as $vocab=>$vocab_uri){
	    		//var_dump($vocab);
	    		if($tree = $this->getTree($vocab, $vocab_uri, 1, $params)){
	    			$bigTree .= $tree;
	    		}else{
	    			$bigTree .='<li><a>Invalid Vocabulary URL</a></li>';
	    		}
	    	}
	    	$bigTree .='</ul>';
	    	$bigTree.='</div>';
    		return $bigTree;
    	}
		
    }



    //secondary function to get the HTML representation of a single tree
	function getTree($vocab, $vocab_uri, $order, $params){
		//echo $vocab_uri;
		//$json = json_decode($this->getResource($vocab_uri));
		if($json=json_decode($this->getResource($vocab_uri))){
			$tree = $this->getVocabTree($json, $vocab_uri);
			return $this->formatVocabTree($tree, $order, $vocab, $params);
		}else return false;
	}

	function getConceptTree($vocabs, $uri, $vocab, $search_params=''){
		$vocab_uri = $vocabs[$vocab];
		$concept_uri = $uri;
		$content = $this->getNarrower($vocab_uri['resolvingService'], $concept_uri);
		$json = json_decode($content);
		//var_dump($json);

		$r='';
		foreach($json->{'result'}->{'items'} as $item){
			//var_dump($item);
			$notation = explode('/',$item->{'_about'});
			$length = sizeof($notation);
			$notation = $notation[$length-1];
			$hasNarrower = $this->hasNarrower($vocab_uri, $item->{'_about'});
			if(!$hasNarrower){
				$class = 'jstree-leaf';
			}else $class = 'jstree-closed';
			$param = array(
				'selected_class' => '',
				'vocab_uri' => $item->{'_about'},
				'notation' => $notation,
				'vocab' => $vocab,
				'prefLabel' => $item->{'prefLabel'}->{'_value'},
				'num' => $this->getNumCollections($item->{'_about'}, $search_params)
			);
			
			$r.='<li class="'.$class.'">'.$this->htmlListItem($param);
				if($hasNarrower){
					$r.='<ul>';
					$r.='<li><a class="jstree-loading">Loading...</a></li>';
					$r.='</ul>';
				}
			$r.='</li>';
		}
		echo $r;
	}

	function getTopConceptsFacet($vocabs, $params){
		//business rules: only get it from anzsrc-for
		$vocab_uri = $vocabs['anzsrc-for'];
		if($json=json_decode($this->getResource($vocab_uri))){
			$tree = $this->getVocabTree($json, $vocab_uri, $params);

			//var_dump($tree);
			return $tree;
			//return $this->formatVocabTree($tree, $order, $vocab, $params);
		}else return false;
	}

	//this function spits out a tree, drill down to the selected node
	function sloadTree($vocabs, $selected, $broader){
		$vocab = $selected['vocab'];
		$vocab_uri = $vocabs[$vocab];

		$json = json_decode($this->getResource($vocab_uri));
		//var_dump($broader);
		$tree = array();
		foreach($json->{'result'}->{'primaryTopic'}->{'hasTopConcept'} as $concept){
			//$tree['topConcept'][] = $concept->{'_about'};
			$concept_uri = $concept->{'_about'};
			$uri['resolvingService']=$vocab_uri['resolvingService'];
			$uri['uriprefix']=$concept->{'_about'};
			$resolved_concept = json_decode($this->getResource($uri));
			//var_dump($resolved_concept);
			//echo($resolved_concept->{'result'}->{'primaryTopic'}->{'notation'});
			$notation = $resolved_concept->{'result'}->{'primaryTopic'}->{'notation'};
			$c['notation'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'notation'};
			$c['prefLabel'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
			$c['uri'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'_about'};

			if($broader){
				if(in_array($c['uri'], $broader)){
					$broader = array_diff($broader, array($c['uri']));
					$c['narrower'] = $this->drilldown($vocab_uri, $c['uri'], $broader, $selected['uri']);
				}else $c['narrower']=false;
			}else{
				$c['narrower']=false;
			}


			$tree['topConcepts'][] = $c;
		}
		//var_dump($tree);
		$r='';
		foreach($tree['topConcepts'] as $key=>$concept){
			if($concept['narrower']){
				$hasNarrower = true;
				$class = 'jstree-open';
			}else{
				$hasNarrower = false;
				$hasNarrowerConcepts = $this->hasNarrower($vocab_uri, $concept['uri']);
				if(!$hasNarrowerConcepts){
					$class = 'jstree-leaf';
				}else $class = 'jstree-closed';
			}
			$selected_class ='';
			if($selected['uri']==$concept['uri']){
				$selected_class.=' jstree-clicked';
			}else $selected_class = '';

			$param = array(
				'selected_class' => $selected_class,
				'vocab_uri' => $concept['uri'],
				'notation' => $concept['notation'],
				'vocab' => $vocab,
				'prefLabel' => $concept['prefLabel'],
				'num' => $this->getNumCollections($concept['uri'])
			);
			
			$r.='<li class="'.$class.'">'.$this->htmlListItem($param);
			if($hasNarrower){
				$r.= '<ul>'.$this->htmlDrillDown($vocab, $concept['narrower'], $selected, $vocab_uri).'</ul>';
			}else{
				if($hasNarrowerConcepts){
					$r.='<ul><li><a class="jstree-loading">Loading...</a></li></ul>';
				}
			}
			$r.='</li>';
		}
		echo $r;
	}

	//This function takes in an array of stuff to be printed, and then spits out the html representation of a list item
	function htmlListItem($params){
		/*
			$param = array(
				'selected_class' => '',
				'vocab_uri' => $concept['uri'],
				'notation' => $concept['notation'],
				'vocab' => $vocab,
				'prefLabel' => $concept['prefLabel'],
				'num' => $this->getNumCollections($concept['uri'])
			)
			htmlListItem($param);
		*/
		$disableClass = '';$title = $params['prefLabel'].' ('.$params['num'].')';
		if($params['num']==0){
			$disableClass = 'disabled-link';
			$title = "There's no collection associated with this subject";
		}
		$r = '';
		$r.='	<a href="javascript:void(0);" 
					class="getConcept '.$params['selected_class'].' '.$disableClass.'"  
					uri="'.$params['vocab_uri'].'" 
					notation="'.$params['notation'].'"
					vocab="'.$params['vocab'].'"
					total="'.$params['num'].'"
					prefLabel="'.$params['prefLabel'].'"
					'//title="'.$title.'"
					.'
					>
					'.$params['prefLabel'].' ('.$params['num'].')'.'
				</a>';
		return $r;
	}

//jstree-clicked
	function htmlDrillDown($vocab, $concept_tree, $selected, $vocab_uri){
		//var_dump($concept_tree);
		if(sizeof($concept_tree)==0) return false;
		$r='';
		foreach($concept_tree as $concept){
			if($concept['narrower']){
				$hasNarrower = true;
				$class = 'jstree-open';
			}else{
				$hasNarrower = false;
				$hasNarrowerConcepts = $this->hasNarrower($vocab_uri, $concept['uri']);
				if(!$hasNarrowerConcepts){
					$class = 'jstree-leaf';
				}else $class = 'jstree-closed';
			}
			$selected_class ='';
			if($selected['uri']==$concept['uri']){
				$selected_class.=' jstree-clicked';
			}else $selected_class = '';

			$param = array(
				'selected_class' => $selected_class,
				'vocab_uri' => $concept['uri'],
				'notation' => $concept['notation'],
				'vocab' => $vocab,
				'prefLabel' => $concept['prefLabel'],
				'num' => $this->getNumCollections($concept['uri'])
			);

			$r.='<li class="'.$class.'">'.$this->htmlListItem($param);
			if($hasNarrower){
				$r.= '<ul>'.$this->htmlDrillDown($vocab, $concept['narrower'], $selected, $vocab_uri).'</ul>';
			}else{
				if($hasNarrowerConcepts){
					$r.='<ul><li><a class="jstree-loading">Loading...</a></li></ul>';
				}

			}
			$r.='</li>';
		}
		return $r;
	}

	function drilldown($vocab_uri, $concept_to_drill, $broader, $selected_uri){
		if(sizeof($broader)==0){
			return false;
		}
		$tree = array();
		$broader = array_diff($broader, array($concept_to_drill));
		$uri['resolvingService'] = $vocab_uri['resolvingService'];
		$uri['uriprefix'] = $concept_to_drill;
		$json = json_decode($this->getNarrower($uri['resolvingService'], $concept_to_drill));
		foreach($json->{'result'}->{'items'} as $key=>$item){
			$i['prefLabel'] = $item->{'prefLabel'}->{'_value'};
			$i['uri'] = $item->{'_about'};
			if($i['uri']==$selected_uri){
				$i['selected'] = true;
			}else $i['selected'] = false;
			$tree[$key] = $i;
			if(in_array($i['uri'], $broader)){
				if($this->drilldown($vocab_uri, $i['uri'], $broader, $selected_uri)){
					$tree[$key]['narrower'] = $this->drilldown($vocab_uri, $i['uri'], $broader, $selected_uri);
				}else{
					$tree[$key]['narrower'] = false;
				}
			}else{
				$tree[$key]['narrower'] = false;
			}
		}
		//var_dump($tree);
		return $tree;
	}


	function getBroader($vocabs, $concept_uri, $vocab, $parents=array()){
		$vocab_uri = $vocabs[$vocab]['resolvingService'];
		//echo $vocab_uri;
		$uri['resolvingService'] = $vocab_uri;
		$uri['uriprefix'] = $concept_uri;
		$content = $this->getResource($uri);
		$json = json_decode($content);

		if(isset($json->{'result'}->{'primaryTopic'}->{'broader'}->{'_about'})){
			$broader_uri = $json->{'result'}->{'primaryTopic'}->{'broader'}->{'_about'};
			array_push($parents, $broader_uri);
			if($this->getBroader($vocabs, $broader_uri, $vocab, $parents)){
				$parents = $this->getBroader($vocabs, $broader_uri, $vocab, $parents);
			}
			return $parents;
		}else{
			return false;
		}
	}

	//returns the resources of a certain concept
	function getConcept($vocabs, $concept_uri, $vocab){
		$vocab_uri = $vocabs[$vocab]['resolvingService'];
		//echo $vocab_uri;
		$uri['resolvingService'] = $vocab_uri;
		$uri['uriprefix'] = $concept_uri;
		$content = $this->getResource($uri);
		$json = json_decode($content);
		return $json;
	}

	//determine if a concept as a narrower
	function hasNarrower($vocab_uri, $about){
		$content = $this->getNarrower($vocab_uri['resolvingService'], $about);
		$json = json_decode($content);
		if(sizeof($json->{'result'}->{'items'})>0){
			return true;
		}else return false;
	}

	//returns the HTML formatting of an array tree (returns by the function getVocabTree)
	function formatVocabTree($tree, $order, $vocab, $search_params){
		//var_dump($search_params);
		//var_dump($tree);
		if($order==1){
			$class='jstree-open';
		}else{
			//$class='jstree-open';
			$class='jstree-closed';
		}
		$r='';
		$r.='<li class="'.$class.' conceptRoot" order="'.$order.'" vocab="'.$vocab.'"><a href="#">'.$tree['topLabel'].'</a>';
		$r.='<ul>';
		foreach($tree['topConcepts'] as $topConcept){
			$param = array(
				'selected_class' => '',
				'vocab_uri' => $topConcept['uri'],
				'notation' => $topConcept['notation'],
				'vocab' => $vocab,
				'prefLabel' => $topConcept['prefLabel'],
				'num' => $this->getNumCollections($topConcept['uri'], $search_params)
			);
			
			$r.='<li class="closed">'.$this->htmlListItem($param);
				$r.='<ul>';
				$r.='<li><a class="jstree-loading">Loading...</a></li>';
				$r.='</ul>';
			$r.='</li>';
		}
		$r.='</li></ul>';
		return $r;
	}

	//returns an array (the tree, ready for formatting of all the top concepts)
	function getVocabTree($json, $vocab_uri, $params=''){
		$tree = array();
		$tree['topLabel']= $json->{'result'}->{'primaryTopic'}->{'altLabel'}->{'_value'};

		foreach($json->{'result'}->{'primaryTopic'}->{'hasTopConcept'} as $concept){
			//$tree['topConcept'][] = $concept->{'_about'};
			$concept_uri = $concept->{'_about'};
			$uri['resolvingService']=$vocab_uri['resolvingService'];
			$uri['uriprefix']=$concept->{'_about'};
			$resolved_concept = json_decode($this->getResource($uri));
			//var_dump($resolved_concept);
			//echo($resolved_concept->{'result'}->{'primaryTopic'}->{'notation'});
			$notation = $resolved_concept->{'result'}->{'primaryTopic'}->{'notation'};
			$c['notation'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'notation'};
			$c['prefLabel'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
			$c['uri'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'_about'};
			$c['collectionNum'] = $this->getNumCollections($c['uri'], $params);

			//var_dump($c);
			$tree['topConcepts'][] = $c;
		}
		//var_dump($json->{'result'}->{'primaryTopic'}->{'hasTopConcept'});
		return $tree;
	}



	//execute the get resources, this is to resolve a single concept
	function getResource($vocab_uri){
		$curl_uri = $vocab_uri['resolvingService'].'resource.json?uri='.$vocab_uri['uriprefix'];
		//echo $curl_uri;
		$ch = curl_init();
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$curl_uri);//post to SOLR
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl
    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl
		return $content;
	}

	//execute the get narrower terms of a concept
	function getNarrower($vocab_uri, $concept_uri){
		$resolve_uri = $vocab_uri.'concept/narrower.json?uri='.$concept_uri;
		//echo $resolve_uri;
		$ch = curl_init();
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$resolve_uri);//post to SOLR
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl
    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl
		return $content;
	}

	//execute the get labelcontain to search for all concept has a preflabel equals
	//this searches all available vocabs and returns a big array
	function labelContain($vocabs, $term){
		$result = array();
		foreach($vocabs as $key=>$vocab){
			$vocab_uri = $vocab['resolvingService'];
			$resolve_uri = $vocab_uri.'concept.json?anycontains='.$term;
			//echo $resolve_uri;
			$ch = curl_init();
	    	//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$resolve_uri);//post to SOLR
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
	    	$content = curl_exec($ch);//execute the curl
	    	//echo 'json received+<pre>'.$content.'</pre>';
			curl_close($ch);//close the curl
			//var_dump($content);

			$results = array();
			$content = json_decode($content);
			//var_dump($content->{'result'}->{'items'});
			foreach($content->{'result'}->{'items'} as $item){
				$r['prefLabel'] = $item->{'prefLabel'}->{'_value'};
				$r['uri']=$item->{'_about'};
				array_push($results, $r);
			}
			//var_dump($results);
			$result[$key] = $results;
		}
		return $result;
	}

	//get the total number of collection that has this subject_vocab_uri
	//this is from SOLR
	function getNumCollections($uri, $search_params=''){
		if($search_params!=''){
			$q = $search_params. '(+subject_vocab_uri:("'.$uri.'") OR +broader_subject_vocab_uri:("'.$uri.'")) +status:PUBLISHED';
		}else{
			$q = '(+subject_vocab_uri:("'.$uri.'") OR +broader_subject_vocab_uri:("'.$uri.'")) +class:collection +status:PUBLISHED';
		}
		$fields = array(
			'q'=>$q,'version'=>'2.2','start'=>'0','rows'=>'1', 'wt'=>'json',
			'fl'=>'hash_key'
		);
		/*prep*/
		$fields_string='';
		//foreach($fields as $key=>$value) { $fields_string .= $key.'='.str_replace("+","%2B",$value).'&'; }//build the string
		foreach($fields as $key=>$value) {
			$fields_string .= $key.'='.$value.'&';
		}//build the string
    	$fields_string .= '&facet=true&facet.field=class';//add the facet bits
    	$fields_string = rtrim($fields_string,'&');

    	//echo $fields_string;

    	$ch = curl_init();
    	$solr_url = $this->config->item('solr_url');
    	//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$solr_url.'select');//post to SOLR
		curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
    	$content = curl_exec($ch);//execute the curl

    	//echo 'json received+<pre>'.$content.'</pre>';
		curl_close($ch);//close the curl



		$json = json_decode($content);
		$num = $json->{'response'}->{'numFound'};
		//echo  "*********".$content;
		return $num;
	}
}
?>
