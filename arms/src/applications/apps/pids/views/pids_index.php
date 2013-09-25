<?php 

/**
 * PIDs Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Identify My Data</h1>
	<div class="btn-group">
		<a data-toggle="modal" href="#mint_modal" href="javascript:;" class="btn btn-large"><i class="icon icon-plus"></i> Mint a new Identifier</a>
		<?php if($this->user->hasFunction('SUPERUSER')) echo anchor('/pids/list_trusted','List Trusted Clients', array('class'=>'btn btn-large')); ?>
	</div>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/pids', 'Identify My Data', array('class'=>'current')); ?>
</div>
<input type="hidden" value="<?php echo $identifier; ?>" id="identifier"/>
<div class="container-fluid" id="main-content">

	<div class="row-fluid">
		<div class="span2">&nbsp;</div>
		<div class="span8">
			<div class="widget-box">
				<div class="widget-title">
					<h5></h5>
					<select class="chosen" id="pid_chooser">
						<option value=""></option>
						<?php foreach($orgRole as $o): ?>
						<option value="<?php echo $o ?>"><?php echo $o; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="widget-content">
					<div id="pids">Loading...</div>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>
</div>


<div class="modal hide fade" id="mint_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Mint a new Identifier</h3>
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
						<input type="url" name="url" value="" placeholder="http://"/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Description</label>
					<div class="controls">
						<input type="text" name="desc"/>
					</div>
				</div>
<div style="height:175px;overflow:auto;border:1px solid #ccc;display:none;" id="terms"><p>You have asked to mint a persistent identifier through ANDS <i>Identify
My Data</i> self-service. This means that you will enter location and/or
description information relating to the object you wish to identify and
ANDS will provide you with a persistent identifier for that object.</p>
<p>
In using ANDS <i>Identify My Data</i> self-service you agree that:
</p>
<ul>
	<li>You are part of the higher education, public research or cultural
	collections sector and that at least some of the objects you are
	identifying are publicly available or will eventually become publicly
	available.</li>
	<li>You are authorised and entitled to mint and manage persistent
	identifiers for the objects you intend to identify.</li>
	<li>You will endeavour to keep up-to-date the location and
	description fields for the persistent identifiers you mint.</li>
	<li>You understand that this location and description information
	will be available to the general public and that confidential material
	should not be entered into these fields.</li>
	<li>You will take responsibility for liaison with any party who has
	queries regarding persistent identifiers that you mint. (ANDS does not
	provide link-rot checking or help-desk services for end-users of
	persistent identifiers.)</li>
</ul>

<p>
You understand that:
</p>
<ul>
	<li>ANDS provides the <i>Identify My Data</i> product on an ‘as is’ and
	‘as available’ basis. ANDS hereby exclude any warranty either express
	or implied as to the merchantability, fitness for purpose, accuracy,
	currency or comprehensiveness of this product. To the fullest extent
	permitted by law, the liability of ANDS under any condition or warranty
	which cannot be excluded legally is limited, at the option of ANDS to
	supplying the services again or paying the cost of having the services
	supplied again.</li>
	
	<li>ANDS does not manage persistent identifiers; ANDS only provides
	the infrastructure that allows minting, resolution and updating of
	identifiers. Processes and policies need to be put in place by those
	utilising <i>Identify My Data</i> to ensure that appropriate maintenance
	practices are put in place to underpin persistence.</li>
	<li>ANDS will endeavour to persist ANDS Identifiers for a minimum of
	twenty years.</li>
	<li>The allocation of a persistent identifier to an object does not
	include any transfer or assignment of ownership of any Intellectual
	Property right (IPR) with regard to that content.</li>
	<li>ANDS will endeavour to provide a high availability service.
	However, ANDS <i>Identify My Data</i> is underpinned and reliant on the <a href="http://www.handle.net/">Handle
	services</a> provided by the <a href="http://cnri.reston.va.us/">Corporation for National Research Initiatives</a>
	(CNRI), in particular the Global Handle Registry. ANDS cannot warrant
	the longevity or reliability of the Handle system or the CNRI.</li>
</ul>
</div>
				<div class="control-group">
					<label class="control-label"></label>
					<div class="controls">
						<input type="checkbox" name="agree" checked=checked/> I Agree To the <a href="javascript:;" id="toggleTerms">Terms and Conditions</a>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="modal-footer">
		<span id="result"></span>
		<a id="mint_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Minting...">Mint</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<script type="text/x-mustache" id="pids-list-template">
<form class="form-search">		
	<div class="input-append">
	    <input type="text" class="search-query" id="search_query" value="{{search_query}}"/>
	    <button type="submit" class="btn">Search</button>
	</div>
	Total number of Identifiers owned: <strong>{{result_count}}</strong>
</form>
{{#no_result}}
<div class="well">No result!</div>
{{/no_result}}
<hr/>
{{#pids}}
<div class="widget-box">
	<div class="widget-title">
		<h5><a href="<?php echo base_url();?>pids/view/?handle={{handle}}">{{handle}}</a></h5>
	</div>
	<div class="widget-content">
		<dl class="dl-nomargin">
			{{#resolver_url}}
				<dt>Resolver Link</dt> 
				<dd><a href="{{resolver_url}}">{{resolver_url}}</a></dd>
			{{/resolver_url}}
			{{#hasDESC}}<dt>Description</dt>{{/hasDESC}}
			{{#DESC}}
				<dd><span class="desc">{{.}}</span></dd>
			{{/DESC}}
			{{#hasURL}}<dt>URL</dt>{{/hasURL}}
			{{#URL}}
				<dd>{{URL}}</dd>
			{{/URL}}
		</dl>
	</div>	
</div>
{{/pids}}
{{#hasMore}}
<a href="javascript:;" class="btn btn-block load_more" next_offset="{{next_offset}}">Load More <i class="icon icon-arrow-down"></i></a>
{{/hasMore}}
</script>

<script type="text/x-mustache" id="pids-more-template">
{{#pids}}
<div class="widget-box">
	<div class="widget-title">
		<h5><a href="<?php echo base_url();?>pids/view/?handle={{handle}}">{{handle}}</a></h5>
	</div>
	<div class="widget-content">
		<dl class="dl-nomargin">
			{{#resolver_url}}
				<dt>Resolver Link</dt> 
				<dd><a href="{{resolver_url}}">{{resolver_url}}</a></dd>
			{{/resolver_url}}
			{{#hasDESC}}<dt>Description</dt>{{/hasDESC}}
			{{#DESC}}
				<dd><span class="desc">{{.}}</span></dd>
			{{/DESC}}
			{{#hasURL}}<dt>URL</dt>{{/hasURL}}
			{{#URL}}
				<dd>{{URL}}</dd>
			{{/URL}}
		</dl>
	</div>	
</div>
{{/pids}}
{{#hasMore}}
<a href="javascript:;" class="btn btn-block load_more" next_offset="{{next_offset}}">Load More <i class="icon icon-arrow-down"></i></a>
{{/hasMore}}
</script>
<?php $this->load->view('footer');?>