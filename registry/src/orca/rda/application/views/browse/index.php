<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>
<div id="item-view" class="top-corner">

	<!--Breadcrumb-->
	<div id="top" class="top-corner">
		<ul id="breadcrumb" class="top-corner">
			<li><?php echo anchor('browse/', 'Browse Research Data Australia', array('class'=>'crumb'))?></li>
		</ul>
	</div>


	<div id="vocab-container">
		<div id="left-vocab">
			<div id="search-vocab">
				<input type="text" id="search-vocab-field"/> <button>Lookup</button>
			</div>
			<div id="tree-vocab">
				Loading Tree
			</div>
		</div>
		<div id="right-vocab">

			<div>
				<h3>Browse Research Data Australia</h3>
				Use the tree tool on the left to explore Research Data Australia by subject area. For more refined search functionality, use the <?php echo anchor('search#!/tab=All', 'Search Tool');?>.
				<br/><br/>
				<i>Note: Only collections with subjects from a recognised vocabulary are listed here. Use the tabs above to locate other types of records in RDA</i>
			</div>


		</div>
		<div class="clearfix"></div>
	</div>
</div>
<?php $this->load->view('tpl/footer');?>