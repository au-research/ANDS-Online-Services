<?php $this->load->view('rda_header');?>	
<div class="container">
	<div class="line grid1-2">
		<div class="vocab-tree-left">
			<p><strong>ANZSRC Field of Research:</strong></p>
			<!--input type="text" id="anzsrc-vocab" name="anzsrc-for" value="" size="25" placeholder="Search"/-->
			<p></p>
			<div id="vocab-tree"></div>
		</div>
		<div>
			<div id="content">
				<h3>Browse Research Data Australia</h3>
				Use the tree tool on the left to explore Research Data Australia by subject area. For more refined search functionality, use the <?php echo anchor('search', 'Search Tool');?>.
				<br><br>
				<i>Note: Only collections with subjects from a recognised vocabulary are listed here. Use the tabs above to locate other types of records in RDA</i>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('rda_footer');?>