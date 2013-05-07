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
				<p>
				Use the tree tool on the left to explore Research Data Australia by
subject area using the ANZSRC Field of Research classification. For more refined search functionality, use the <?php echo anchor('search', 'Search Tool');?>.
				</p>
				<p>
				<i>Note: Only collections with subjects using the ANZSRC-FOR vocabulary are listed here. Use the tabs above to locate other types of records in RDA.</i>
				</p>
				<h4 style="margin-top:1em">About the ANZSRC Field of Research Classification</h4>
				<p>
				  The Australian and New Zealand Standard Research Classification
				  (ANZSRC) is the collective name for a set of three related
				  classifications
				  developed by the Australian Bureau of Statistics for use in the measurement and analysis of research and
				  experimental development (R&D) undertaken in Australia and New
				  Zealand.
				</p>
				<p>
				  The Field of Research (FOR) classification allows R&D
				  activity to be categorised hierarchically and includes major fields of
				  research
				  investigated by national research institutions and organisations, and
				  emerging areas of study. Research Data Australia uses the ANZSRC Field
				  of Research classification as the suggested vocabulary for describing
				  research domain entities and
				  activities in the ANDS Collections Registry. Using standard classifiers
				  of research helps to make linkages across the Research Data Australia
				  national corpus.
				</p>
				<p>
				  For more information about ANZSRC and the Field of Research
				  classification, refer to the Australian Bureau of Statistics website on Fields of Research. For a guide to the three hierarchical Field of Research levels see: FOR Divisions, Groups and Fields.
				</p>
				</div>
		</div>
	</div>
</div>
<?php $this->load->view('rda_footer');?>