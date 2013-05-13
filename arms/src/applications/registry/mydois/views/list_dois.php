<?php 

/**
 * DOI Listing Screen
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<?php 
$this->load->view('header'); 
$testDoiPrefix =  $this->config->item('test_doi_prefix');

?>
<div class="container" id="main-content">
	
<section id="registry-web-services">
	
<div class="row">
	<div class="span12" id="registry-web-services-left">
		<div class="box">
			<div class="box-header clearfix">
				<div class="navbar">
				
				    <a class="brand" href="<?=base_url('mydois');?>">DOI Query Tool</a>
				    <ul class="nav pull-right">
				      <li class="active"><a href="#">List My DOIs</a></li>
				      <li><?=anchor('mydois/getActivityLog?app_id=' . rawurlencode($client->app_id), 'View Activity Log', array("role"=>"button", "data-target"=>"#viewActivityLogModal", "data-toggle"=>"modal"));?></li>
				      <li><?=anchor('mydois/getAppIDConfig?app_id=' . rawurlencode($client->app_id), 'App ID Configuration', array("role"=>"button", "data-target"=>"#viewAppIDConfigModal", "data-toggle"=>"modal"));?></li>
				    </ul>
				
				</div>
			</div>
		
			<div class="box">
				
				<h3>Listing DOIs for <?=$client->client_name;?> <small>(<?=$client->app_id;?>)</small></h3>
				
				<table class="table table-hover table-condensed">
					<thead>
						<tr>
							<th>Title</th>	
							<th>DOI</th>
							<th></th>
							<th></th>
							<th>Status</th>	
							<th>Last Updated</th>			
						</tr>
					</thead>
					<tbody>
					<?php foreach($dois AS $doi): 
					$doiTitle = getDoiTitle($doi->datacite_xml);
					?>
						<tr>
							<td width="40%"><small><strong><?=$doiTitle;?></strong><br/><?=anchor($doi->url,$doi->url);?></small></td>
							<td>
								<?=anchor('http://dx.doi.org/' . $doi->doi_id, $doi->doi_id);?>
								<?php if(strpos($doi->doi_id ,$testDoiPrefix) === 0) {echo "<br/><span class='muted'><em>Test prefix DOI</em></span>";}  ?>
							</td>
							<td>
								<?=anchor('mydois/updateDoi?doi_id=' . rawurlencode($doi->doi_id), 'Update', array("role"=>"button", "class"=>"btn btn-mini", "data-target"=>"#updateDoiModal", "data-toggle"=>"modal"));?>
							</td>
							<td>
								<?=anchor('mydois/getDoiXml?doi_id=' . rawurlencode($doi->doi_id), 'View XML', array("role"=>"button", "class"=>"btn btn-mini", "data-target"=>"#viewDoiXmlModal", "data-toggle"=>"modal"));?>
							</td>
							<td><?=$doi->status;?>
							</td>
							<td><?=date('Y-m-d H:i:s', strtotime($doi->updated_when));?></td>
						</tr>	
					<?php endforeach; ?>
					</tbody>
				</table>
				
			</div>
			
		
		</div>
	</div>
</div>

</section>

<div class="modal hide fade" id="updateDoiModal" tabindex="-1" role="dialog" aria-labelledby="updateDoiModal" aria-hidden="true">
  <div class="modal-body">
    <p>Loading...</p>
    <div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"></div>
	</div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div class="modal hide fade" id="viewDoiXmlModal" tabindex="-1" role="dialog" aria-labelledby="viewDoiXmlModal" aria-hidden="true">
  <div class="modal-body">
    <p>Loading...</p>
    <div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"></div>
	</div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div class="modal hide fade" id="viewAppIDConfigModal" tabindex="-1" role="dialog" aria-labelledby="viewAppIDConfigModal" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3>App ID Configuration for <?=$client->client_name;?> <small>(<?=$client->app_id;?>)</small></h3>
  </div>
  <div class="modal-body">
    <p>Loading...</p>
    <div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"></div>
	</div>
  </div>
  <div class="modal-footer">
  	<p class="alert">To request a change to any of the information related to this DOI AppID, please contact <?=mailto('services@ands.org.au');?></p>
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div class="bigModal modal hide fade" id="viewActivityLogModal" tabindex="-1" role="dialog" aria-labelledby="viewActivityLogModal" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3>Activity Log for <?=$client->client_name;?> <small>(<?=$client->app_id;?>)</small></h3>
  </div>
  <div class="modal-body">
    <p>Loading...</p>
    <div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"></div>
	</div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>


</div>
<?php $this->load->view('footer');?>
<?php 
if(isset($doi_update))
{
	//echo (substr($doi_update,0,5));
	//{
		//$doi_update = "<span class='error'>".$doi_update."</span>";
	//}
?>
<div class="modal hide fade" id="updateDoiResult" tabindex="-1" role="dialog" aria-labelledby="updateDoiResult" aria-hidden="true">
	<div class="modal-header">
		 <button type="button" class="close" data-dismiss="modal">×</button>
		  <h3><?php if(isset($error)) { echo "Alert"; } else { echo '&nbsp;';}?></h3>
	</div>	
  	<div class="modal-body">
   		<p>
    	<div>
    		<?php 
    		if(isset($error))
    		{
    		?>
    			<p>An error has occurred:</p>
    			<p>Update of the doi was unsuccessful. The following error message was returned:</p>
    		<?php 
    		}
    		?>
    		<p><?=$doi_update?></p>
    	</div>
    </p>
    </div>
    <div class="modal-footer">
    	<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  	</div>
</div>

<script >
$("#updateDoiResult").modal();
</script>

<?php 
}
?>
<?php 

function getDoiTitle($doiXml)
{
	
	$doiObjects = new DOMDocument();
	$titleFragment = 'No Title';
	if(strpos($doiXml ,'<') === 0)
	{			
		$result = $doiObjects->loadXML(trim($doiXml));
		$titles = $doiObjects->getElementsByTagName('title');
		
		if($titles->length > 0)
		{
			$titleFragment = '';
			for( $j=0; $j < $titles->length; $j++ )
			{
				if($titles->item($j)->getAttribute("titleType"))
				{
					$titleType = $titles->item($j)->getAttribute("titleType");
					$title = $titles->item($j)->nodeValue;
					$titleFragment .= $title." (".$titleType.")<br/>";
				}
				else {
					$titleFragment .= $titles->item($j)->nodeValue."<br/>";
				}
			}
		}
	}
	else{
		$titleFragment = $doiXml;
	}
		
	return $titleFragment;
	
}


?>
function