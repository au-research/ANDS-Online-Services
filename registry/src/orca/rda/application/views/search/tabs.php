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
			//print_r($json_tab->{'facet_counts'}->{'facet_fields'});
            $object_class = $json_tab->{'facet_counts'}->{'facet_fields'}->{'class'};
            $all_numFound = number_format($json_tab->{'response'}->{'numFound'});
            
            
  
            $classes = array();// array to stores the class
			for($i=0;$i<sizeof($object_class)-1 ;$i=$i+2){
			    $classes[$object_class[$i]] = number_format($object_class[$i+1]);
			}
			
			//The all tab filter
            if($classFilter=='All'){
               echo '<li><a href="javascript:void(0);" id="All" name="All" title="View All '.$all_numFound.' results" class="current tab">All</a>';
            }else echo '<li><a href="javascript:void(0);" id="All" name="All" title="View All '.$all_numFound.' results" class="tab">All</a>';
            
			$order = array("collection", "party", "activity", "service");//the order on the tabs
		
			foreach($order as $c){
				if(isset($classes[$c])){
                //echo $c. ': '.$classes[$c];
                $string = '<li>';
                $string.= '<a href="javascript:void(0);" id="'.$c.'" name="'.$c.'"';
                $string.= ' class="';
                
                if($classFilter == $c){
                	$string.='current ';
                }
                
                if($classes[$c]==0){
                	$string.='zero ';
                }
                
                $str = '';
                switch($c){
                	case 'collection':$str='Collections';break;
                	case 'party':$str='Parties';break;
                	case 'activity':$str='Activities';break;
                	case 'service':$str='Services';break;
                }
                
                $string.='tab"';
         		
                $string.= ' title="'.$classes[$c].' results">'.$str.'</a></li>';
                echo $string;
				}
			}
			
			
?>