$(document).ready(function() {

// Pointer to DOM element containing metadata about this registryObject
var metadataContainer = $('#registryObjectMetadata');
var loading_icon = '<div style="width:100%; padding-top:40px; text-align:center;"><img src="'+base_url+'assets/core/images/ajax-loader.gif" alt="Loading..." /></div>';
var ACCORDION_MODE_SUGGESTORS = ['datacite'];

// Check if we have a hierarchal connections graph
initConnectionGraph();

// If we're a collection, then hit DataCite for SeeAlso
if ( $('#class', metadataContainer).html() == "Collection" )
{
    initDataciteSeeAlso();
}



function initDataciteSeeAlso()
{

    // Get the suggested link count
    var suggestor = "datacite";
    if (isPublished())
    {
        var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+0+"/"+0+"/?slug=" + getSLUG();
    }
    else
    {
        var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+0+"/"+0+"/?id=" + getRegistryObjectID();
    }

    $.get( base_url+url_suffix,
            function(data)
            {
                if (parseInt(data.count) >= 0)
                {
                    datacite_explanation =  "<h3>About DataCite</h3>" + 
                                            "<div class='about_datacite'>Datacite is a not-for-profit orginisation formed in London on 1 December 2009." +
                                            "<p>DataCite's aim is to:</p>" +
                                            "<ul>" +
                                            "<li>Establish easier access to research data on the internet</li>" +
                                            "<li>Increase acceptance of research data as legitimate, citable contributions to the scholarly record</li>" +
                                            "<li>Support data archiving that will permit results to be verified and re-purposed for further study.</li>" +
                                            "</ul>" +
                                            "<p>For more information about DataCite, visit <a href='http://datacite.org'>http://datacite.org</a></p></div>";

                    datacite_qmark = "<img class='datacite_help' src='"+base_url+"assets/core/images/question_mark.png' width='12px' />";
                    $('#DataCiteSuggestedLinksBox').html(
                                                        '<h4>External Records</h4>' +
                                                        '<h5><a href="#" class="show_accordion" data-title="Records suggested by DataCite" data-suggestor="'+suggestor+'" data-start="0" data-rows="10"> ' + data.count + " records</a> from DataCite " + datacite_qmark + "</h5>"
                                                        );
                    $('#DataCiteSuggestedLinksBox').fadeIn();

                        
                    $('.datacite_help').qtip({
                        content: {
                            text: datacite_explanation,
                        },
                        style: {
                            classes: 'ui-tooltip-light ui-tooltip-shadow datacite-about',
                            width: 400,
                        },
                        show: {
                            event: 'click',
                            solo: true
                        },
                        hide: {
                            delay: 1000,
                            fixed: true,
                        },
                        position: {
                            my: 'bottom right',
                            at: 'top center',
                        },
                    });
                }
            },
            'json'
    );

}

/* Hook to capture class="show_accordion" */
/* Note: will grab the current cursor and link target from
         bound data- attributes */
$('.show_accordion').live('click', function(e){
    e.preventDefault();
     $( "#links_dialog" ).dialog({
      maxHeight: 640,
      width:600,
      position: { my: "center", at: "center", of: window },
      draggable: false, resizable: false,
      closeText: "hide"
    });
    updateLinksDisplay($('#links_dialog'),$(this).attr('data-title'), $(this).attr('data-suggestor'), $(this).attr('data-start'), $(this).attr('data-rows'));
});


/* Updates the contents of an accordion window */
function updateLinksDisplay(container, title, suggestor, start, rows)
{
    // Loading icon as display loads...
    container.html(loading_icon);

    // Specify the web service endpoint
    if (isPublished())
    {
        var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+start+"/"+rows+"/?slug=" + getSLUG();
    }
    else
    {
        var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+start+"/"+rows+"/?id=" + getRegistryObjectID();
    }

    // Fire off the request
    $.get(base_url + url_suffix, function(data)
    {   
        // Change the title of the dialog box
        $('span.ui-dialog-title',container.parent().parent().parent()).html(title);


        // If we have expandable contents, display our links in the accordion format 
        if ($.inArray(suggestor,ACCORDION_MODE_SUGGESTORS) >= 0)
        {
            container.html("<div class='links_list' id='"+suggestor+"_links_list'></div>");

            link_contents = ''
            for (var link in data['links'])
            {

                link_contents += decodeURIComponent(data['links'][link]['expanded_html']);
            }

            $("#"+suggestor+"_links_list",container).append( link_contents ).accordion({ header: "h3" });

        }

        // Else we display these as links with preview popups
        else
        {
            container.html("<ul class='links_list'></ul>");
            var class_icon = getClassIconSrc(data['links'][link]['class']);
            
            if (class_icon)
            {
                var icon = '<img src="'+ class_icon +'" alt="Class Icon" class="icon_sml">';
            }
            else
            {
                var icon = '';
            }

            $('ul', container).append('<li>'+icon+'<a href="">'+data['links'][link]['title']+'</a></li>');

            var target = $('ul li', container).last();

            // Create the tooltip
            generatePreviewTip(target, data['links'][link]['slug'], null);
             // Bind the tooltip show
            target.on('click',function(e){e.preventDefault(); $(this).qtip('show');});
        }

        // Footer for "previous/more"
        container.append('<hr/>');
        start = parseInt(start); rows = parseInt(rows);
        if (start > 0)
        {
            container.append('<a style="float:left;" href="#" class="next_accordion_query" data-title="'+title+'" data-suggestor="'+suggestor+'" data-start="'+(start-rows)+'" data-rows="'+rows+'">&lt; previous</a>');
        }

        if(data.count >= (start+rows))
        {
            container.append('<a style="float:right;" href="#" class="next_accordion_query" data-title="'+title+'" data-suggestor="'+suggestor+'" data-start="'+(rows+start)+'" data-rows="'+rows+'">more &gt;</a>');
        }

    },'json');
}

$('a.next_accordion_query').live('click', function(e){
    e.preventDefault();
    e = $(this);
    updateAccordion($('#links_dialog'), e.attr("data-title"), e.attr("data-suggestor"), e.attr("data-start"), e.attr("data-rows"));
});



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

function getClassIconSrc(ro_class)
{
    switch(ro_class)
    {
        case "collection":
            return base_url+'assets/core/images/icons/collections.png';
        break;
        case "party":
            return base_url+'assets/core/images/icons/parties.png'
        break;
        case "service":
            return base_url+'assets/core/images/icons/services.png'
        break;
        case "activity":
            return base_url+'assets/core/images/icons/activities.png'
        break;
        default:
            return false;
    }
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


function initConnectionGraph()
    {
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

}



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


});