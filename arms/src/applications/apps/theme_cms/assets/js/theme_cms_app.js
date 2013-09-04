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
				var promise = $http.post(base_url+'theme_cms/new_page/', postData).then(function(response){
					return response.data;
				});
				return promise;
			},
			deletePage: function(slug){
				var promise = $http.post(base_url+'theme_cms/delete_page', {'slug':slug}).then(function(response){
					return response.data;
				});
				return promise;
				
			},
			savePage: function(postData){
				var promise = $http.post(base_url+'theme_cms/save_page', postData).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	factory('search_factory', function($http){
		return{
			search: function(query, fq){
				var url = real_base_url+'registry/services/registry/solr_search/?query='+query;
				// console.log(url);
				var promise = $http.get(url).then(function(response){
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
	})

function ListCtrl($scope, pages_factory){
	pages_factory.listAll().then(function(data){
		$scope.pages = data;
	});
}

function ViewPage($scope, $routeParams, pages_factory, $location, search_factory){

	$scope.sortableOptions = {
		handle:'.widget-title',
		connectWith: '.region',
		stop: function(ev, ui){
			$scope.save();
		}
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
			$scope.save();
		}else c.editing = true;
	}
	$scope.delete_blob = function(region, index){
		$scope.page[region].splice(index, 1);
	}

	$scope.addToList = function(list_type, list){
		var newObj = {};
		switch(list_type){
			case 'gallery': newObj = {'src':''}; break;
			case 'list_ro': newObj = {'key':''}; break;
		}
		if(!list[list_type]) list[list_type] = [];
		list[list_type].push(newObj);
	}

	$scope.removeFromList = function(list_type, list, index){
		list[list_type].splice(index, 1);
	}

	$scope.preview_search = function(c){
		if(c.search.query){
			c.search.id = Math.random().toString(36).substring(7);
			search_factory.search(c.search.query, null).then(function(data){
				console.log(data);
				$scope.search_result = data;
			});
		}
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