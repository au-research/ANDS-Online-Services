$(document).ready(function(){
	var search_term = $('#search-box').val();
	var page = 1;
	var classFilter = $('#classSelect').val();
	var typeFilter = 'All';
	var groupFilter = 'All';
	var subjectFilter = 'All';
	var advanced_search_term = '';
	var spatial_included_ids = '';
	var temporal = 'All';
	var doTemporalSearch = false;
	var n = '';var e = '';var s='';var w='';
	var resultSort = 'score desc';
	var researchGroupSort = 'index';
	var subjectSort = 'index';
	var typeSort = 'index';

	//router
		
	if(window.location.href.indexOf('https://')==0){
		var thisurl = window.location.href;
		thisurl = thisurl.replace('https://','http://');
		window.location.href=thisurl;
	}
		
	if(window.location.href.indexOf('/view')>=0){
		initViewPage();
		if(window.location.href.indexOf('printview')>=0) initPrintViewPage();
	}else if((window.location.href==base_url) || (window.location.href==base_url+'index.php')){
		initHomePage();
	}else if(window.location.href==secure_base_url){
		window.location.href=base_url;
	}else if(window.location.href.indexOf('search')>=0){
		initSearchPage();
	}else if(window.location.href.indexOf('contact')>=0){
		initContactPage();
	}else if(window.location.href.indexOf('help')>=0){
		initHelpPage();
	}else if(window.location.href.indexOf('preview')>=0){
		initPreviewPage();
	}
	$('#clearSearch').tipsy({live:true, gravity:'se'});
	
	function initSearchPage(){
		$('.disable-info').live('click',function(){
			//console.log($.cookie('spatial-info'));
			var info = $(this).parent().parent().attr('id');
			if(info=='spatial-info') $.cookie('spatial-info','read');
			$(this).parent().parent().fadeOut();
		});
	}
	
	var hash = window.location.hash;
	//console.log(hash);
	
	/*GET HASH TAG*/
	$(window).hashchange(function(){
		//var hash = window.location.hash;
		var hash = location.href.substr(location.href.indexOf("#"));
		//console.log('Hash Change: '+ hash + '<br/>');
		$('#date-slider').slider({//date slider for advanced search
			range: true,
			min:1544,
			max:2011,
			values: [ 1544, 2011 ],
			slide: function( event, ui ) {
				temporal = ui.values[0]+'-'+ui.values[1];
				$('#dateFrom').val(ui.values[0]);
				$('#dateTo').val(ui.values[1]);
			},
			stop: function(event, ui) {
				changeHashTo(formatSearch(search_term, page, classFilter));
			}
		});
		//console.log($('#date-slider').slider('option', 'values'));
		var query = hash.substring(3, hash.length);
		var words = query.split('/');
		clearFilter();
		$.each(words, function(){
			var string = this.split('=');
			var term = string[0];
			var value = string[1];
			switch(term){
				case 'q':search_term=value;break;
				case 'p':page=value;break;
				case 'tab':classFilter=value;break;
				case 'group':groupFilter=encodeURIComponent(decodeURIComponent(value));break;
				case 'type':typeFilter=encodeURIComponent(decodeURIComponent(value));break;
				case 'subject':subjectFilter=encodeURIComponent(decodeURIComponent(value));break;
				case 'temporal':temporal=value;break;
				case 'n':n=value;break;
				case 'e':e=value;break;
				case 's':s=value;break;
				case 'w':w=value;break;
				case 'resultSort':resultSort=value;break;
				case 'researchGroupSort':researchGroupSort=value;break;
				case 'subjectSort':subjectSort=value;break;
				case 'typeSort':typeSort=value;break;
			}
		});
		if(classFilter!=$('#classSelect').val()) {
			$('#classSelect').val(classFilter);
		}
		//console.log('term='+search_term+'page='+page+'tab='+classFilter);
		search_term = decodeURIComponent(search_term);
		if(window.location.href.indexOf('search')>=0) {
			//console.log('yea');
			search_term = search_term.replace(/ or /g, " OR ");//uppercase the ORs
			search_term = search_term.replace(/ and /g, " AND ");//uppercase the ANDS
			doSearch();
		}
		if((search_term!='*:*') && (search_term!='')){
			$('input').val('');
			$('#search-box').val(search_term);
			$('#advanced-exact').val('');$('#advanced-or1').val('');$('#advanced-or2').val('');$('#advanced-or3').val('');$('#advanced-not').val('');
			$('#address').val('');
			populateAdvancedFields(search_term);
			$('#clearSearch').show();
			//$('#advanced-all').val(search_term);
		}else{
			$('#search-box').val('');$('#clearSearch').hide();
		}
		if(temporal!='All') {
			doTemporalSearch = true;
			refreshTemporalSearch();
		}else{
			refreshTemporalSearch();
		}
	});
	$(window).hashchange(); //do the hashchange on page load
	
	function refreshTemporalSearch(){
		//console.log(doTemporalSearch);
		if(doTemporalSearch){
			$('#show-temporal-search').attr('src',base_url+'img/yes.png');
			//console.log(parseInt(word[1]));
			if(temporal!='All'){
				var word = temporal.split('-');
			}else{
				var word = [1544,2011];
			}
			$('#dateFrom').val(word[0]).attr('disabled','');
			$('#dateTo').val(word[1]).attr('disabled','');
			$("#date-slider").slider("option", "disabled", false );
			$('#date-slider').slider("values", 0, parseInt(word[0]));
			$('#date-slider').slider("values", 1, parseInt(word[1]));
		}else{
			$('#show-temporal-search').attr('src',base_url+'img/no.png');
			$('#dateFrom').val(1544);
			$('#dateTo').val(2011);
			$('#dateFrom').attr('disabled','true');
			$('#dateTo').attr('disabled','true');
			$( "#date-slider" ).slider( "option", "disabled", true );
		}
	}
	
	function populateAdvancedFields(search_term){
		var word = search_term.split(' ');
		
		//getting ors
		var ors = [];
		$('#advanced-or1, #advanced-or2, #advanced-or3').val('');
		$.each(word, function(index){
			if(this.toString()=='OR'){
				//console.log($.inArray(word[index-1], ors));
				if($.inArray(word[index-1], ors)==-1)ors.push(word[index-1]);
				if($.inArray(word[index+1], ors)==-1)ors.push(word[index+1]);
			}
		});
		if(ors[0]) $('#advanced-or1').val(ors[0]);
		if(ors[1]) $('#advanced-or2').val(ors[1]);
		if(ors[2]) {
			var lastor = [];
			$.each(ors, function(index){
				if(index>=2){
					lastor.push(this.toString());
				}
			});
			lastor = lastor.join(' OR ');
			$('#advanced-or3').val(lastor);
		}
		
		//getting the exact
		first = (search_term.indexOf('"'));
		rest = search_term.substring(first+1, search_term.length);
		second = (rest.indexOf('"'));
		exact = rest.substring(0,second);
		
		var exacts = exact.split(' ');
		$.each(exacts, function(){
			ors.push(this.toString());
		});
		
		//getting the others
		var nots = '';var exacts='';var full='';
		$.each(word, function(){
			var str = this.toString();
			if(str.indexOf('-')==0){//starts with -
				nots += str.substring(1, str.length)+ ' ';
			}else{//put the rest in if not already put in ors
				if(($.inArray(str, ors)==-1) && (str!='AND') && (str!='OR')) {
					if((str.indexOf('"')==-1)){
						full += str + ' ';
					}
				}
			}
		});
		$('#advanced-all').val(full);
		$('#advanced-not').val(nots);
		$('#advanced-exact').val(exact);
	}
	
	function formatSearch(term, page, classFilter){
		if(term=='') term ='*:*';
		var res = 'search#!/q='+encodeURIComponent(term)+'/p='+page;
		res+='/tab='+classFilter;
		if(typeFilter!='All') res+='/type='+(typeFilter);
		if(groupFilter!='All') res+='/group='+(groupFilter);
		if(subjectFilter!='All') res+='/subject='+(subjectFilter);
		if(temporal!='All') res+='/temporal='+(temporal);
		if(n!=''){
			res+='/n='+n+'/e='+e+'/s='+s+'/w='+w;
		}
		if(resultSort!='score desc') res+='/resultSort='+(resultSort);
		if(researchGroupSort!='index') res+='/researchGroupSort='+(researchGroupSort);
		if(subjectSort!='index') res+='/subjectSort='+(subjectSort);
		if(typeSort!='index') res+='/typeSort='+(typeSort);
		//alert(res);
		return res;
		
	}
	
	
	/*INIT*/
	
	/* 
	 * Auto complete for main search box 
	 * Use getDictionaryTerms for search terms
	 * Use getDictionaryTermsOLD for solr dictionary
	 * */
	$( "#search-box" ).autocomplete( {
		source: base_url+"view_part/getDictionaryTerms/",
		minLength: 2,
		delimiter:/(,|;)\s*/,
		select: function( event, ui ) {
			$('#search-box').val = ui.item.value;
			doSearch();
		}
	});

	$('#date-slider').slider({//date slider for advanced search
		range: true,
		min:1544,
		max:2011,
		values: [ 1544, 2011 ],
		slide: function( event, ui ) {
			temporal = ui.values[0]+'-'+ui.values[1];
			$('#dateFrom').val(ui.values[0]);
			$('#dateTo').val(ui.values[1]);
		},
		stop: function(event, ui) {
			changeHashTo(formatSearch(search_term, page, classFilter));
		}
	});
	$('#dateFrom, #dateTo').change(function(){
		var thedate = $(this).val();
		var index = 0;
		if($(this).attr('id')=='dateTo') index=1;
		$('#date-slider').slider("values", index, thedate);
	}).tipsy({gravity:'s'});
	$('ul.sf-menu').superfish({autoArrows:false, delay:50});//menu
	
	
	//$('#advanced').hide(); //already hides in css
	
	$('#clearSearch').live('click', function(){	//clearing search box, also clears everything
		clearEverything();
	});
	
	function clearEverything(){
		//clearing the values
		clearFilter();
		
		//clearing the displayed values
		$('#advanced-all').val('');
		$('#advanced-exact').val('');
		$('#advanced-or1').val('');
		$('#advanced-or2').val('');
		$('#advanced-or3').val('');
		$('#advanced-not').val('');
		$('#result-placeholder').html('');
		spatial_included_ids='';
    	for(i in drawingArrays){
    		if(drawingArrays[i]!=null){
    			drawingArrays[i].setMap(null);
    			drawingArrays[i]=null;
    		}
    	}
    	$('#clear-drawing').click();
    	n='';s='';e='';w='';
    	$('#start-drawing').hide();
        $('#clear-drawing').show();
		$(this).hide();
		if(window.location.href.indexOf('search')>0){
			$('#search-result').html('');//clear search result if we are on the search page
		}
	}
	
	function clearFilter(){
		search_term='';$('#search-box').val('');page = 1;
		classFilter = $('#classSelect').val();typeFilter = 'All';groupFilter = 'All';subjectFilter = 'All';
		advanced_search_term = '';spatial_included_ids = '';temporal = 'All';
		$('#clearSearch').hide();
		if(doTemporalSearch){
			$('#show-temporal-search').attr('checked','false');
			doTemporalSearch = false;
			refreshTemporalSearch();
		}
		resultSort='score desc';
	}
	
	var MAX_LOGO_WIDTH = 132;
	var MAX_LOGO_HEIGHT = 63;
	
	function testLogo(id, url){
		var img = $('#'+id)[0]; // Get my img elem
		//$('#'+id).hide();
		var pic_real_width, pic_real_height;
		$("<img/>") // Make in memory copy of image to avoid css issues
		    .attr("src", $(img).attr("src"))
		    .load(function() {
		    	resizeLogo(this, id);
		    	$('#'+id).show();
		    }).error(function(){
			  $('#'+id).hide();
			});
	}	
	
	function resizeLogo(image , id)
	{
		var maxWidth = MAX_LOGO_WIDTH; // Max width for the image
	    var maxHeight = MAX_LOGO_HEIGHT;    // Max height for the image
	    var width = image.width;    // Current image width
	    var height = image.height;  // Current image height
		if(width > maxWidth || height > maxHeight)
		{
			var hRatio = maxHeight / height;
			var wRatio = maxWidth / width;
	        if(hRatio > wRatio)
	    	{
	            h = height * wRatio;   // Set new height
	            w = width * wRatio;   	
	    	}
	        else
	        {
	            h = height * hRatio;   // Set new height
	            w = width * hRatio;   	
	    	}
	    	$('#' + id).css({ height: h, width: w });
		}
	}
	
	function initPreviewPage(){
		//$('#right').remove();
		drawMap();
		$('#view-in-orca').remove();

		$('.anzsrc-for, .anzsrc-seo, .anzsrc-toa').each(function(){
			var unresolved = $(this).attr('id');
			var subjectClass = $(this).attr('class');
			var item = $(this);
			//console.log(subjectClass);
			$.ajax({
               			type:"GET",
				url: service_url+"?subject="+unresolved+"&vocab="+subjectClass,
				        success:function(msg){ 
						//console.log(msg);
				        	item.text(msg);
				        },
				        error:function(msg){}
			});
		});

		$('.tipme').tipsy({live:true, gravity:'s'});

	}
	
	function initPrintViewPage(){
		//alert('init');
		$('#header').hide();
		$('.descriptions div').show();
		$('.descriptions div').height('auto');
		$('.showall_descriptions').hide();
		$('.tipsy').hide();
		window.print();
	}
	
	function initViewPage(){

		drawMap();//map drawing
		
		
		if(!$.browser.msie){
		//hide all descriptions and headings
		$('.descriptions div, .descriptions h3').hide();
		brief = null;
		//if there is a brief, brief is the first brief
		$('.descriptions div').each(function(){
			if (brief==null){
				if($(this).hasClass('brief')) {
					brief = this;
				}
			}
		});
		
		
		//if there is no brief, brief will be the first full
		if(brief==null){
			$('.descriptions div').each(function(){
				if(brief==null){
					if($(this).hasClass('full')) {
						brief = this;
					}
				}
			});
		}
		//if there is no brief or full, grab the first description
		if(brief==null){
			brief = $('.descriptions div')[0];
		}
		
		//limit to 10 lines, each line height is 17px;
		
		//if there are more than 1 description, show the more button
		if($('.descriptions div').length > 1){
			$('.showall_descriptions').show();
		}
		//the more button
		$('.showall_descriptions').live('click', function(){
			//show all descriptions and headings
			$(this).hide();
				$('.descriptions div, .descriptions h3').show();
			$('.descriptions div').css('height','auto');
				$('.descriptions div').css('overflow','auto');
				$('.descriptions table').show();
		});
		$(brief).show();
		}
		

		
		var key = $('#key').html();
		var itemClass = $('#class').text();
		//console.log(key);
		
		initConnectionsBox();//setup the connections Box
		
		if(itemClass=='Collection') {
			initSubjectsSEEALSO();
		}else if(itemClass=='Party') {
			initIdentifiersSEEALSO();
		}
		
		
		//identifier clickable
        $.each($('#identifiers p[name=uri]'), function(){//find in every identifiers
        	var ident = $(this).text();
    		if(ident.indexOf('http')==0){
    			//is a link
    			$(this).html('<a href="'+ident+'">'+ident+'</a>');
    		}
        });
		
		//ARCS RIGHTS ELEMENTS fix
		$('.rights').each(function(){
			var content = $(this).html();
			var img = '';
			if(content.indexOf('https://df.arcs.org.au/ARCS/projects/PICCLOUD')>0){
				var s = content.split('http');
				url = 'http'+s[1];
				var img = '<a href="'+url+'"><img src="http://polarcommons.org/images/PIC_print_small.png"/></a>';
				$(this).html(img);
			}
		});
		
		
       
        $('.ui-widget-overlay').live("click", function() {
        	$("#infoBox").dialog("close");
        });
        
        
        if($('#party_logo').length>0){//logo fix
        	var party_logo_url = $('#party_logo').attr('src');
        	testLogo('party_logo', party_logo_url);
        }
        return false;
	}
	
	function setupSeealsoBtns(){
		$('.button').button();
        $("#status").html($('#seeAlsoCurrentPage').html() + '/'+$('#seeAlsoTotalPage').html());
    }
	
	function initConnectionsBox(){

		   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//NEW CONNECTIONS
		var key_value=$('#key').text();
		//console.log(key_value);
		key_value = key_value.replace(/[+]/gi,"%2B");
        $.ajax({
                type:"POST",
                url: base_url+"search/connections/count",data:"q=relatedObject_key:"+key_value+"&key="+key_value,
                        success:function(msg){ 
                        	//alert(key_value);
                                $("#connections").html(msg);
                                $('ul.connection_list li a').tipsy({live:true, gravity:'s'});
                                if(parseInt($('#connections-realnumfound').html())==0){
	                            	$('#connectionsRightBox').hide();
	                            }
                                
                        },
                        error:function(msg){
                        	//alert(msg);
                        }
        });
		var connectionsPage = 1;
		
        $('.connections_NumFound').live('click', function(){
         	var types = $(this).attr("type_id");
         	var classes = $(this).attr("class_id");
         	if(!classes) var classes = 'party';
 
	        $.ajax({
                type:"POST",
                url: base_url+"search/connections/content/"+classes+"/"+types,data:"q=relatedObject_key:"+key_value+"&key="+key_value+"&page="+connectionsPage,
                    success:function(msg){
                             //console.log("success" + msg);
                            $("#connectionsInfoBox").html(msg);

                            $(".accordion").accordion({autoHeight:false, collapsible:true,active:false});
                            $("#connectionsInfoBox").dialog({
                                    modal: true,minWidth:700,position:'center',draggable:'false',resizable:false,
                            		title:"Connections",
                                    buttons: {
                                        '<': function() {
                                                if(connectionsPage > 1){
                                                	connectionsPage = connectionsPage - 1;
                                                        $('.accordion').html('Loading...');
                                                        getConnectionsAjax(classes,types, connectionsPage, key_value)
                                                }
                                        },
                                        '>': function() {
                                                if(connectionsPage < parseInt($('#connectionsTotalPage').html())){
                                                	connectionsPage = connectionsPage + 1;
                                                        $('.accordion').html('Loading...');
                                                        getConnectionsAjax(classes,types, connectionsPage, key_value)
                                                }
                                        }
                                    },
                                    open: function(){
                                        $(".ui-dialog-buttonset").append("<span id='status'></span>");
                                        setupConnectionsBtns();
                                        return false;
                                    }
                            });
                            
                           return false;   
                    },
                    error:function(msg){
                            //console.log("error" + msg);
                    }
	         });
	        return false;
        });
	}
	
	function initSubjectsSEEALSO(){
		//SEE ALSO FOR SUBJECTS
        var group_value = encodeURIComponent($('#group_value').html());
        //console.log(group_value);
        var key_value = $('#key').html();
        var subjectSearchstr = '';
        $('.subjectFilter').each(function(){
                //console.log($(this).attr('id'));
                subjectSearchstr += $(this).attr('id')+';';
        });
        subjectSearchstr = subjectSearchstr.substring(0,subjectSearchstr.length -1 );
        //console.log(subjectSearchstr);
        subjectSearchstr = encodeURIComponent(subjectSearchstr);
        $.ajax({
                type:"POST",
                url: base_url+"search/seeAlso/count/subjects",data:"q=*:*&classFilter=collection&typeFilter=All&groupFilter=All&subjectFilter="+subjectSearchstr+"&page=1&spatial_included_ids=&temporal=All&excluded_key="+key_value,
                        success:function(msg){
                                $("#seeAlso").html(msg);
                                //console.log(msg);
                                if(parseInt($('#seealso-realnumfound').html())==0){
	                            	$('#seeAlsoRightBox').hide();
	                            }
                        },
                        error:function(msg){
                                //console.log("error" + msg);
                        }
        });
		var seeAlsoPage = 1;
        $('#seeAlso_subjectNumFound').live('click', function(){
	        $.ajax({
                type:"POST",
                url: base_url+"search/seeAlso/content/subjects",data:"q=*:*&classFilter=collection&typeFilter=All&groupFilter=All&subjectFilter="+subjectSearchstr+"&page="+seeAlsoPage+"&spatial_included_ids=&temporal=All&excluded_key="+key_value,
                    success:function(msg){
                            //console.log("success" + msg);
                            $("#infoBox").html(msg);

                    		$(".accordion").accordion({autoHeight:false, collapsible:true,active:false});
                            //var seeAlso_display = $('#seeAlsoCurrentPage').html() + '/'+$('#seeAlsoTotalPage').html();
                            
                            $("#infoBox").dialog({
                                    modal: true,minWidth:700,position:'center',draggable:false,resizable:false,
                            		title:"ANDS Suggested Links",
                                    buttons: {
                                        '<': function() {
                                                if(seeAlsoPage > 1){
                                                        seeAlsoPage = seeAlsoPage - 1;
                                                        $('.accordion').html('Loading...');
                                                        getSeeAlsoAjax(group_value, subjectSearchstr, seeAlsoPage, key_value)
                                                }
                                        },
                                        '>': function() {
                                                if(seeAlsoPage < parseInt($('#seeAlsoTotalPage').html())){
                                                        seeAlsoPage = seeAlsoPage + 1;
                                                        $('.accordion').html('Loading...');
                                                        getSeeAlsoAjax(group_value, subjectSearchstr, seeAlsoPage, key_value)
                                                }
                                        }
                                    },
                                    open: function(){
                                        $(".ui-dialog-buttonset").append("<span id='status'></span>");
                                        setupSeealsoBtns();
                                        return false;
                                    }
                            }).height('auto');
                            
                            //$('#infoBox').dialog().prepend('<div id="something-here" style="border:1px solid black;height:400px;width:400px;"></div>');
                            //$('#infoBox').overlay();
                            
                           return false;   
                    },
                    error:function(msg){
                            //console.log("error" + msg);
                    }
	         });
	        return false;
        });
 
	}
	
	function initIdentifiersSEEALSO(){
		var key_value=$('#key').text();
		//SEE ALSO FOR IDENTIFIERS
        var identifiers = [];
        $.each($('#identifiers p'), function(){//find in every identifiers
        	identifiers.push($(this).html());
        	var ident = $(this).html();
        	if(ident.indexOf('nla.party-')>=0){
        		if(ident.indexOf('http://')==0){
        			//is a link
        			$('#endlink').prepend('<a href="'+ident+'">View the record for this Party in Trove</a><br/>');
        		}else{
					//is not a link
					$('#endlink').prepend('<a href="http://'+ident+'">View the record for this Party in Trove</a><br/>');
        		}
        	}
        });
        
        $.each($('.descriptions p'), function(){//find in every descriptions that contains the identifier some where for NLA parties
        	if($(this).html().indexOf('nla.party-') > 0){
        		var foundit = $(this).html();
        		var words = foundit.split(' ');
        		$.each(words, function(i, word){
        			if(word.indexOf('nla.party-')>=0){
        				identifiers.push(word);
        			}
        		});
        	}
        });
        
        $.each($('.descriptions p'), function(){//find in every descriptions that contains the identifier some where NHMRC and ARC
        	if($(this).html().indexOf('http://purl.org/au') > 0){
        		var foundit = $(this).html();
        		var words = foundit.split(' ');
        		$.each(words, function(i, word){
        			if(word.indexOf('http://purl.org/au')>=0){
        				identifiers.push(word);
        			}
        		});
        	}
        });

        //console.log(identifiers);
        if (identifiers.length > 0){
	        var identifierSearchString = '+fulltext:(';
	        var first = true;
	        $(identifiers).each(function(){
	        	if(first){
	        		identifierSearchString +='"'+this+'"';
	        		first = false;
	        	}else{
	        		identifierSearchString += ' OR "'+this+'"';
	        	}
	        });
	        identifierSearchString +=')';
	        //console.log(identifierSearchString);
	        identifierSearchString = encodeURIComponent(identifierSearchString);
	        
	        var relatedClass = $('#class').html();
	        
	        $.ajax({
	            type:"POST",
	            url: base_url+"search/seeAlso/count/identifiers"+relatedClass,
	            data:"q=*:*&classFilter=party;activity&typeFilter=All&groupFilter=All&subjectFilter=All&page=1&spatial_included_ids=&temporal=All&excluded_key="+key_value+'&extended='+identifierSearchString,
	                    success:function(msg){
	                            $("#seeAlso-IdentifierBox").html(msg);
	                            if(parseInt($('#seealso-realnumfound').html())==0){
	                            	$('#seeAlso-Identifier').hide();
	                            }
	                    },
	                    error:function(msg){
	                            //console.log("error" + msg);
	                    }
	        });
	        var seeAlsoPage = 1;
	        $('#seeAlso_identifierNumFound').live('click', function(){
		        $.ajax({
	                type:"POST",
	                url: base_url+"search/seeAlso/content/identifiers",
	                data:"q=*:*&classFilter=party;activity&typeFilter=All&groupFilter=All&subjectFilter=All&page=1&spatial_included_ids=&temporal=All&excluded_key="+key_value+'&extended='+identifierSearchString,
	                    success:function(msg){
	                            $("#infoBox").html(msg);
	                            $(".accordion").accordion({autoHeight:false, collapsible:true,active:false});
	                            $("#infoBox").dialog({
	                                    modal: true, minWidth:700,maxHeight:300,draggable:false,resizable:false,
	                            		title:"ANDS Suggested Links",
	                                    buttons: {
	                                        '<': function() {
	                                                if(seeAlsoPage > 1){
	                                                        seeAlsoPage = seeAlsoPage - 1;
	                                                        $('.accordion').html('Loading...');
	                                                        getSeeAlsoAjax(group_value, subjectSearchstr, seeAlsoPage, key_value)
	                                                }
	                                        },
	                                        '>': function() {
	                                                if(seeAlsoPage < parseInt($('#seeAlsoTotalPage').html())){
	                                                        seeAlsoPage = seeAlsoPage + 1;
	                                                        $('.accordion').html('Loading...');
	                                                        getSeeAlsoAjax(group_value, subjectSearchstr, seeAlsoPage, key_value)
	                                                }
	                                        }
	                                    },
	                                    open: function(){
	                                        $(".ui-dialog-buttonset").append("<span id='status'></span>");
	                                        setupSeealsoBtns();
	                                    }
	                            }).height('auto');
	                    },
	                    error:function(msg){
	                            //console.log("error" + msg);
	                    }
		         });
	        });
        }else{
        	$('#seeAlso-Identifier').hide();
        }
	}
	
	function setupConnectionsBtns(){
		$(".accordion").accordion({autoHeight:false, collapsible:true,active:false});
		$('.button').button();
        $("#status").html($('#connectionsCurrentPage').html() + '/'+$('#connectionsTotalPage').html());
    }	
	function getSeeAlsoAjax(group_value, subjectSearchstr, seeAlsoPage, key_value){
		 $.ajax({
             type:"POST",
             url: base_url+"search/seeAlso/content",data:"q=*:*&classFilter=collection&typeFilter=All&groupFilter=All&subjectFilter="+subjectSearchstr+"&page="+seeAlsoPage+"&spatial_included_ids=&temporal=All&excluded_key="+key_value,
                     success:function(msg){
                             $(".accordion").html(msg);
                             $(".accordion").accordion({autoHeight:false, collapsible:true,active:false});
                             setupSeealsoBtns();
                     },
                     error:function(msg){}
             });
	}
	
	function getConnectionsAjax(classes,types,connectionsPage, key_value){
		 $.ajax({
            type:"POST",
            url: base_url+"search/connections/content/"+classes+"/"+types,data:"q=relatedObject_key:"+key_value+"&key="+key_value+"&page="+connectionsPage,
                    success:function(msg){
                            $(".accordion").html(msg);
                            setupConnectionsBtns();
                    },
                    error:function(msg){}
            });
	}
	
	
	function drawMap(){//drawing the map on the left side
		if($('p.coverage').length > 0){//if there is a coverage
			var myOptions = {
		      zoom: 2,disableDefaultUI: true,center:latlng,panControl: true,zoomControl: true,mapTypeControl: true,scaleControl: true,
		      streetViewControl: false,overviewMapControl: true,mapTypeId: google.maps.MapTypeId.TERRAIN
		    };
		    var map2 = new google.maps.Map(document.getElementById("spatial_coverage_map"),myOptions);
		    var bounds = new google.maps.LatLngBounds();
			
		    //draw coverages
		    var coverages = $('p.coverage');
		    //console.log(coverages.html());
		    //console.log(coverages.text());
		    var coverageText = coverages.text();
		    if(coverages.text().indexOf('northlimit') ==-1){
				if(validateLonLatText(coverageText)){	
					$.each(coverages, function(){
						coverage = $(this).html();
						split = coverage.split(' ');
						coords = [];
						$.each(split, function(){
							coord = stringToLatLng(this);
							coords.push(coord);
							bounds.extend(coord);
						});
						poly = new google.maps.Polygon({
						    paths: coords,
						    strokeColor: "#FF0000",
						    strokeOpacity: 0.8,
						    strokeWeight: 2,
						    fillColor: "#FF0000",
						    fillOpacity: 0.35
						});
						poly.setMap(map2);
						//console.log(poly);
					});
				}else{
					$('#spatial_coverage_map').hide();
					$('p.coverage').show();
				}
		    }else {
		    	//console.log(coverages);
		    	$.each(coverages, function(){
		    		coverage = $(this).html();
		    		split = coverage.split(';');
		    		
		    		$.each(split, function(){
						word = this.split('=');
						//console.log(word);
						if(jQuery.trim(word[0])=='northlimit') n=word[1];
						if(jQuery.trim(word[0])=='southlimit') s=word[1];
						if(jQuery.trim(word[0])=='eastLimit') e=word[1];
						if(jQuery.trim(word[0])=='eastlimit') e=word[1];
						if(jQuery.trim(word[0])=='westlimit') w=word[1];
					});
		    		coords = [];
		    		coords.push(new google.maps.LatLng(parseFloat(n), parseFloat(e)));
		    		coords.push(new google.maps.LatLng(parseFloat(n), parseFloat(w)));
		    		coords.push(new google.maps.LatLng(parseFloat(s), parseFloat(w)));
		    		coords.push(new google.maps.LatLng(parseFloat(s), parseFloat(e)));
		    		coords.push(new google.maps.LatLng(parseFloat(n), parseFloat(e)));
		    		
		    		$.each(coords, function(){
		    			bounds.extend(this);
		    		});
		    		
		    		poly = new google.maps.Polygon({
					    paths: coords,
					    strokeColor: "#FF0000",
					    strokeOpacity: 0.8,
					    strokeWeight: 2,
					    fillColor: "#FF0000",
					    fillOpacity: 0.35
					});

					poly.setMap(map2);
		    		//console.log(poly);
				});
		    }
			//draw centers
			var centers = $('.spatial_coverage_center');
			$.each(centers, function(){
				var marker = new google.maps.Marker({
		            map: map2,
		            position: stringToLatLng($(this).html()),
		            draggable: false,
		            raiseOnDrag:false,
		            visible:true
		        });
			});
			map2.fitBounds(bounds);
		}
	}
	
	/*
	 * Convert a string of a form (x, y) to a pair of latlng
	 */
	function stringToLatLng(str){
		var word = str.split(',');
		var lat = word[1];
		var lon = word[0];
		var coord = new google.maps.LatLng(parseFloat(lat), parseFloat(lon));
		return coord;
	}
    
	/*
	 * Do the Search (FULL SEARCH)
	 * Used in initSearchPage()
	 * */
	function doSearch(){
			//$('#advanced, #mid').css('opacity','0.5');
			$('#result-placeholder').html('Loading...');
			$('#loading').show();$('#clearSearch').hide();
			$('#map-stuff').hide();
			$('.ui-autocomplete').hide();
			$('#map-help-stuff').html('Please wait...');
			if(n!=''){
				doSpatialSearch();
			}else{
				doNormalSearch();
			}
	}
	
	function doSpatialSearch(){
		$('#result-placeholder').html('Loading');
        $('#loading').show();$('#clearSearch').hide();
        $('#map-stuff').hide();
		$('#map-help-stuff').html('Please wait...');
        $.ajax({
  			type:"POST",   
  			url: base_url+"/search/spatial/", 
  			data:"north="+n+"&south="+s+"&east="+e+"&west="+w,   
  				success:function(msg){
  					spatial_included_ids = msg;
  					//console.log(spatial_included_ids);
  					doNormalSearch();
  				},
  				error:function(msg){
  					//console.log('spatial: error'+msg);
  				}
  		});
	}
	
	function doNormalSearch(){
		//$('#search-result').css('opacity','0.7');
		//console.log("search_term" + encodeURIComponent(search_term));
		$.ajax({
  			type:"POST",
  			url: base_url+"search/filter/",
  			data:"q="+decodeURIComponent(search_term)+"&classFilter="+classFilter+"&typeFilter="+typeFilter+"&groupFilter="+groupFilter+"&subjectFilter="+subjectFilter+"&page="+page+"&spatial_included_ids="+spatial_included_ids+"&temporal="+temporal+'&sort='+resultSort,   
  				success:function(msg){
  					$("#search-result").html(msg);
  					$('#loading').hide();
  					//$('#advanced, #mid').css('opacity','1.0');
  					$('#map-stuff').show();
  					$('#map-help-stuff').html('');
  					//$('#search-result').css('opacity','1.0');
  					initFormat();
  					if($('#realNumFound').html() !='0'){//only update statistic when there is a result
  						//update search statistics
  						$.ajax({
  				  			type:"POST",
  				  			url: base_url+"search/updateStatistic/",
  				  			data:"q="+decodeURIComponent(search_term)+"&classFilter="+classFilter+"&typeFilter="+typeFilter+"&groupFilter="+groupFilter+"&subjectFilter="+subjectFilter+"&page="+page+"&spatial_included_ids="+spatial_included_ids+"&temporal="+temporal,   
  				  				success:function(msg){},
  				  				error:function(msg){}
  				  			});
  					}
  				},
  				error:function(msg){
  					console.log('error');
  				}
  		});	
	}
	
	/*
	 * Execute the functions only available in home page
	 */
	function initHomePage(){

		loadHPStat('score');
		
		//$('#content').sortable();
		$('.hp-icons img').hover(function(){
			id = $(this).attr('id');
			
			$('.hp-icon-content').hide();
			$('#hp-content-'+id).show();
			//console.log('#hp-content-'+id);
			$('.hp-icons img').removeClass('active');
			$(this).addClass('active');
		});
		
		$("#scrollable").scrollable({circular: true}).autoscroll(6000);
		var api = $("#scrollable").data("scrollable");
		api.onSeek(function() {
			var currentImageIndex = this.getIndex()+2;
			var prev = this.getIndex() + 1;
			var next = this.getIndex() + 3;
			currentKey = $("#items img:nth-child(" + currentImageIndex + ")").attr('alt');
			$('#items img').removeClass('current-scroll');
			$("#items img:nth-child(" + currentImageIndex + ")").addClass('current-scroll');
			currentDescription = $('div[name="'+currentKey+'"]').html();
			$('#display-here').html(currentDescription);
			$('#display-here a').tipsy({live:true, gravity:'w'});
		});
		$("#items img").click(function(){
			api.seekTo($(this).index()-1);
			if($(this).hasClass('current-scroll')){
				//console.log('current');
				var h1 = $('#display-here a').html();
				h1 = h1.replace('-','');
				changeHashTo(formatSearch(h1,1, 'activity'));
			}
		});
		
		$("#display-here").mouseenter(function() {
		  api.pause();
		}).mouseleave(function() {
		  api.play();
		});

		$('#items img:first').click();

		$('.hp-class-item').live('click', function(){
			var id = $(this).attr('id');
			resultSort='listTitle asc';
			changeHashTo(formatSearch(search_term,1,id));
		});
		
		
		function loadHPStat(sort){//load Home Page Stat
			$.ajax({
	  			type:"GET",   
	  			url: base_url+"/view_part/homepageStat/"+sort,   
	  				success:function(msg){
	  					$("#hp-stat").html(msg);
	  					
	  					var groupsList = $('#hp-groups');
	  					sortAlpha(groupsList);
	  					
	  					$("#hp-browse-collection").html('('+$('#hp-count-collection').html()+')');
	  					$("#hp-browse-party").html('('+$('#hp-count-party').html()+')');
	  					$("#hp-browse-activity").html('('+$('#hp-count-activity').html()+')');
	  					$("#hp-browse-service").html('('+$('#hp-count-service').html()+')');

	  					$('#hp-groups').makeacolumnlists({cols:4,colWidth:0,equalHeight:false,startN:1});
	  				},
	  				error:function(msg){
	  					//$('#debug').append('doSearch error: '+msg+'<br/>');
	  				}
	  			});
		}
		$('#clearSearch').hide();
	}
	
	function sortAlpha(mylist){
		var listitems = mylist.children('li').get();
		listitems.sort(function(a, b) {
		   var compA = $(a).text().toUpperCase();
		   var compB = $(b).text().toUpperCase();
		   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
		});
		$.each(listitems, function(idx, itm) { mylist.append(itm); });
	}
	
	function initContactPage(){
		$('input').focus(function(){
			if($(this).val()==$(this).attr('default')){
				$(this).val('');
			}
		}).blur(function(){
			if($(this).val()==''){
				$(this).val($(this).attr('default'));
			}
		}).tipsy({gravity:'w',trigger:'focus'});
		$('textarea').tipsy({gravity:'s', trigger:'focus'});
		$('#send-button').live('click', function(e){
			e.preventDefault();
			
			clear = true;
			$.each($('#contact-us-form input, #contact-content'), function(){
				if($(this).val()=='') {
					clear=false;
					$(this).tipsy('show');
				}
				if($(this).val()==$(this).attr('default')){
					clear=false;
					$(this).tipsy('show');
				}
			});
			
			
			if(clear){
				$.ajax({
		  			type:"POST",
		  			url: base_url+"/home/send/",
		  			data:"name="+$('#contact-name').val()+"&email="+$('#contact-email').val()+"&content="+$('#contact-content').val(),   
		  				success:function(msg){
		  					$('#contact-us-form').html(msg);
		  				},
		  				error:function(msg){}
	  			});
			}
			
		});
	}
	
	function initHelpPage(){
		$("#tabs").tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
		$("#tabs li").removeClass('ui-corner-top').addClass('ui-corner-left');
	}
	
	
	
	/*
	 * TABS
	 * For Search Page
	 */
	$('.tab').live('click', function(event){
		if(!$(this).hasClass('zero')){
			page = 1;
			classFilter = $(this).attr('name');
			changeHashTo(formatSearch(search_term, 1, classFilter));
		}
	});
	
	
	/*
	 * TYPE FACETS
	 * This is called everywhere there is a type, group or subjects that needs to fire a search based on their ID
	 */
	$('.typeFilter, .groupFilter, .subjectFilter').live('click', function(event){
		if(event.type=='click'){
			page = 1;
			if($(this).hasClass('typeFilter')){
				typeFilter = encodeURIComponent($(this).attr('id'));
				changeHashTo(formatSearch(search_term, 1, classFilter));
			}else if($(this).hasClass('groupFilter')){
				groupFilter = encodeURIComponent($(this).attr('id'));
				changeHashTo(formatSearch(search_term, 1, classFilter));
			}else if($(this).hasClass('subjectFilter')){
				subjectFilter = encodeURIComponent($(this).attr('id'));
				changeHashTo(formatSearch(search_term, 1, classFilter));
			}
			scrollToTop();
		}
	});
	
	/*
	 * Similar to subjectFilter, but this is for subjects within search result
	 * There is a reason why we don't use subjectFilter, possibily of CSS conflicts
	 */
	$('.contentSubject').live('click', function(e){
		subjectFilter = $(this).attr('id');
		changeHashTo(formatSearch(search_term, 1, classFilter));
	});
	
	
	/*
	 * Init the format of the search page
	 * This includes:
	 *  limiting the list item on the facet to 17 characters
	 *  limit the lists to 5 items
	 *  draw all centers for spatial search result
	 *  init the show-hide facets button
	 *  other show/hide init
	 */
	function initFormat(){	
		//if there are no return result and there're other results in All tab, click on it
		var realNumFound = parseInt($('#realNumFound').html());
		var numFound = parseInt($('#numFound').html());
		//console.log(realNumFound+' '+numFound);
		if ((realNumFound==0) && (numFound > 0)){
			//console.log('redirecting');
			classFilter = 'All';
			changeHashTo(formatSearch(search_term,1,classFilter));
			//doSearch();
		}
		
		
		/*
		 * Customisation
		 */
		$('#customiseSearchResult').click(function(e){
			e.preventDefault();
			e.stopPropagation();
			$('#searchResultCustomise').toggle();
		});
		
		$('#searchResultCustomise a').click(function(e){
			e.preventDefault();
			e.stopPropagation();
			var id = $(this).attr('id');
			switch(id){
				case "sort-by-magic":resultSort='score desc';changeHashTo(formatSearch(search_term,1,classFilter));;break;
				case "sort-by-alpha":resultSort='listTitle asc';changeHashTo(formatSearch(search_term,1,classFilter));;break;
				case "sort-by-alpha-desc":resultSort='listTitle desc';changeHashTo(formatSearch(search_term,1,classFilter));;break;
			}
		});
		
		switch(resultSort){
			case "score desc":$('#sort-by-magic').addClass('current');break;
			case "listTitle asc":$('#sort-by-alpha').addClass('current');break;
		}
		
		if($.cookie('show_icons')=='') {
			$.cookie('show_icons')='yes';
			$.cookie('show_icons', 'yes');
		}
		
		if($.cookie('show_icons')=='yes') $('#show_icons').addClass('current');
		if($.cookie('show_subjects')=='yes') $('#show_subjects').addClass('current');
		if($.cookie('facets')=='yes') $('#facets').addClass('current');
		
		$('#show_icons, #show_subjects, #facets').click(function(){
			var this_id = $(this).attr('id');
			if($(this).hasClass('current')){
				$.cookie(this_id, 'no');
				doSearch();
			}else{
				$.cookie(this_id, 'yes');
				doSearch();
			}
		});
		
		if(researchGroupSort=='count') $('#group-facetSort').attr('src', base_url+'img/sort-number-desc.png');
		if(subjectSort=='count') $('#subject_value-facetSort').attr('src', base_url+'img/sort-number-desc.png');
		if(typeSort=='count') $('#type-facetSort').attr('src', base_url+'img/sort-number-desc.png');
		
		$('.toggle-facet-sort').click(function(){
			var this_id = $(this).attr('id');
			if(this_id=='group-facetSort'){
				if(researchGroupSort=='count'){
					researchGroupSort='index';
					changeHashTo(formatSearch(search_term,1,classFilter));
				}else{
					researchGroupSort='count';
					changeHashTo(formatSearch(search_term,1,classFilter));
				}
			}else if(this_id=='subject_value-facetSort'){
				if(subjectSort=='count'){
					subjectSort='index';
					changeHashTo(formatSearch(search_term,1,classFilter));
				}else{
					subjectSort='count';
					changeHashTo(formatSearch(search_term,1,classFilter));
				}
			}else if(this_id=='type-facetSort'){
				if(typeSort=='count'){
					typeSort='index';
					changeHashTo(formatSearch(search_term,1,classFilter));
				}else{
					typeSort='count';
					changeHashTo(formatSearch(search_term,1,classFilter));
				}
			}
		});
		
		
		$('body').click(function(){
			$('#searchResultCustomise').hide();
		});
		
		$('#search-tabs li a:first').addClass('top-left-corner');
		
		$('ul.more').each(function(){
			var this_id = $(this).attr('id');
			if((researchGroupSort!='count') && (this_id=='group-facet')){
				sortAlpha($(this));
			}else if((subjectSort!='count') && (this_id=='subject_value-facet')){
				sortAlpha($(this));
			}else if((typeSort!='count') && (this_id=='type-facet')){
				sortAlpha($(this));
			}
		});
		
		//TRUNCATE
		/*
		$('li.limit a').each(function(){
		    var s = $(this).text().substring(0,17);
		    if($(this).text().length > 18) {
		    	s = s + '...';
		    	$(this).text(s);
		    }
		});*/
		
		//LIMIT 5
		$("ul.more").each(function() {
		    $("li:gt(5)", this).hide(); 
		    $("li:nth-child(6)", this).after("<a href='#' class=\"more\">More...</a>");
		});
		$("a.more").live("click", function() {
			//console.log($(this).parent());
			$(this).parent().children().slideDown();
			$(this).remove();
		    return false;
		});
		
		$('.clearFilter').each(function(){
			$(this).append('<img class="clearFilterImg" src="'+base_url+'/img/delete.png"/>');
		});
		
		$('ul.more').each(function(){
			if($(this).text()=='') $(this).parent().hide();
		});
		
		//draw
		for(i in markerArray){
    		if(markerArray[i]!=null)markerArray[i].setMap(null);
    		markerArray[i]=null;
    	}
		
		for(i in drawingArrays){
    		if(drawingArrays[i]!=null)drawingArrays[i].setMap(null);
    		drawingArrays[i]=null;
    	}
		var centers = $('.spatial_center');
		$.each(centers, function(){
			var key = $(this).parent().children('.key').html();
			var info = $(this).parent();
			drawMarker(stringToLatLng($(this).html()), map, info);
		});
		
		//draw search box if searching for spatial
		if(n!=''){
			//console.log(n);
			var p1 = new google.maps.LatLng(n, e);
			var p2 = new google.maps.LatLng(s, w);
			var geoCodeRectangle = new google.maps.Rectangle({ map: map });
			var bounds = new google.maps.LatLngBounds(p2, p1);
	        geoCodeRectangle.setBounds(bounds);
	        drawingArrays.push(geoCodeRectangle);
	        map.fitBounds(bounds);
	        $('#start-drawing').hide();
	        $('#clear-drawing').show();
	        $('#advanced').show();
	        resetZoom();//google map api bug fix
	        map.fitBounds(bounds);
		}
		//show-hide-facets
		if($.cookie('facets')=='yes'){
			//console.log('show');
			$('#search-left').css('width','200px');
			$('#search-right').css('width','750px');
			$('#search-left').show();
		}else if($.cookie('facets')=='no'){
			//console.log('hide');
			$('#search-left').hide();
			$('#search-right').css('width', '960px');
		}
		
		$('.tipsy').remove();
		//$('#result-placeholder').html($('.result').html());
		//$('.result').hide();
		$('#show-facets').hide();
		$('.typeFilter, .groupFilter, .subjectFilter, .ro-icon, .clearFilter, .toggle-facets').tipsy({live:true, gravity:'sw'});
		$('#customise-dialog').tipsy({live:true, gravity:'se'});
		$('#search-tabs li a').tipsy({live:true, gravity:'s'});
		refreshTemporalSearch();
		
	}

	/*
	 * Clearing filters/facets
	 */
	$('.clearFilter').live('click', function(e){
		if($(this).hasClass('clearType')){
			typeFilter = 'All';
		}else if($(this).hasClass('clearGroup')){
			groupFilter = 'All';
		}else if($(this).hasClass('clearSubjects')){
			subjectFilter = 'All';
		}
		changeHashTo(formatSearch(search_term,1,classFilter));
	});
	
	/*
	 * show-hide facet content, slide up and down
	 */
	$('.toggle-facet-field').live('click', function(){
		//console.log($(this).parent().parent().next('div.facet-content'));
		$(this).parent().parent().next('div.facet-content').slideToggle();
		//$(this).parent().children().toggle();//show all the toggle facet field in the same div
		$(this).toggleClass('ui-icon-arrowthickstop-1-n');
		$(this).toggleClass('ui-icon-arrowthickstop-1-s');
		//$(this).hide();
	});
	
	/*
	 * Clearing Spatial button, this will reset the spatial included ids and init a click on the clear-drawing button
	 */
	$('#clearSpatial').live('click', function(event){
		spatial_included_ids = '';
		$('#clear-drawing').click();
		doSearch();
	});
	
	/*
	 * Clearing Spatial info
	 */
	$('#clearTemporal').live('click', function(){
		temporal = 'All';
		changeHashTo(formatSearch(search_term,page,classFilter));
	});
	
	
	/*ADVANCED SEARCH*/
	$('#advanced-search-button').click(function(){
		advanced_search_term = $('#search-box').val();
		$('#advanced-search-term').html(advanced_search_term);
		$('#advanced').slideToggle();
		resetZoom();//google map api bug fix
		$.cookie('advanced-search','open');
	});
	
	$('#close_advanced').click(function(){
		$('#advanced-search-button').click();
		$.cookie('advanced-search','close');
	});
	
	$('#search_advanced').click(function(){
		if(doTemporalSearch){
			temporal = $('#date-slider').slider('values', 0)+'-'+$('#date-slider').slider('values',1);
		}else temporal = 'All';
		$('#search-button').click();
	}).button();
	
	$('#clear_advanced').click(function(){
		$('#clearSearch').click();
	});
	
	$('#show-temporal-search').click(function(){
		if(doTemporalSearch){
			doTemporalSearch=false;
		}else doTemporalSearch = true;
		refreshTemporalSearch();
		//alert(doTemporalSearch);
	}).tipsy();
	
	$('#classSelect').change(function(){
		//console.log($(this).val());
		classFilter = $(this).val();
		//console.log(classFilter);
	});
	
	//$('button').button();
	
	/*
	 * Advanced Search inputs
	 * Updates the main search-box on type
	 */
	$('.search-input').keyup(function(){
		var all = $('#advanced-all').val();
		var exact = $('#advanced-exact').val();
		var or1 = $('#advanced-or1').val();
		var or2 = $('#advanced-or2').val();
		var or3 = $('#advanced-or3').val();
		var not = $('#advanced-not').val();

		if(exact!=''){
			exact = "\""+$.trim(exact)+"\"";
		}
		if(not!=''){
			var words = not.split(' ');
			var res = '';
			$.each(words, function(){
				if(this!='')res += ' -'+this;
			});
			not = $.trim(res);
		}
		
		if(or1==''){
			if(or2!=''){
				$('#advanced-or1').val(or2);
				or1 = or2;
				$('#advanced-or2').val(or3);
				or2 = or3;
				$('#advanced-or3').val('');
				or3 = '';
			}
		}
		
				
		var ors = '';
		if(or1!=''){ors+=or1+" "}
		if(or2!=''){ors+="OR "+or2+" "}
		if(or3!=''){ors+="OR "+or3+" "}
		
		var term = '';
		all = $.trim(all);exact=$.trim(exact);not=$.trim(not);ors=$.trim(ors);
		if(all!='') term += all + ' ';
		if(exact!='') term+=exact + ' ';
		if(not!='') term+=not + ' ';
		if(ors!='') term+=ors + ' ';
		
		//var term = $.trim(all)+" "+exact+or1+or2+or3+not;
		term = term.split(' ');
		term = term.join(' ');
		$('#search-box').val(term);
		search_term = term;
		advanced_search_term = term;
	}).keypress(function(e){
		if(e.which==13){//press enter
			$('#search_advanced').click();
		}
	});
	$('#advanced-text label').tipsy({gravity:'s'});
	/*END ADVANCED SEARCH*/
	
	/*
	 * Show-Hide facet, with animation
	 */
	$("#toggle-facets").live('click', function(e){
		$(this).toggleClass('ui-icon-arrowthickstop-1-w');
		$(this).toggleClass('ui-icon-arrowthickstop-1-e');
		if($(this).hasClass('ui-icon-arrowthickstop-1-w')){
			//show facets
			$.cookie('facets','yes');
			$('#search-left').animate({width:'200px'},300,function(){
	    		$('#search-left').show();
	    	});
	    	$('#search-right').animate({width:'750px'},300,function(){});
	    	$('#top-tab').animate({marginLeft:'210px'},300,function(){});
		}else if($(this).hasClass('ui-icon-arrowthickstop-1-e')){
			//hide facets
			$.cookie('facets','no');
			$('#search-left').hide();
	    	$('#search-left').animate({width:'0px'},300,function(){
	    		$('#search-left').hide();
	    	});
	    	$('#search-right').animate({width:'960px'},300,function(){});
	    	$('#top-tab').animate({marginLeft:'0px'},300,function(){});
		}
	});

	/*
	 * On type, update the search term
	 * On Press Enter, change hash value and thus do search based on search term
	 * Initial search on collection
	 */
	$('#search-box').keypress(function(e){
		if(e.which==13){//press enter
			page = 1;
			resetFilter();
			search_term = $('#search-box').val();
			if(search_term=='')search_term='*:*';
			$('.ui-autocomplete').hide();
			changeHashTo(formatSearch(search_term, 1, classFilter));
		}
	}).keyup(function(){//on typing
		search_term = $('#search-box').val();
		if($(this).val()==''){
			$('#clearSearch').hide();
			populateAdvancedFields(search_term);
			clearEverything();
		}else{		
			$('#clearSearch').show();
			populateAdvancedFields(search_term);
		}
	});
	
	function resetFilter(){
		subjectFilter = 'All';
		classFilter= $('#classSelect').val();
		groupFilter= 'All';
	}
	
	/*
	 * Big search button
	 */
	$('#search-button').click(function(){
		page = 1;
		search_term = $('#search-box').val();
		if(search_term=='')search_term='*:*';
		changeHashTo(formatSearch(search_term, 1, classFilter));
	});
	
	/*Change the Hash Value on the URL*/
	function changeHashTo(location){
		if(window.location.href.indexOf("view") || (window.location.href.indexOf("browse"))){
			window.location.href = base_url+location;
		}else {
			window.location.hash = location;
		}
	}
	
	/*PAGINATION*/
	$('#next').live('click', function(){
		var current_page = parseInt(page);
		var next_page =  current_page + 1;
		changeHashTo(formatSearch(search_term, next_page, classFilter));
		page = next_page;
	});
	
	$('#prev').live('click', function(){
		var current_page = parseInt(page);
		var next_page =  current_page - 1;
		var term = '#'+search_term+'/p'+next_page;
		changeHashTo(formatSearch(search_term, next_page, classFilter));
		page = next_page;
	});
	
	$('.gotoPage').live('click', function(){
		var id = $(this).attr('id');
		var term = '#'+search_term+'/p'+id;
		changeHashTo(formatSearch(search_term, id, classFilter));
		page = id;
	});

	
	/*GOOGLE MAP*/

	$( "#start-drawing" ).button({
		text: true,
		icons: {
			primary: "ui-icon-pencil"
		}
	}).click(function(){
		startDrawing();
		$('#clear-drawing').show();
		$('#map-stuff').hide();
		$('#map-help-stuff').html('Click on the map and Release');
		$('#map-help-stuff').fadeIn();
		$(this).hide();
	});
	
	$( "#expand" ).button(
		{text: false,icons: {primary: "ui-icon-arrowthickstop-1-e"}
	}).click(function(){
		$('#advanced-text').hide();
    	$('#advanced-spatial').css('width', '100%');
    	$('#spatialmap').animate({width:'100%'}, 300, function(){
    		resetZoom();
    	});
    	$('#collapse').show();
    	$(this).hide();
	});
	
	$('#collapse').hide();
	$("#collapse").button(
			{text: false,icons: {primary: "ui-icon-arrowthickstop-1-w"}
	}).click(function(){
			$('#advanced-text').show();
	    	$('#advanced-spatial').css('width', '300px');
	    	$('#spatialmap').css('width', '300px');
	    	resetZoom();
	    	$('#expand').show();
	    	$(this).hide();
	});
	
	$('#map-info')
		.button({text:false,icons:{primary:"ui-icon-info"}})
		.click(function(){
			$('#spatial-info2').dialog({
	    		title:"Spatial Map Search Information",
	    		minWidth:400,draggable:false,resizable:false
	    	}).height('auto');
		});
	
	$('#clear-drawing').hide();
	$('#clear-drawing').button(
		{text: true,icons: {primary: "ui-icon-closethick"}
	}).click(function(e){
    	//console.log(drawingArrays);
    	//google.maps.event.trigger(map, 'resize');
    	//map.setZoom( map.getZoom() );
    	spatial_included_ids='';
    	for(i in drawingArrays){
    		if(drawingArrays[i]!=null){
    			drawingArrays[i].setMap(null);
    			drawingArrays[i]=null;
    		}
    	}
    	$('#clearSpatial').hide();
    	$('#start-drawing').show();
    	$(this).hide();
    	n='';s='';e='';w='';
    	changeHashTo(formatSearch(search_term,1,classFilter));
    });
	
	function resetZoom(){
		google.maps.event.trigger(map, 'resize');
		map.setCenter(latlng);
    	map.setZoom( map.getZoom() );
	}
	
	var latlng = new google.maps.LatLng(-25.397, 133.644);
	var drawingArrays = [];
    var myOptions = {
      zoom: 2,
      center: latlng,
      disableDefaultUI: true,
      panControl: true,
      zoomControl: true,
      mapTypeControl: true,
      scaleControl: true,
      streetViewControl: false,
      overviewMapControl: false,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("spatialmap"),myOptions);
    //GEOCODER
    var geocoder = new google.maps.Geocoder();
    
    
    $("#address").autocomplete({
        //This bit uses the geocoder to fetch address values
        source: function(request, response) {
          geocoder.geocode( {'address': request.term }, function(results, status) {
            response($.map(results, function(item) {
              return {
                label:  item.formatted_address,
                value: item.formatted_address,
                latitude: item.geometry.location.lat(),
                longitude: item.geometry.location.lng(),
                bounds: item.geometry.viewport
              }
            }));
          })
        },
      //This bit is executed upon selection of an address
        select: function(event, ui) {
          //$("#latitude").val(ui.item.latitude);
          //$("#longitude").val(ui.item.longitude);
          //var location = new google.maps.LatLng(ui.item.bounds);
        	var geoCodeRectangle = new google.maps.Rectangle({ map: map });
	          geoCodeRectangle.setBounds(ui.item.bounds);
	          drawingArrays.push(geoCodeRectangle);
	          map.fitBounds(ui.item.bounds);
	          spatialSearch(geoCodeRectangle);
	          $('#start-drawing').hide();
	          $('#clear-drawing').show();
	          //console.log(location);
	          //marker.setPosition(location);
        }
      });

    function startDrawing(){
    	var icon = new google.maps.MarkerImage(base_url+'img/square.png',
  		      new google.maps.Size(16, 16),
  		      new google.maps.Point(0,0),
  		      new google.maps.Point(8, 8));
	  	var marker1 = new google.maps.Marker({
	        map: map,
	        position: new google.maps.LatLng(map.getCenter()),
	        icon:icon,
	        draggable: false,
	        raiseOnDrag:false,
	        title: 'Drag me!',
	        visible:false
	     });
	  	var marker2 = new google.maps.Marker({
	        map: map,
	        position: new google.maps.LatLng(map.getCenter()),
	        icon:icon,
	        draggable: false,
	        raiseOnDrag:false,
	        title: 'Drag me!',
	        visible:false
	     });
    	
    	pointCount = 0;
    	drawingArrays.push(marker1);
    	drawingArrays.push(marker2);
    	google.maps.event.addListener(map, 'click', function(e){
    		pointCount++;
    		if(pointCount == 1){
    			marker1.setPosition(e.latLng);
    			marker1.setVisible(true);
    		}
    		if(pointCount == 2){
    			//console.log('map clicked marker 2');
    			marker2.setPosition(e.latLng);
    			marker2.setVisible(true);
    		}
    	});
    	
    	google.maps.event.addListener(marker1, 'click', function(e){
    		pointCount++;
    		if(pointCount == 1){
    			marker1.setPosition(e.latLng);
    			marker1.setVisible(true);
    			$('#map-help-stuff').html('Move your cursor and click on the map again');
    		}
    	});
    	google.maps.event.addListener(marker2, 'click', function(e){
        	pointCount++;
    		if(pointCount == 2){//do spatialSearch once
    			marker2.setPosition(e.latLng);
    			marker2.setVisible(true);
    			redraw();
    			spatialSearch(rectangle);
    			$('#map-help-stuff').html('');
    			$('#map-stuff').fadeIn();
    		}
        });
    	rectangle = new google.maps.Rectangle({ map: map });
    	drawingArrays.push(rectangle);
    	google.maps.event.addListener(map, 'mousemove', function(e){
    		if(pointCount == 0){
    			marker1.setPosition(e.latLng);
    			marker1.setVisible(true);
    		}else if(pointCount == 1){
    			marker2.setPosition(e.latLng);
    			marker2.setVisible(true);
    			redraw();
    		}
    	});
    	
    	google.maps.event.addListener(rectangle, 'mousemove', function(e){
    		if(pointCount == 0){
    			marker1.setPosition(e.latLng);
    			marker1.setVisible(true);
    		}else if(pointCount == 1){
    			marker2.setPosition(e.latLng);
    			marker2.setVisible(true);
    			redraw();
    		}
    	});
    	
    	//google.maps.event.addListener(marker1, 'drag', redraw);
        //google.maps.event.addListener(marker2, 'drag', redraw);
        //google.maps.event.addListener(marker1, 'dragend', redrawAndSearch);
    	//google.maps.event.addListener(marker2, 'dragend', redrawAndSearch);
        
        function redrawAndSearch(){
        	redraw();
        	spatialSearch(rectangle);
        }
        
        function redraw() {
        	if(marker1.getPosition().lng() < marker2.getPosition().lng()){
	            var latLngBounds = new google.maps.LatLngBounds(
	              marker1.getPosition(),
	              marker2.getPosition()
	            );
	            rectangle.setBounds(latLngBounds);
        	}else{
        		var latLngBounds = new google.maps.LatLngBounds(
      	              marker2.getPosition(),
      	              marker1.getPosition()
      	            );
      	        rectangle.setBounds(latLngBounds);
        	}
         }
    }//END startDrawing
    
    
    
    function spatialSearch(rt){
    	bnds = rt.getBounds();
    	var north = bnds.getNorthEast().lat().toFixed(6);
        var east = bnds.getNorthEast().lng().toFixed(6);
        var south = bnds.getSouthWest().lat().toFixed(6);
        var west = bnds.getSouthWest().lng().toFixed(6);
        n = north;e= east;s = south;w = west;
        changeHashTo(formatSearch(search_term, 1, classFilter));
    }
    
    var markerArray = [];
    var infoWindows = [];
    function drawMarker(latlng, drawingMap, info){
    	if(spatial_included_ids!=''){//only display on spatial search
	    	var icon = new google.maps.MarkerImage('img/square.png',
	  		      new google.maps.Size(16, 16),
	  		      new google.maps.Point(0,0),
	  		      new google.maps.Point(8, 8));
	    	var marker = new google.maps.Marker({
	            map: drawingMap,
	            position: latlng,
	            draggable: false,
	            raiseOnDrag:false,
	            visible:true
	         });
	    	drawingArrays.push(marker);
	    	markerArray.push(marker);
	    	
	    	var infowindow = new google.maps.InfoWindow({
	    	    content: info.children('h2').html(),
	    	    maxWidth: 60
	    	});
	    	infoWindows.push(infowindow);

	    	google.maps.event.addListener(marker, 'click', function() {
	    		for(i in infoWindows){
	        		infoWindows[i].close();
	        	}
	    		infowindow.open(map,marker);
	    	});
	    	//console.log(marker);
    	}
    }

    function scrollToTop(){
    	$("html, body").animate({ scrollTop: 0 }, "slow");
    	return false;
    }
});//END DOCUMENT READY


function validateLonLatText(lonlatText)
{
	var valid = true;
	
	if( lonlatText != "" )
	{
		var coords = lonlatText.split(' ');
		var lat = null;
		var lon = null;
		var coordsPair = null;
   		
		// Test for a two point line.
		if( coords.length == 2 )
		{
			//mctSetErrorMessage("The coordinates don't represent a point or a region.");
			valid = false;	
		}

		for( var i=0; i < coords.length && valid; i++ )
		{
			// Get the lat and lon.
			coordsPair = coords[i].split(",");
			lat = coordsPair[1];
			lon = coordsPair[0];
			
			// Test for numbers.
			if( isNaN(lat) || isNaN(lon) )
			{
				//mctSetErrorMessage('Some coordinates are not numbers.');
				valid = false;
				break;
			}
			// Test the limits.
			if( Math.abs(lat) > 90 || Math.abs(lon) > 180 )
			{
				//mctSetErrorMessage('Some coordinates have invalid values.');
				valid = false;
				break;
			}
		
			// Test for an open region.
			if( i == coords.length-1 )
			{
				if( coords[0] != coords[i] )
				{
					//mctSetErrorMessage("The coordinates don't represent a point or a region. To define a region the last point needs to be the same as the first.");
					valid = false;	
				}
			}	
		}
	}
	return valid;
}
