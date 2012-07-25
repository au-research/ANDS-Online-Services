<?php include('header.php');?>
<div class="container" id="main-content">
<section>
	 <div class="page-header">
        <h1>Manage My Records<small> for Datasource: <b>Minh Duc Nguyen</b></small></h1>
    </div>
    <div class="row" id="mmr_toolbar">
    	<div class="span12">
    		<div class="well">
    			<div class="btn-toolbar">
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
				<div class="btn-group" data-toggle="buttons-radio">
				  <button class="btn" data-toggle="button">Status</button>
				  <button class="btn" data-toggle="button">Quality</button>
				</div>
				</div>
			</div>
    	</div>
    </div>

	<ul class="thumbnails" id="items">
		<?php
			for($i=0;$i<15;$i++){
				echo '
				<li class="span3">
				  	<div class="thumbnail">
				  		<h3>Test collection</h3>
				  		<p class="brief">Lorem ipsum laborum aliqua occaecat aute ex voluptate est voluptate est eu aute nisi. </p>
				  		<div class="btn-group">
						  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
						    Action
						    <span class="caret"></span>
						  </a>
						  <ul class="dropdown-menu">
						    <li><a href="javascript:;"><i class="icon-eye-open"></i> View</a></li>
						    <li><a href="javascript:;"><i class="icon-edit"></i> Edit</a></li>
						    <li><a href="javascript:;"><i class="icon-trash"></i> Delete</a></li>
						  </ul>
						</div>
				  	</div>
				  </li>
				';
			}
		?>
	</ul>
	<div class="row">
		<div class="span12">
			<div class="well"><a href="#">Load Moar...</a></div>
		</div>
	</div>

</section>



</div>
<?php include('footer.php');?>