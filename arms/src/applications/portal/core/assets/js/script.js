$(document).ready(function() {
    var metadataContainer = $('#registryObjectMetadata');

	$('.text_select').each(function() {
		var $this = $(this),
			$input = $this.find('input'),
			$ul = $this.find('ul'),
			$span = $this.find('.default_value');
		$input.val('');	
		var emptyValue = $span.text();
		$('<li />').text(emptyValue).prependTo($ul);
		$this.click(function() {
			$ul.slideDown();
			$this.addClass('current');				
		});
		$this.mouseleave(function() {
			$ul.slideUp('fast');
			$this.removeClass('current');				
		});			
		$ul.find('li').click(function() {
			var value = $(this).text();
			if(value!=emptyValue) {
				$input.val($(this).text());
				$span.hide();
			} else {
				$input.val('');
				$span.show();				
			}
		});
	});
    $( "#range_slider" ).slider({
            range: true,
            min: 0,
            max: 500,
            values: [ 75, 300 ],
            slide: function( event, ui ) {
                $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            }
    });
    $("#slider").editRangeSlider({
    	bounds:{min: 1544, max: 2012},
    	defaultValues:{min: 1544, max: 2012},
    	valueLabels:"change",
    	type:"number",
    	arrows:false,
    	delayOut:400
	});

    $('#clear_search').click(function() {
    	var $form = $(this).parents('form');
    	$form.find('input[type="text"]').val('');
    	$form.find('input[type="checkbox"]').removeAttr('checked');
    	$form.find('option').attr('selected', false);
    	$form.find('select').find('option').first().attr('selected', true);
    	return false;
    });
    $('#ad_st').toggle(function() {
    	$(this).addClass('exped');
    	$('.advanced_search').slideDown();
    	$("#slider").editRangeSlider("resize");
    	return false;
    }, function() {
     	$(this).removeClass('exped');
    	$('.advanced_search').slideUp('fast');
    	$("#slider").editRangeSlider("resize");
    	return false;
    });

    $('#search_box').keypress(function(e){
		if(e.which==13){//press enter
			window.location = base_url+'search/#!/q='+$(this).val();
		}
	});

	$('#search_map_toggle').click(function(e){
		window.location = base_url+'search/#!/map=show';
	});

	$('#adv_start_search').click(function(e){
		e.preventDefault();
		var q = '';
		var all = $('.adv_all').val();
		var input = $('.adv_input').val();
		var nots = $('.adv_not');
		var not = '';
		$.each(nots, function(e){
			var v = $(this).val();
			if(v!='')not +='-'+v+' ';
		});
		if(all!='') q +='"'+all+'" ';
		q += input+ ' '+not;
		var tab = $('#record_tab').val();
		var temporal = $("#slider").editRangeSlider("values");
		window.location = base_url+'search/#!/q='+q+'/tab='+tab+'/temporal='+Math.round(temporal.min)+'-'+Math.round(temporal.max);
	});



    $('#ands_subject_match').click(function(e){
        e.preventDefault();
        updateAccordion($('#suggested_links_accordion'),"Record with matching subjects", "ands_subjects", 0, 10);
        $( "#dialog-modal" ).dialog({
          maxHeight: 640,
          width:600,
          position: { my: "center", at: "center", of: window },
          draggable: false, resizable: false,
          closeText: "x"
        });
    });

    $('#ands_identifier_match').click(function(e){
        e.preventDefault();
        updateAccordion($('#suggested_links_accordion'),"Record with matching identifiers", "ands_identifiers", 0, 10);
        $( "#dialog-modal" ).dialog({
          maxHeight: 640,
          width:600,
          position: { my: "center", at: "center", of: window },
          draggable: false, resizable: false,
          closeText: "hide"
        });
    });

     jQuery('body')
      .bind(
       'click',
       function(e){
        if(
         jQuery('#dialog-modal').dialog('isOpen')
         && !jQuery(e.target).is('.ui-dialog, a')
         && !jQuery(e.target).closest('.ui-dialog').length
        ){
         jQuery('#dialog-modal').dialog('close');
        }
       }
      );



     $('a.next_accordion_query').live('click', function(e){
        e.preventDefault();
        e = $(this);
        updateAccordion($('#suggested_links_accordion'),$('span.ui-dialog-title', e.parent().parent().parent()).html(), e.attr("data-suggestor"), e.attr("data-start"), e.attr("data-rows"));
    });



    function updateAccordion(container, title, suggestor, start, rows)
    {
        if (isPublished())
        {
            var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+start+"/"+rows+"/?slug=" + getSLUG();
        }
        else
        {
            var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+start+"/"+rows+"/?id=" + getRegistryObjectID();
        }


        $.get(base_url + url_suffix, function(data)
        {   
            var ui_dialog = container.parent().parent().parent();
            $('span.ui-dialog-title', ui_dialog).html(title);
            container.html("<ul></ul>");
            for (var link in data['links'])
            {
                // This is a link to a record
                if (data['links'][link]['slug'])
                {
                    var ro_class = data['links'][link]['class'];
                    var icon = '<img src="'+base_url+'assets/core/images/icons/collections.png" alt="collections" class="icon_sml">'
                    $('ul', container).append('<li>'+icon+'<a href="">'+data['links'][link]['title']+'</a></li>');
                    var target = $('ul li', container).last();
                    target.on('click',function(e){e.preventDefault(); $(this).qtip('show');});
                    generatePreviewTip(target, data['links'][link]['slug'], null);
                }
                else
                {
                    // This is an external link, XXX: outbound link image?
                    $('ul', container).append('<li><a href="'+data['links'][link]['url']+'">'+data['links'][link]['title']+'</a></li>');
                }
            }

            // Footer for "previous/more"
            container.append('<hr/>');
            start = parseInt(start); rows = parseInt(rows);
            if (start > 0)
            {
                container.append('<a style="float:left;" href="#" class="next_accordion_query" data-suggestor="'+suggestor+'" data-start="'+(start-rows)+'" data-rows="'+rows+'">&lt; previous</a>');
            }

            if(data.count >= (start+rows))
            {
                container.append('<a style="float:right;" href="#" class="next_accordion_query" data-suggestor="'+suggestor+'" data-start="'+(rows+start)+'" data-rows="'+rows+'">more &gt;</a>');
            }

        },'json');
    }


	function getURLParameter(name) 
	{
	    return unescape(
	        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	    );
	}


    /*************/
    /* view page */
    /*************/

    function isPublished()
    {
        return ($('#status', metadataContainer).html() == "PUBLISHED");
    }

    function getRegistryObjectID()
    {
        return $('#registry_object_id', metadataContainer).html();
    }

    function getSLUG()
    {
        return $('#slug', metadataContainer).html();
    }

    function traverseAndSelectChildren(tree, select_id)
    {
        for (var i = tree.length - 1; i >= 0; i--) {
            if (tree[i].registry_object_id == select_id)
            {
                tree[i].select = true;
                tree[i].expand = true;
                tree[i].focus = true;
               // tree[i].activate = true;
            }
            else
            {
                if (tree[i].children)
                {            
                    tree[i].children = traverseAndSelectChildren(tree[i].children, select_id);
                }
            }
        }
        return tree;
    }

    // Attach the dynatree widget to an existing <div id="tree"> element
    // and pass the tree options as an argument to the dynatree() function:
    var connection_params = {}
    if (isPublished())
    {
        connection_params.slug = getSLUG();
    }
    else
    {
        connection_params.id = getRegistryObjectID();
    }
    $.get( base_url+"/view/connectionGraph",
    		connection_params,
	        function(data)
	        {
                if (data && data.length>=1 && data[0].children.length>0)
                {
                  
                     //console.log(data);
                    traverseAndSelectChildren(data, getRegistryObjectID()); 

    	        	/* Generate the tree */
    	        	$("#connectionTree").dynatree({
            			children: data,
                        onActivate: function(node) {
                            // If this has more parts, open them...
                            if (node.data.children)
                            {
                                node.expand();
                            }
                        },
    			        onClick: function(node) {
                            if (node.data.registry_object_id != getRegistryObjectID())
                            {
                                $('#' + node.li.id).qtip('show'); 
                            }

                            // XXX: show the tooltip
    			            // A DynaTreeNode object is passed to the activation handler
    			            // Note: we also get this event, if persistence is on, and the page is reloaded.
    			           //window.location = base_url + node.data.slug;
    			        },

    			        onDblClick: function(node) {
    			        	// Change to view this record
    			        	if (isPublished())
    			        	{
    			        		window.location = base_url + node.data.slug;
    			        	}
    			        	else
    			        	{
    			        		window.location = base_url + "view/?id=" + node.data.registry_object_id;
    			        	}
    			        },

    			        onPostInit: function (isReloading, isError)
    			        {
                            // Hackery to make the nodes representing THIS registry object
                            // visible, but not highlighted
                            nodes = this.getSelectedNodes();
                            for (var i = nodes.length - 1; i >= 0; i--) {
                                nodes[i].activate();
                                nodes[i].deactivate();
                            };
    			        },

    			        onRender: function(node, nodeSpan) {

                            /* Change the icon in the tree */
    			        	if (node.data.class=="collection")
    			        	{
    					    	$(nodeSpan).find("span.dynatree-icon").css("background-position", "-38px -155px");
    					    }
                            else if (node.data.class=="party")
                            {
                                $(nodeSpan).find("span.dynatree-icon").css("background-position", "-19px -155px");
                            }
                            else if (node.data.class=="service")
                            {
                                $(nodeSpan).find("span.dynatree-icon").css("background-position", "0px -156px");
                            }
                            else if (node.data.class=="activity")
                            {
                                $(nodeSpan).find("span.dynatree-icon").css("background-position", "-57px -155px");
                            }

                            var preview_url;
                            if (isPublished())
                            {
                                preview_url = base_url + "preview/" + node.data.slug;
                            }
                            else
                            {
                                preview_url = base_url + "preview/?id=" + node.data.registry_object_id;
                            }

                            /* Prepare the tooltip preview */
                            $('#' + node.li.id).qtip({
                                content: {
                                    text: 'Loading preview...',
                                    ajax: {
                                        url: preview_url, 
                                        type: 'GET',
                                        data: { "slug": node.data.slug, "registry_object_id": node.data.registry_object_id },
                                        success: function(data, status) {
                                            data = $.parseJSON(data);                                       
                                            this.set('content.text', data.html);

                                            if (isPublished())
                                            {
                                                $('.viewRecordLink').attr("href",base_url + data.slug);
                                            }
                                            else
                                            {
                                                $('.viewRecordLink').attr("href",base_url+"view/?id=" + data.registry_object_id);
                                            }
                                        } 
                                    }
                                },
                                position: {
                                    my: 'left center',
                                    at: 'right center',
                                    target: $('#' + node.li.id + " > span")
                                },
                                show: {
                                    event: 'none',
                                    solo: true
                                },
                                hide: {
                                    delay: 1000,
                                    fixed: true,
                                },
                                style: {
                                    classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
                                    width: 700,
                                }
                            });
    					},

    			        persist: false,
                        generateIds: true,
    			        autoCollapse: false,
    			        activeVisible: true,
    			        autoFocus: false,
    			        clickFolderMode: 3, // 1:activate, 2:expand, 3:activate and expand
    			        imagePath: "/",
    			        debugLevel: 0
    			    });	
                $('#collectionStructureWrapper').show();
                }
	        }, 
	        'json'
	);


    function generatePreviewTip(element, slug, registry_object_id)
    {
        var preview_url;
        if (isPublished())
        {
            preview_url = base_url + "preview/" + slug;
        }
        else
        {
            preview_url = base_url + "preview/?id=" + registry_object_id;
        }

        console.log(element);

        /* Prepare the tooltip preview */
        $('a', element).qtip({
            content: {
                text: 'Loading preview...',
                ajax: {
                    url: preview_url, 
                    type: 'GET',
                    data: { "slug": slug, "registry_object_id": registry_object_id },
                    success: function(data, status) {
                        data = $.parseJSON(data);                                       
                        this.set('content.text', data.html);

                        if (isPublished())
                        {
                            $('.viewRecordLink').attr("href",base_url + data.slug);
                        }
                        else
                        {
                            $('.viewRecordLink').attr("href",base_url+"view/?id=" + data.registry_object_id);
                        }
                    } 
                }
            },
            position: {
                my: 'left center',
                at: 'right center',
            },
            show: {
                event: 'click',
                solo: true
            },
            hide: {
                delay: 1000,
                fixed: true,
            },
            style: {
                classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
                width: 700,
            }
        });
    }

	//setTimeout(function(){alert("Hello")},3000)

});