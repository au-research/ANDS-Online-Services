<?php include('header.php');?>
<div class="container" id="main-content">
<section>
	 <div class="page-header">
        <h1>Manage My Records<small> for Datasource: <b>Minh Duc Nguyen</b></small></h1>
    </div>
    <div class="row-fluid" id="mmr_toolbar">
    	<div class="span4">
    		<span class="btn-toolbar">
	    		<div class="btn-group">
				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				    View
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu" id="switch_view">
				    <li><a href="javascript:;" name="thumbnails"><i class="icon-th"></i> Thumbnails View</a></li>
				    <li><a href="javascript:;" name="lists"><i class="icon-th-list"></i> List View</a></li>
				  </ul>
				</div>

				<div class="btn-group">
					<button class="btn select_all_btn" data-toggle="button" name="select_all">Select All</button>
				</div>

				<div class="btn-group" data-toggle="buttons-radio">
				  <button class="btn report_view" name="status">Status</button>
				  <button class="btn report_view" name="quality">Quality</button>
				</div>
			</span>
		</div>
		<div class="span4">
			<span>
				<form class="form-search">
				  <input type="text" class="input-medium" placeholder="Search...">
				  <button class="btn">Search</button>
				</form>
			</span>
    	</div>
    	<div class="span4">
    		<span class="btn-toolbar">
    			<div class="btn-group">
				  <a class="btn dropdown-toggle disabled" data-toggle="dropdown" href="#">
				    Batch
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu pull-right" id="switch_view">
				    <li><a href="javascript:;" name="thumbnails">Enable Drag and Drop Select</a></li>
				  </ul>
				</div>
	    		<div class="btn-group">
				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				    Options
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu pull-right" id="switch_view">
				    <li><a href="javascript:;" name="thumbnails">Enable Drag and Drop Select</a></li>
				    <li><a href="javascript:;" name="thumbnails">Hide minibar</a></li>
				  </ul>
				</div>
			</span>
    	</div>
    </div>
    <div class="row-fluid">
    	<div class="span12">
    		<div class="well hide" id="items_info">

    		</div>
    	</div>
    </div>

	<ul class="thumbnails" id="items"></ul>
	<div class="row-fluid">
		<div class="span12">
			<div class="well"><a href="javascript:;" id="load_more">Load Moar...</a></div>
		</div>
	</div>

	<div class="hide" id="items-template">
		{{#items}}
			<li class="span3">
			  	<div class="item">
			  		<div class="item-info"></div>
			  		<div class="item-snippet">
				  		<h3>{{title}}</h3>
				  		<p class="brief">{{brief}}</p>
				  	</div>
			  		<div class="btn-group">
			  			<button class="btn"><i class="icon-eye-open"></i></button>
				  		<button class="btn"><i class="icon-edit"></i></button>
				  		<button class="btn"><i class="icon-trash"></i></button>
					</div>
			  	</div>
			  </li>
		{{/items}}
	</div>

</section>



</div>
<?php include('footer.php');?>