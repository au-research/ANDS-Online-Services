angular.module('portal_theme',[]).
	factory('pages', function($http){
		return {
			getPage: function(slug){
				var promise = $http.get(rda_service_url+'getThemePage/'+slug).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	factory('searches', function($http){
		return{
			search: function(filters){
				var promise = $http.post(base_url+'search/filter/', {'filters':filters}).then(function(response){
					return response.data;
				});
				return promise;
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
	}).
	filter('class_name', function(){
		return function(text){
			switch(text){
				case 'collection': return 'Collections';break;
				case 'activity': return 'Activities';break;
				case 'party': return 'Parties';break;
				case 'service': return 'Services';break;
				default: return text;break;
			}
		}
	}).
	controller('init', function($scope, pages, searches, $filter){
		$scope.search_results = {}; 
		$scope.slug = $('#slug').val();
		// pages.getPage($scope.slug).then(function(data){
		// 	$scope.page = data;
		// });
		$('.theme_search').each(function(){
			var filter = {};
			filter['q'] = $('.theme_search_query', this).val();
			// filter['id'] = $(this).attr('id');
			var search_id = $(this).attr('id');
			filter['fq'] = {};
			$('.theme_search_fq', this).each(function(){
				if(filter[$(this).attr('fq-type')]){
					if(filter[$(this).attr('fq-type')] instanceof Array){
						filter[$(this).attr('fq-type')].push($(this).val());
					}else{
						var prev = filter[$(this).attr('fq-type')];
						filter[$(this).attr('fq-type')] = [];
						filter[$(this).attr('fq-type')].push(prev);
						filter[$(this).attr('fq-type')].push($(this).val());
					}
				}else filter[$(this).attr('fq-type')] = $(this).val();
			});
			searches.search(filter).then(function(data){
				$scope.search_results[search_id] = data;

				// console.log(filter);
				var filter_query = '';
				$.each(filter, function(i, k){
					if(k instanceof Array || (typeof(k)==='string' || k instanceof String)){
						filter_query +=i+'='+encodeURIComponent(k)+'/';
					}
				});
				data.filter_query = filter_query;

				
				data.tabs = [];
				$(data.facet_result).each(function(){
					if(this.facet_type=='class'){
						$.each(this.values, function(){
							var new_tab = {
								title: $filter('class_name')(this.title),
								inc_title: this.title,
								count: this.count
							};
							if(filter['class']==this.title) new_tab.current = true;
							data.tabs.push(new_tab);
						});
					}
				});
				
				//search data goes here
				var template = $('#search-result-template').html();
				var output = Mustache.render(template, data);
				$('#'+search_id).html(output).show();
				if($('.tabs a.current').length==0) $('.tabs a:first-child').addClass('current');

				//facets
				if($('.theme_facet[search-id='+search_id+']').length>0){
					var facet_type = $('.theme_facet[search-id='+search_id+']').attr('facet-type');
					var facet_data = '';
					$(data.facet_result).each(function(){
						if(this.facet_type==facet_type){
							facet_data = this;
						}
					});
					$(facet_data.values).each(function(){
						this.inc_title = encodeURIComponent(this.title);
					});
					var template = $('#facet-template').html();
					var output = Mustache.render(template, facet_data);
					$('.theme_facet[search-id='+search_id+']').html(output).show();
				}

				// var template = $('#facet-template').html();
				// var output = Mustache.render(template, data);
				// $('.theme_facet[search-id='+search_id+']').html(output).show();
			});
		});
	});
