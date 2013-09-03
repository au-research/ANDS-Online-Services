angular.module('theme_cms_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize']).
	factory('pages_factory', function($http){
		return {
			listAll : function(){
				var promise = $http.get(base_url+'theme_cms/list_pages').then(function(response){
					return response.data;
				});
				return promise;
			},
			getPage : function(slug){
				var promise = $http.get(base_url+'theme_cms/get/'+slug).then(function(response){
					return response.data;
				});
				return promise;
			},
			newPage: function(postData){
				// postData = JSON.stringify(postData);
				var promise = $http({
					method: 'POST',
					url: base_url+'theme_cms/new_page/',
					data: postData
				}).then(function(response){
					return response.data;
				});
				return promise;
			},
			deletePage: function(slug){
				var promise = $http({
					method: 'POST',
					url: base_url+'theme_cms/delete_page/',
					data: $.param({'slug': slug})
				}).then(function(response){
					return response.data;
				});
				return promise;
			},
			savePage: function(postData){
				var promise = $http({
					method: 'POST',
					url: base_url+'theme_cms/save_page/',
					data: postData
				}).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:ListCtrl,
				template:$('#list_template').html()
			})
			.when('/new_page',{
				controller:NewPageCtrl,
				template:$('#new_page_template').html()
			})
			.when('/view/:slug', {
				controller: ViewPage,
				template:$('#view_page_template').html()
			})
			.when('/delete/:slug', {
				controller: DeletePage,
				template:$('#delete_page_template').html()
			})
	})

function ListCtrl($scope, pages_factory){
	pages_factory.listAll().then(function(data){
		$scope.pages = data;
	});
}

function ViewPage($scope, $routeParams, pages_factory, $location){

	$scope.sortableOptions = {
		handle:'.widget-title',
		connectWith: '.region'
	}

	pages_factory.getPage($routeParams.slug).then(function(data){
		$scope.page = data;
		$scope.page.left = $scope.page.left || [];
		$scope.page.right = $scope.page.right || [];
	});
	$scope.addContent = function(region){
		var blob = {'title':'New Content', 'type':'html', 'content':''};
		if(region=='left'){
			$scope.page.left.push(blob);
		}else if(region=='right'){
			$scope.page.right.push(blob);
		}
	}

	$scope.show_delete_confirm = false;
	$scope.deleting = function(param){
		if(param=='true'){
			$scope.show_delete_confirm = true;
		}else $scope.show_delete_confirm = false;
	}
	$scope.delete = function(slug){
		pages_factory.deletePage(slug).then(function(data){
			$location.path('/');
		});
	}

	$scope.save = function(){
		pages_factory.savePage($scope.page).then(function(data){
			console.log(data);
		});
	}

	$scope.edit = function(c){
		if(c.editing){
			c.editing = false;
		}else c.editing = true;
	}
	$scope.delete_blob = function(region, index){
		if(region=='left'){
			$scope.page.left.splice(index, 1);
		}
	}

	$scope.addImage = function(c){
		if(!c.img_list) c.img_list = [];
		c.img_list.push({'src':''});
	}

	$scope.removeImage = function(c, index){
		c.img_list.splice(index, 1);
	}

}

function NewPageCtrl($scope, pages_factory, Slug){
	$scope.addPage = function(){
		$scope.ok, $scope.fail = null;
		var slug = Slug.slugify(this.new_page_title);
		var postData = {
			title: this.new_page_title,
			slug: slug
		}
		pages_factory.newPage(postData).then(function(data){
			if(data==1){
				$scope.ok = {'msg': 'Your Theme Page has been created.', 'slug':slug};
			}else{
				$scope.fail = {'msg':'There is a problem creating your page'};
			}
		});
	}
}



function DeletePage ($scope, $routeParams, pages_factory) {
	pages_factory.getPage($routeParams.slug).then(function(data){
		$scope.page = data;
		$scope.page.left = $scope.page.left || [];
		$scope.page.right = $scope.page.right || [];
	});
}