<?php 

/**
 * PIDs Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Persistent Identifier Service (PIDS)</h1>
	<div class="btn-group">
		<a data-toggle="modal" href="#mint_modal" href="javascript:;" class="btn btn-large"><i class="icon icon-plus"></i> Mint</a>
	</div>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor('/pids', '<i class="icon-home"></i> List My Identifiers', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div class="widget-box">
		<div class="widget-title">
			<h5>PIDs</h5>
		</div>
		<div class="widget-content">
			<form class="form-search">
			  <div class="input-append">
			    <input type="text" class="span2 search-query" id="search_query">
			    <button type="submit" class="btn">Search</button>
			  </div>
			</form>
			<hr/>
			<div id="pids">Loading...</div>
		</div>
	</div>
</div>


<div class="modal hide fade" id="mint_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Mint a new PID</h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="mint_form">
				<div class="control-group">
					<label class="control-label">URL</label>
					<div class="controls">
						<input type="url" name="url" value="http://"/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Description</label>
					<div class="controls">
						<input type="text" name="desc"/>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="modal-footer">
		<a id="mint_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Minting...">Mint</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<script type="text/x-mustache" id="pids-list-template">
<p>Total number of PIDs owned: <strong>{{result_count}}</strong></p>
{{#no_result}}
<div class="well">No result!</div>
{{/no_result}}
<hr/>
{{#pids}}
	<h5>{{handle}}</h5>
	<dl class="dl-horizontal">
	{{#DESC}}
		<dt>Description</dt>
		<dd>{{DESC}}</dd>
	{{/DESC}}
	{{#URL}}
		<dt>URL</dt>
		<dd>{{URL}}</dd>
	{{/URL}}
	</dl>
<hr/>
{{/pids}}
{{#hasMore}}
<a href="javascript:;" class="btn btn-block load_more" next_offset="{{next_offset}}">Load More <i class="icon icon-arrow-down"></i></a>
{{/hasMore}}
</script>

<script type="text/x-mustache" id="pids-more-template">
{{#pids}}
	<h5>{{handle}}</h5>
	<dl class="dl-horizontal">
	{{#DESC}}
		<dt>Description</dt>
		<dd>{{DESC}}</dd>
	{{/DESC}}
	{{#URL}}
		<dt>URL</dt>
		<dd>{{URL}}</dd>
	{{/URL}}
	</dl>
<hr/>
{{/pids}}
{{#hasMore}}
<a href="javascript:;" class="btn btn-block load_more" next_offset="{{next_offset}}">Load More <i class="icon icon-arrow-down"></i></a>
{{/hasMore}}
</script>

<script type="text/x-mustache" id="trusted_clients-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Trusted Clients</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>IP</th>
					<th>App ID</th>
					<th>Description </th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td>{{ip_address}}</td>
					<td>{{app_id}}</td>
					<td>{{description}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php $this->load->view('footer');?>