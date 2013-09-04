<?php $this->load->view('header'); ?>
<div ng-app="theme_cms_app">
	<div ng-view></div>
</div>




<div id="list_template" class="hide">
	<div class="content-header">
		<h1>Theme CMS</h1>
		<div class="btn-group">
			<a class="btn btn-large" href="#/new_page"><i class="icon icon-plus"></i> New Page</a>
		</div>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Theme CMS</a>
	</div>
	
	<div class="container-fluid">
		<div class="widget-box">
			<div class="widget-title">
				<h5>Pages</h5>
			</div>
			<div class="widget-content">
				<ul>
					<li ng-repeat="page in pages">
						<a href="#/view/{{page}}">{{page}}</a>
					</li>
				</ul>
				<div ng-show="pages.length == 0" class="alert alert-info">
					There are no existing pages. Create new one with the button bellow
				</div>
				<hr>
				<a class="btn" href="#/new_page"><i class="icon icon-plus"></i> New Page</a>
			</div>
		</div>
	</div>

	
</div>

<div id="new_page_template" class="hide">
	<div class="content-header">
		<h1>Theme CMS</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/">Theme CMS</a>
		<a href="#/new_page" class="current">New Page</a>
	</div>
	<div class="container-fluid">
		<div class="widget-box">
			<div class="widget-title">
				<h5>Add New Page</h5>
			</div>
			<div class="widget-content">
				<form ng-submit="addPage()" class="form">
					<fieldset>
						<label for="">Theme Page Title: </label>
						<input type="text" placeholder="Theme Page Title" name="title" ng-model="new_page_title" required><br/>
						<span class="help-block" ng-show="new_page_title">A file name {{new_page_title | slugify}}.json will be automatically generated upon creation</span>
						<button type="submit" class="btn btn-primary">Add New Page</button>
					</fieldset>
					<div class="alert alert-success" ng-show="ok">{{ok.msg}} <a href="#/view/{{ok.slug}}">Click here</a> to view your page</div>
					<div class="alert alert-danger" ng-show="fail">{{fail.msg}}</div>
				</form>
			</div>
		</div>
	</div>
</div>




<div id="view_page_template" class="hide">
	<div class="content-header">
		<h1>{{page.title}}</h1>
		<div class="btn-group">
			<a class="btn btn-large" ng-click="save()"><i class="icon icon-hdd"></i> Save</a>
			<a class="btn btn-large"><i class="icon icon-eye-open"></i> Preview</a>
			<a class="btn btn-large btn-danger" tip="Delete" ng-click="deleting('true')"><i class="icon-white icon-trash"></i></a>
		</div>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/">Theme CMS</a>
		<a href="#/view/{{page.slug}}" class="current">{{page.title}}</a>
	</div>
	
	<div class="container-fluid">

		<div class="row-fluid" ng-show="show_delete_confirm">
			<div class="span3">&nbsp;</div>
			<div class="span6">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Confirmation</h5>
					</div>
					<div class="widget-content">
						<div class="alert alert-danger">
							<p>Are you sure you want to delete this page? This action is irreversible</p>
							<a href="" class="btn btn-danger" ng-click="delete(page.slug)">Yes, Delete the page</a>
							<a href="" class="btn btn-link" ng-click="deleting('false')">Close</a>
						</div>
					</div>
				</div>
			</div>
			<div class="span3">&nbsp;</div>
		</div>

		<div class="row-fluid">
			<div class="span8">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Main Content</h5>
					</div>
					<div ui-sortable="sortableOptions" ng-model="page.left" class="widget-content region">
						<div ng-repeat="c in page.left" ng-dblclick="edit(c)">
							<div class="widget-box">
								<div class="widget-title">
									<h5>{{c.title}} <small>{{c.type}}</small></h5>
									<select ng-model="c.type">
										<option value="html">HTML Contents</option>
										<option value="gallery">Image Gallery</option>
										<option value="list_ro">List of Registry Objects</option>
										<option value="separator">Separator</option>
										<option value="search">Search Results</option>
										<option value="separator">Facet</option>
									</select>
								</div>
								<div class="widget-content">
									<div ng-hide="c.editing">
										<div ng-bind-html="c.content" ng-show="c.type == 'html'"></div>
										<div ng-show="c.type=='gallery'" ng-repeat="img in c.gallery">
											<img src="{{img.src}}" alt="">
										</div>
										<div ng-show="c.type=='list_ro'">
											<ul>
												<li ng-repeat="ro in c.list_ro">{{ro.key}}</li>
											</ul>
										</div>
										<hr/>
										<a href="" ng-click="edit(c)" class="btn">Edit</a>
										<a href="" ng-click="delete_blob('left', $index)" class="btn btn-danger"><i class="icon-white icon-trash"></i></a>
									</div>

									<div ng-show="c.editing">

										<form class="form">
											<label for="title">Title</label><input type="text" ng-model="c.title">
											<hr/>
											<div ng-show="c.type == 'html'">
												<textarea ui-tinymce ng-model="c.content"></textarea>
											</div>

											<div ng-show="c.type == 'gallery'">
												<div ng-repeat="img in c.gallery">
													Image Link: <input type="text" ng-model="img.src"> <a href="" class="" ng-click="removeFromList('gallery', c, $index)"><i class="icon icon-remove"></i></a>
												</div>
												<a href="" class="btn btn-primary" ng-click="addToList('gallery', c)"><i class="icon-white icon-plus"></i> Add Image</a>
											</div>

											<div ng-show="c.type == 'list_ro'">
												<div ng-repeat="ro in c.list_ro">
													Registry Object Key: <input type="text" ng-model="ro.key"> <a href="" class="" ng-click="removeFromList('list_ro', c, $index)"><i class="icon icon-remove"></i></a>
												</div>
												<a href="" class="btn btn-primary" ng-click="addToList('list_ro', c)"><i class="icon-white icon-plus"></i> Add Registry Object</a>
											</div>

											<div ng-show="c.type == 'search'">
												Search Query: <input type="text" ng-model="c.search.query">
												<div></div>
												<a href="" class="btn" ng-click="addToList('search_filters', c.search.fq)"><i class="icon icon-plus"></i> Add Filter</a>
												<a href="" class="btn" ng-click="preview_search(c)">Preview Search</a>
												<div ng-show="c.search.id">
													<ul>
														<li ng-repeat="doc in search_result.result.docs">{{doc.display_title}}</li>
													</ul>
												</div>
											</div>
											

										</form>
										<hr/>
										<a href="" ng-click="edit(c)" class="btn">Done</a>
									</div>
								</div>
							</div>
						</div>
						<hr/>
						<a href="" ng-click="addContent('left')" class="btn btn-primary"><i class="icon-white icon-plus"></i> Add Content</a>
					</div>
				</div>
			</div>
			<div class="span4">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Sidebar</h5>
					</div>
					<div class="widget-content region">
						<!-- <div class="alert alert-info" ng-hide="page.right">There are no content here, add something</div> -->
						<div ui-sortable="sortableOptions" ng-repeat="c in page.right">
							<div class="widget-box">
								<div class="widget-title">
									<h5>{{c.title}}</h5>
								</div>
								<div class="widget-content">
									{{c.content}}
								</div>
							</div>
						</div>
						<hr/>
						<a href="" ng-click="addContent('right')" class="btn btn-primary"><i class="icon-white icon-plus"></i> Add Content</a>
					</div>
				</div>
			</div>
		</div>
		{{page | json}}
	</div>

</div>

<div id="delete_page_template" class="hide">
	<div class="content-header">
		<h1>{{page.title}}</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/">Theme CMS</a>
		<a href="#/view/{{page.slug}}">{{page.title}}</a>
		<a href="#/new_page" class="current">Delete</a>
	</div>
	
</div>

<?php $this->load->view('footer'); ?>