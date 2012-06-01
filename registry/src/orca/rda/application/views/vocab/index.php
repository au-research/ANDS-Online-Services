<?php $this->load->view('tpl/header');?>
<?php $this->load->view('tpl/mid');?>
<div id="item-view" class="top-corner">

	<!--Breadcrumb-->
	<div id="top" class="top-corner">
		<ul id="breadcrumb" class="top-corner">
			<li><?php echo anchor('vocab/index', 'Vocab Home', array('class'=>'crumb'))?></li>
			<li><?php echo anchor('vocab/index', 'anzsrc-for', array('class'=>'crumb', 'id'=>'vocabID'))?></li>
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
		<div id="right-vocab"></div>
		<div class="clearfix"></div>
	</div>
</div>
<?php $this->load->view('tpl/footer');?>