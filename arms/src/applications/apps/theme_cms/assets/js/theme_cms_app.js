angular.module('theme_cms_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize']).
	factory('pages_factory', function($http){
		return {
			listAll : function(){
				var promise = $http.get(apps_url+'theme_cms/list_pages').then(function(response){
					return response.data;
				});
				return promise;
			},
			getPage : function(slug){
				var promise = $http.get(apps_url+'theme_cms/get/'+slug).then(function(response){
					return response.data;
				});
				return promise;
			},
			newPage: function(postData){
				var promise = $http.post(apps_url+'theme_cms/new_page/', postData).then(function(response){
					return response.data;
				});
				return promise;
			},
			deletePage: function(slug){
				var promise = $http.post(apps_url+'theme_cms/delete_page', {'slug':slug}).then(function(response){
					return response.data;
				});
				return promise;
				
			},
			savePage: function(postData){
				var promise = $http.post(apps_url+'theme_cms/save_page', postData).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	factory('search_factory', function($http){
		return{
			search: function(filters){
				var promise = $http.post(real_base_url+'registry/services/registry/post_solr_search', {'filters':filters}).then(function(response){
					return response.data;
				});
				return promise;
			},
			getConnections: function(key){
				var promise = $http.get(real_base_url+'registry/services/rda/getConnections/?registry_object_key='+encodeURIComponent(key)).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	filter('facet_display', function(){
		return function(text){
			var res = '';
			if(text){
				for(var i = 0 ;i<text.length-1;i=i+2){
					res+='<li>'+text[i]+' ('+text[i+1]+')'+'</li>';
				}
			}
			return res;
		}
	}).
	filter('relationships_display', function($filter){
		return function(text, type){
			var res = '';
			if(text && text[type]){
				var s = (text[type]);
				res += '<h4>'+$filter('class_name')(type)+'</h4>';
				res +='<ul>';
				$.each(s, function(i, k){
					res += '<li><a href="'+real_base_url+''+k['slug']+'">'+k['title']+' <small class="muted">'+k['relation_type']+'</small></a></li>';
				});
				res +='</ul>';
				if(text[type+'_count']>5){
					res += 'Total Count: '+text[type+'_count'];
				}
			}
			return res;
		}
	}).
	filter('class_name', function(){
		return function(text){
			switch(text){
				case 'collection': return 'Collections';break;
				case 'activity': return 'Activities';break;
				case 'party_one': return 'Researchers';break;
				case 'party_multi': return 'Research Groups';break;
				case 'service': return 'Services';break;
				default: return text;break;
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
	}).
	directive('roSearch', function($compile){
		return {
			restrict : 'A',
			link: function(scope, element){
				$(element).ro_search_widget().on('selected.rosearch.ands', function(e, ro){
					if(scope.ro){
						scope.ro.key = ro.key;
					}else if(scope.c){
						scope.c.relation.key = ro.key;
					}
				});
			}
		}
	}).
	directive('colorbox', function(){
		return {
			restrict: 'AC',
			link: function(scope, element, attrs){
				$(element).colorbox(attrs.colorbox);
			}
		}
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
		$scope.search_result = {};
		$scope.available_search = [];
		$scope.relationships = [];
		$($scope.page.left).each(function(){
			if(this.type=='search'){
				$scope.preview_search(this);
			}else if(this.type=='relation'){
				$scope.preview_relation(this);
			}
		});
		$($scope.page.right).each(function(){
			if(this.type=='search'){
				$scope.preview_search(this);
			}else if(this.type=='relation'){
				$scope.preview_relation(this);
			}
		});
		$scope.available_facets = [
			{type:'class', name:'Class'},
			{type:'group', name:'Research Groups'},
			{type:'license_class', name:'Licences'}
		];
		$scope.available_relation_class = [
			{type:'collection', name:'Collections'},
			{type:'party_one', name:'Researchers'},
			{type:'party_multi', name:'Research Groups'},
			{type:'activity', name:'Activities'},
			{type:'service', name:'Services'}
		];
		$scope.gallery_types = [
			{name: 'Carousel', value:'carousel'},
			{name: 'Filmstrip', value:'filmstrip'}
		]
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
			var now = new Date();
			$scope.saved_msg = 'Last Saved: '+now; 
		});
	}

	$scope.edit = function(c){
		if(c.editing){
			c.editing = false;
			if(c.type=='search'){
				$scope.preview_search(c);
			}else if(c.type=='relation'){
				$scope.preview_relation(c);
			}
			$scope.save();
		}else c.editing = true;
	}
	$scope.delete_blob = function(region, index){
		$scope.page[region].splice(index, 1);
	}

	$scope.addToList = function(blob, list){
		var newObj = {};
		switch(blob.type){
			case 'gallery': 
				newObj = {'src':''};
				if(!blob.gallery) blob.gallery = []; list = blob.gallery;
				break;
			case 'list_ro': 
				newObj = {'key':''};
				if(!blob.list_ro) blob.list_ro = []; list = blob.list_ro
				break;
			case 'search': 
				newObj = {name:'', value:''};
				if(!blob.search) blob.search = [];
				if(!blob.search.fq) blob.search.fq = []; list = blob.search.fq;
				break;
		}
		list.push(newObj);
	}

	$scope.setFilterType = function(filter, type){
		filter.name = type;
	}

	$scope.removeFromList = function(list, index){
		list.splice(index, 1);
	}

	$scope.preview_search = function(c){
		if(c.search){
			if(!c.search.id) c.search.id = Math.random().toString(36).substring(7);
			var filters = $scope.constructSearchFilters(c);
			search_factory.search(filters).then(function(data){
				filter_query ='';
				$.each(filters, function(i, k){
					if(k instanceof Array || (typeof(k)==='string' || k instanceof String)){
						if(i!='fl') filter_query +=i+'='+encodeURIComponent(k)+'/';
					}
				});
				$scope.search_result[c.search.id] = {name:c.title, data:data, search_id:c.search.id, filter_query:filter_query};
				$scope.$watch('search_result', function(){
					$scope.available_search = [];
					angular.forEach($scope.search_result, function(key, value){
						$scope.available_search.push({search_id:key.search_id, name:key.name});
					});
				});
			});
		}
	}

	$scope.preview_relation= function(c){
		if(c.relation){
			search_factory.getConnections(c.relation.key).then(function(data){
				$scope.relationships[c.relation.key] = data.connections[0];
			});
		}
	}

	$scope.addBoost = function(blob,key){
		if(!blob.search.fq) blob.search.fq = [];
		blob.search.fq.push({name:'boost_key', value:key});
	}

	$scope.constructSearchFilters = function(c){
		var filters = {};
		var placeholder = '';
		filters['include_facet'] = true;
		filters['fl'] = 'id, display_title, slug, key';
		if(c.search.query) filters['q'] = c.search.query;
		$(c.search.fq).each(function(){
			if(this.name){
				if(filters[this.name]){
					if(filters[this.name] instanceof Array){
						filters[this.name].push(this.value);
					}else{
						placeholder = filters[this.name];
						filters[this.name] = [];
						filters[this.name].push(placeholder);
						filters[this.name].push(this.value);
					}
				}else filters[this.name] = this.value;
			}
		});
		return filters;
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