// Pseudo-namespace with automatic evaluation. 
(function(){
	var WIDGET_NAMESPACE = "ands_widget";
	
	var aStrDefs = {
		
		ands_search_query:"*:*",
		ands_search_query_mode:"query", // alt. "facet"
		ands_search_query_facet:"",
		ands_search_query_facet_incl_count:"",
		
		ands_search_sort:"search_base_score desc",
		
		ands_search_service_point:"http://services.ands.org.au:8080/solr/",
		ands_search_portal_url:"http://services.ands.org.au/home/orca/rda/",
		
		ands_search_widget_type:"right-box",
		
		ands_search_width:"",
		ands_search_height:"",
		
		ands_search_record_limit:10,
		
		ands_search_include_description:false,
		ands_search_title:"",
		ands_search_desc:"",
		ands_search_heading:"Search Results",
		ands_search_bg:"#fff",
		ands_search_border:"#999",		
	}
	
	function makeWidget(ands_widget_reference_id)
	{
		var win = window,
		    doc = document;
		
		initVars(win, ands_widget_reference_id);
		
		var ands = win[WIDGET_NAMESPACE][ands_widget_reference_id];
		
		g = "<div class='ands_widget_wrapper " + ands.ands_search_widget_type + "' id='ands_search_widget_" + ands.ands_widget_reference_id + "'" 
			+ ' style="position:relative;' 
			+ (ands.ands_search_bg != "" ? 'background-color:' + ands.ands_search_bg +';' : '')
			+ (ands.ands_search_border != "" ? 'border:1px solid' + ands.ands_search_border +';' : '')
			+ (ands.ands_search_width != "" ? 'width:' + ands.ands_search_width + 'px;' : "")
			+ (ands.ands_search_height != "" ? 'height:' + ands.ands_search_height + 'px;' : "")
			+ '">'
			+ (ands.ands_search_heading != "" ? "<h2>" + ands.ands_search_heading + "</h2>" : "")
			+ "<div class='ands_search_widget_results'>"
			+ "<ul>"
			+ "</ul>"
			+ "</div>" 
			+ '</div>';

		doc.write(g);
		
		var search_params = 'select/?';
		if (ands.ands_search_query_facet != "")
		{
			search_params += 'q='+encodeURIComponent(ands.ands_search_query);
			search_params += '&rows=0'; // no rows output in facet mode
			search_params += '&indent=on&wt=json';
			search_params += (ands.ands_search_sort != "" ? '&sort='+encodeURIComponent(ands.ands_search_sort) : '');
			search_params += '&facet=true&facet.mincount=1';
			search_params += '&facet.field=' + ands.ands_search_query_facet;
		}
		else
		{
			search_params = 'select/?q='+encodeURIComponent(ands.ands_search_query)
								+ '&version=2.2&start=0&rows='+(parseInt(ands.ands_search_record_limit)+1)
								+ '&indent=on&wt=json'
								+ (ands.ands_search_sort != "" ? '&sort='+encodeURIComponent(ands.ands_search_sort) : '');					
		}
		
		$.getJSON(ands.ands_search_service_point 
				+ search_params
				+ '&int_ref_id=' + ands.ands_widget_reference_id
				+ '&json.wrf=?',
			function (data)
			{
				var ands = win[WIDGET_NAMESPACE][data['responseHeader']['params']['int_ref_id']];
				if (!ands)
				{
					console.log('Error - returned widget data does not match a reference ID');
					return;
				}
				
				var widget_results = $('#ands_search_widget_' + ands.ands_widget_reference_id + ' .ands_search_widget_results ul');

				
				// Facetted results behave differently
				if (ands.ands_search_query_facet != "")
				{
					
					if (data['facet_counts']['facet_fields'][ands.ands_search_query_facet])
					{
						var t;
						$.each(data['facet_counts']['facet_fields'][ands.ands_search_query_facet], function(key, doc) {
							// every second field is the count (bizarre)
							if (key%2==0)
							{
								t = doc;
							}
							else
							{
								widget_results.append("<li><a href='" 
									+ ands.ands_search_portal_url 
									+ "search#!/q=" + t
									+ "'>" + t + (ands_search_query_facet_incl_count ? " (" + doc + ")" : "") + "</a></li>");
							}

							
						});
					}
					$("li:gt(5)", widget_results).hide(); 
					$("li:nth-child(6)", widget_results).after("<a href='#' class=\"more\">More...</a>");
					$("a.more").live("click", function() {
						$(this).parent().children().slideDown();
						$(this).remove();
					    return false;
					});
				}
				else if (data['response']['numFound'] == 0)
				{
					widget_results.append('<li class="no_results">No matching records...</li>');
				}
				else
				{
					$.each(data['response']['docs'], function(key, doc) {
						
						if (key < ands.ands_search_record_limit)
						{ 
							widget_results.append("<li><a href='" 
									+ ands.ands_search_portal_url 
									+ "view/?key=" + encodeURIComponent(doc['key'])
									+ "'>" + doc['list_title'] + "</a></li>");
						}
						
					});
					
					if (data['response']['numFound'] > ands.ands_search_record_limit)
					{
						widget_results.append("<li><a href='"+ands.ands_search_portal_url+"search#!/q="+encodeURIComponent(ands.ands_search_query)+"'>More...</a></li>");
					}
					
				}
			}, 'jsonp'
		);
		
	}
	
	/*
	*  Initialise the window variables by using those in the global scope
	*  (if available) or alternatively the defaults above.
	*/
	function initVars(win, id)
	{
		if (!win[WIDGET_NAMESPACE]) { win[WIDGET_NAMESPACE] = {}; }
		win[WIDGET_NAMESPACE][id] = {'ands_widget_reference_id':id};
		
		for (var def in aStrDefs)
		{
			if (!win[def])
			{
				win[WIDGET_NAMESPACE][id][def] = aStrDefs[def];
			}
			else
			{
				win[WIDGET_NAMESPACE][id][def] = decodeURIComponent(win[def].replace('%20',' '));
			}
			win[def] = '';
		}
	}
	

	makeWidget(ands_search_id);
}) ()