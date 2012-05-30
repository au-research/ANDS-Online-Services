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
    function __construct() {
        parent::__construct();
    }

    //primary function to get the tree containing everything
    function getBigTree($vocabs){

    	$bigTree = '';
    	$bigTree .='<div id="vocab-browser">';
			$bigTree .='<ul>';
				$bigTree .='<li id="rootNode">';
				$bigTree .='<a href="#">ANDS Vocab Service</a>';
				$bigTree .='<ul>';
		    	//var_dump($vocabs);
		    	$order = 1; //additional parameter to add to each tree for identification purpose
		    	foreach($vocabs as $key=>$vocab){
		    		//var_dump($vocab);
		    		if($tree = $this->getTree($key, $vocab, $order)){
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
    }

    //secondary function to get the HTML representation of a single tree
	function getTree($vocab, $vocab_uri, $order){
		//echo $vocab_uri;
		//$json = json_decode($this->getResource($vocab_uri));
		if($json=json_decode($this->getResource($vocab_uri))){
			$tree = $this->getVocabTree($json, $vocab_uri);
			//var_dump($tree);
			return $this->formatVocabTree($tree, $order, $vocab);
		}else return false;
	}

	function getConceptTree($vocabs, $num, $vocab){
		$vocab_uri = $vocabs[$vocab];
		$concept_uri = $vocab_uri['uriprefix'].$num;
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
			$r.='<li><a href="javascript:void(0);" class="getConcept" notation="'.$notation.'" vocab="'.$vocab.'">'.$item->{'prefLabel'}->{'_value'}.'</a>';
				if($hasNarrower){
					$r.='<ul>';
					$r.='<li><a class="jstree-loading">Loading...</a></li>';
					$r.='</ul>';
				}
			$r.='</li>';
		}
		echo $r;
	}

	function getConcept($vocabs, $num, $vocab){
		$vocab_uri = $vocabs[$vocab]['resolvingService'];
		//echo $vocab_uri;
		$concept_uri = $vocabs[$vocab]['uriprefix'].$num;
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
	function formatVocabTree($tree, $order, $vocab){
		//var_dump($tree);
		if($order==1){
			$class='jstree-open';
		}else $class='jstree-closed';
		$r='';
		$r.='<li class="'.$class.' conceptRoot" order="'.$order.'"><a href="#">'.$tree['topLabel'].'</a>';
		$r.='<ul>';
		foreach($tree['topConcepts'] as $topConcept){
			$r.='<li class="closed"><a href="javascript:void(0);" class="getConcept" notation="'.$topConcept['notation'].'" vocab="'.$vocab.'">'.$topConcept['prefLabel'].'</a>';
				$r.='<ul>';
				$r.='<li><a class="jstree-loading">Loading...</a></li>';
				$r.='</ul>';
			$r.='</li>';
		}
		$r.='</li></ul>';
		return $r;
	}

	//returns an array (the tree, ready for formatting of all the top concepts)
	function getVocabTree($json, $vocab_uri){
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
			if(isset($resolved_concept->{'result'}->{'primaryTopic'}->{'narrower'})){
				$c['narrower'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'narrower'};
			}else{
				$c['narrower'] = 'noNarrower';
			}
			
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
}
?>
