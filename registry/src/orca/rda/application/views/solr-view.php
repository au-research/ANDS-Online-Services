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
<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>

<div id="item-view" class="top-corner">
	<!--  the following hidden elements dfine content to be used in further ajax calls --> 
	<span id="originating_source" class="hide"><?php echo $doc->{'originating_source'};?></span>
    <div id="group_value" class="hide"><?php echo $doc->{'group'};?></div>
    <div id="datasource_key" class="hide"><?php echo $doc->{'originating_source'};?></div>
    <div id="class" class="hide"><?php echo $doc->{'class'};?></div>       
    <span id="key" class="hide"><?php echo $doc->{'key'};?></span>
    
	<div id="top" class="top-corner">
		<ul id="breadcrumb" class="top-corner">
				<li><a href="<?php echo base_url();?>" class="crumb">Home</a></li>
				<li><a href="<?php echo base_url();?>search/browse/<?php echo $doc->{'group'};?>" class="crumb"><?php echo $doc->{'group'};?></a></li>
				<li><a href="<?php echo base_url();?>search/browse/<?php echo $doc->{'group'};?>/<?php echo $doc->{'class'};?>" class="crumb"><?php echo ucfirst($doc->{'class'});?></a></li>
				<li><?php echo substr($doc->{'displayTitle'}, 0, 40);?>...</li>
				
				<div id="breadcrumb-corner">
					 <!-- AddToAny BEGIN -->   
	       			<div class="a2a_kit a2a_default_style no_print" id="share">
	        		<a class="a2a_dd" href="http://www.addtoany.com/share_save">Share</a>
	        		<span class="a2a_divider"></span>
	       			 <a class="a2a_button_linkedin"></a>
	        		<a class="a2a_button_facebook"></a>
	        		<a class="a2a_button_twitter"></a>
	        		<a class="a2a_button_wordpress"></a>
	        		<a class="a2a_button_stumbleupon"></a>
	        		<a class="a2a_button_delicious"></a>
	        		<a class="a2a_button_digg"></a>
	        		<a class="a2a_button_reddit"></a>
	        		<a class="a2a_button_email"></a>
	        		</div>
	        		<script type="text/javascript">
	        		var a2a_config = a2a_config || {};
	        		a2a_config.linkname = "Research Data Australia.";
	        		a2a_config.linknote = "Research Data Australia.";
	        		</script>
	    
	        		<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
	      
	        		<!-- AddToAny END -->  
	        		
	        		<a href="<?php echo base_url();?>view/printview/?key=<?php echo $doc->{'key'};?>"><img id="print_icon" src="<?php echo base_url();?>img/1313027722_print.png"/></a>
				</div>
		</ul>
		<!-- END BREADCRUMB -->
	</div>
	<!-- END TOP-->
	
	
	<div id="item-view-inner" class="clearfix">
	
		<div id="left">
			<div id="displaytitle">
				<h1><?php
					if($doc->{'displayTitle'}==''){
						echo $doc->{'key'};
					}else{
						echo $doc->{'displayTitle'};
					}
					?></h1>
			</div>
			<div class="right_icon">
				<?php
					$classType = 'collections';
					switch($doc->{'class'}){
						case "collection": $classType = 'collections';break;
						case "activity": $classType = 'activites';break;
						case "service": $classType = 'services';break;
						case "party": 
										if($doc->{'type'}=='person'){
											$classType = 'party_one';
										}else $classType = 'party_multi';
										break;
					} 
				?>
				<img class="icon-heading" src="<?php echo base_url();?>img/icon/<?php echo $classType;?>_32.png"/>
			</div> 
			<div class="clearfix"></div>
			
			
			<!-- LOGO -->
			<div class="clearfix"></div>
			
			<!-- ALTERNATIVE NAMES -->
			<div class="clearfix"></div>
			
			<!-- DESCRIPTIONS -->
			<div id="descriptions" class="descriptions">
				<?php 
					$notDisplay = array('logo', 'rights', 'accessRights');
					$display = array('brief','full', 'significanceStatement', 'notes');
					foreach($doc->{'description_type'} as $key=>$t){
						if(($t=='brief')&&(!in_array($t, $notDisplay))) echo '<p>'.$doc->{'description_value'}[$key].'</p>';
					}
					
					foreach($doc->{'description_type'} as $key=>$t){
						if(($t=='full')&&(!in_array($t, $notDisplay))) echo '<p>'.$doc->{'description_value'}[$key].'</p>';
					}
					
					foreach($doc->{'description_type'} as $key=>$t){
						if(($t=='significanceStatement')&&(!in_array($t, $notDisplay))) echo '<p>'.$doc->{'description_value'}[$key].'</p>';
					}
					
					foreach($doc->{'description_type'} as $key=>$t){
						if(($t=='notes')&&(!in_array($t, $notDisplay))) echo '<p>'.$doc->{'description_value'}[$key].'</p>';
					}
					
					foreach($doc->{'description_type'} as $key=>$t){
						if((!in_array($t, $display)&&(!in_array($t, $notDisplay)))) echo '<p>'.$doc->{'description_value'}[$key].'</p>';
					}
				?>
			</div>
			<a href="javascript:void(0);" class="showall_descriptions hide">More...</a>
			<div class="clearfix"></div>
			
			<!-- RELATED INFO -->
			<div class="clearfix"></div>
			
			<!-- COVERAGES -->
			
			<div class="clearfix"></div>
			
			<!-- SUBJECTS -->
			<div class="clearfix"></div>
			
			<!-- CITATIONS INFO -->
			<div class="clearfix"></div>
			
			<!-- IDENTIFIERS -->
			<div class="clearfix"></div>
		</div>
		<!-- END LEFT -->
		
		<div id="right">
			
			<!-- RIGHTS -->
			<?php 
				$hasRights = false;
				foreach($doc->{'description_type'} as $t) {
					if(($t=='rights')||($t=='accessRights')) $hasRights = true;
				}
				if($hasRights){
					echo '<div class="right-box">';
					echo '<h2>Access</h2>';
					echo '<div class="limitHeight300">';
					echo '<h3>Rights</h3>';
					foreach($doc->{'description_type'} as $key=>$t){
						if(($t=='rights')||($t=='accessRights')){
							echo '<p>'.$doc->{'description_value'}[$key].'</p>';
						}
					}
					echo '</div>';
					echo '</div>';
				}
			?>
			
			
			<!-- CONNECTIONS -->
			<div class="right-box" id="connectionsRightBox">
				<div id="connectionsInfoBox" class="hide"></div>
				<h2>Connections</h2>
				<div id="connections">
					<img src="<?php echo base_url();?>img/ajax-loader.gif" class="loading-icon"/>
				</div>
			</div>	
			
			
			<!-- SEE ALSO -->
			<?php if($doc->{'class'}=='collection'):?>
			<div class="right-box" id="seeAlsoRightBox">
				<div id="infoBox" class="hide"></div>
				<h2>ANDS Suggested Links</h2>
				<div id="seeAlso">
					<img src="<?php echo base_url();?>img/ajax-loader.gif" class="loading-icon"/>
				</div>
			</div>
			<?php endif;?>
			
			<?php if($doc->{'class'}=='party'):?>
			<div class="right-box" id="seeAlso-Identifier">
				<div id="infoBox" class="hide"></div>
				<h2>ANDS Suggested Links</h2>
				<div id="seeAlso-IdentifierBox">
					<img src="<?php echo base_url();?>img/ajax-loader.gif" class="loading-icon"/>
				</div>
			</div>
			<?php endif;?>
		</div>
		<!-- END RIGHT -->
	</div>
	<!-- END ITEM VIEW INNER -->
	
</div>
<!-- END ITEM VIEW -->
<pre>
	<?php var_dump($doc);?>
</pre>
<?php $this->load->view('tpl/footer');?>