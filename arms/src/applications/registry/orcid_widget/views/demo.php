<?php
/******
 *
 * XXX
 *
 */?>

<?php $this->load->view('header');?>
 <div class="formarea">
	<h1>Orcid Widget Demonstrator</h1>
        <h2>Example Orcid Retrieval</h2>
        <hr/>

        <div class="formfields">
          <form id="searchform">
	    		<fieldset>
	      			<legend>Details</legend>
              		<dl>
						<dt>Orcid:</dt>
						<dd><input type="text" name="name" id="orcid_name1" value="" size="40" class="orcid_lookup orcid_widget"/></dd>
					</dl>
<dl>
						<dt>Orcid:</dt>
						<dd><input type="text" name="name" id="orcid_name2" value="" size="40" class="orcid_lookup orcid_widget"/></dd>
					</dl>	
	    		</fieldset>
            	<p><input type="submit"/></p>
          </form>
        </div>
      </div>
</div>
<?php $this->load->view('footer');?>