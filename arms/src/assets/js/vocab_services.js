/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' 	: browse(words[1]);break;
					case 'view'		: load_vocab(words[1]);break;
					case 'edit'		: load_vocab_edit(words[1], words[2]);break;
					case 'delete'	: load_vocab_delete(words[1]);break;
					case 'add'		: load_vocab_add();break;
					case 'version-edit'		: load_vocab_version_edit(words[1],words[2]);break;					
					default: logErrorOnScreen('this functionality is currently being worked on');break;
				}
				$('#vocab_view_container').attr('vocab_id', words[1]);
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}else{//there is no hash suffix
			browse('lists');
		}
	});
	$(window).hashchange();//initial hashchange event

	//switch view button binding
	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});

	load_more(1);//init the load_more function | load the first page

	//load_more button binding, once clicked will increment the page value
	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		load_more(page);
		$(this).attr('page', page++);
	});

	//item level binding
	$('.item').live({
		mouseenter: function(e){
			$('.btn-group', this).show();
		},
		mouseleave: function(e){
			$('.btn-group', this).hide();
		},
		dblclick: function(e){
			e.preventDefault();
			changeHashTo('view/'+$(this).attr('vocab_id'));
		},
		click: function(){
			
		}
	});

	//item button binding
	$('.btn').live({
		click: function(e){
			e.preventDefault();
			var vocab_id = $(this).parent().attr('vocab_id');
			if($(this).hasClass('view')){
				changeHashTo('view/'+vocab_id);
			}else if($(this).hasClass('edit')){
				changeHashTo('edit/'+vocab_id);
			}else if($(this).hasClass('delete')){
				changeHashTo('delete/'+vocab_id);
			}else if($(this).hasClass('add')){
				changeHashTo('add/');
			}else if($(this).hasClass('version-edit')){
				var version_id = $(this).attr('version_id')	
				showEditVersionModal(vocab_id,version_id);
			}else if($(this).hasClass('version-format-delete')){
				var format_id = $(this).attr('format_id')	
				deleteVersionFormat(format_id,vocab_id)
			}else if($(this).hasClass('version-format-add')){
				var version_id = $(this).attr('version_id')	
				addVersionFormat(version_id,vocab_id);
			}
		}
	});


	//vocab chooser event
	$('#vocab-chooser').live({
		change: function(e){
			changeHashTo('view/'+$(this).val());
		}
	});

	//vocab version format chooser event
	$('#versionFormatType').live({
		change: function(e){
			var theChoice = $(this).val();
			var thebox = $('#versionFormatValueBox').html();
			if(theChoice == 'file')
				{
					$('#versionFormatValueBox').html('<input type="file" id="versionFormatValue" style="display:inline"/><br />');
				} else {
					$('#versionFormatValueBox').html('<input type="text" value="" id="versionFormatValue" style="width:300px"/><br />');			
				}
		}
	});
	
	//closing box header will go back in history
	$('.box-header .close').live({
		click: function(e){
			//changeHashTo('browse/'+currentView);
			window.history.back();
		}
	});

});

/*
 * Initialize the View
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [string] view ENUM thumbnails|lists
 * @returns: [void]
 */
function browse(view){
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-vocabs').fadeIn();
	}else{
		logErrorOnScreen('invalid View Argument');
	}
	$("#vocab-chooser").chosen();
}

/*
 * Initialize the view
 * This load the view for the next page, append that to the main #items container
 * @TODO: remove the next page div when there is no_more
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [int] page value
 * @returns: [void]
 */
function load_more(page){
	$.ajax({
		url: 'vocab_service/getVocabs/'+page,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			if(!data.more) $('#load_more_container').hide();
			$('#items').append(output);
		}
	});
}

/*
 * Load a vocab view
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params vocab_id
 * @return false
 */
function load_vocab(vocab_id){
	$('#view-vocab').html('Loading');
	$('#browse-vocabs').slideUp(500);
	$.ajax({
		url: 'vocab_service/getVocab/'+vocab_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			if(data.status=='ERROR') logErrorOnScreen(data.message);
			var template = $('#vocab-view-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-vocab');
			$('#view-vocab').html(output);
			$('#view-vocab').fadeIn(500);

			//bind tooltips on formats
			$('.format').each(function(){
				var vocab_id = $(this).attr('vocab_id');
				var format = $(this).attr('format');
				$(this).qtip({
					content:{
						text:'Loading...',
						ajax:{
							url: 'vocab_service/getDownloadableByFormat/'+vocab_id+'/'+format,
							type: 'GET',
							success: function(data, status){
								var template = $('#vocab-format-downloadable-template').html();
								var output = Mustache.render(template, data);
								this.set('content.text', output);
							}
						}
					},
					position:{
						my:'top left',
						at: 'bottom center'
					},
					show: {event: 'click'},
					hide: {event: 'unfocus'},
					events: {},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
				});
			});

			//bind the tooltips on versions

			$('.version').each(function(){
				var version_id = $(this).attr('version_id');
				var vocab_id = $(this).parents('.box').attr('vocab_id');
				var target = $(this).parent();
				$(target).qtip({
					content:{
						text:'Loading...',
						ajax:{
							url: 'vocab_service/getFormatByVersion/'+version_id,
							type: 'GET',
							success: function(data, status){
								var template = $('#vocab-format-downloadable-template-by-version').html();
								var output = Mustache.render(template, data);
								this.set('content.text', output);

								$('.editVersion').click(function(){
									var version_id = $(this).attr('version_id');
									$('.editVersionForm[version_id='+version_id+']').slideDown(100, function(){
										$(target).qtip('reposition');
									});
								});

								$('.cancelEdit').click(function(){
									var version_id = $(this).attr('version_id');
									$('.editVersionForm[version_id='+version_id+']').slideUp(100, function(){
										$(target).qtip('reposition');
									});
								});

								$('.editVersionConfirm').click(function(){
									var version_id = $(this).attr('version_id');
									var form = $('.editVersionForm[version_id='+version_id+']')
									var jsonData = [];
									jsonData.push({name:'id', value:version_id});
									
									$('input[type=text]', form).each(function(){
										var label = $(this).attr('name');
										var value = $(this).val();
										if(value!='' && value){
											jsonData.push({name:label, value:value});
										}
									});
									if($('input[type=checkbox]', form).is(":checked")){
										jsonData.push({name:'makeCurrent', value:true});
									}
									$.ajax({
										url:'vocab_service/updateVersion/', 
										type: 'POST',
										data: jsonData,
										success: function(data){	
											load_vocab(vocab_id);
										},
										error: function(data){
											logErrorOnScreen(data);
										}
									});
								});

								$('.deleteVersion').click(function(){
									var version_id = $(this).attr('version_id');
									$('.deleteVersionForm[version_id='+version_id+']').slideDown(100, function(){
										$(target).qtip('reposition');
									});
								});

								$('.cancelDelete').click(function(){
									var version_id = $(this).attr('version_id');
									$('.deleteVersionForm[version_id='+version_id+']').slideUp(100, function(){
										$(target).qtip('reposition');
									});
								});

								$('.cancelAddFormat').click(function(){
									var version_id = $(this).attr('version_id');
									$('.addFormatForm[version_id='+version_id+']').slideUp(100, function(){
										$(target).qtip('reposition');
									});
								});

								$('.deleteVersionConfirm').click(function(){
									var version_id = $(this).attr('version_id');
									var vocab_id = $(this).attr('vocab_id');
									$.ajax({
										url:'vocab_service/deleteVersion/', 
										type: 'POST',
										data: {version_id:version_id},
										success: function(data){	
											load_vocab(vocab_id);
										},
										error: function(data){
											logErrorOnScreen(data);
										}
									});
								});

								$('.addFormat').click(function(){
									var version_id = $(this).attr('version_id');
									$('.addFormatForm[version_id='+version_id+']').slideDown(100, function(){
										$(target).qtip('reposition');
									});
								});

								$('.toggleAddFormatType .btn').click(function(){
									var version_id = $(this).parent().attr('version_id');
									var show = $(this).attr('content');
									$('.addFormatTypeContent').hide();
									$('.'+show+'[version_id='+version_id+']').show();
									$('.inputAddFormatType[version_id='+version_id+']').val($(this).attr('value'));
								});

								$('.toggleAddFormat .btn').click(function(){
									var version_id = $(this).parent().attr('version_id');
									$('.inputAddFormat[version_id='+version_id+']').val($(this).attr('value'));
								});

								$('.addFormatSubmit').click(function(){
									var version_id = $(this).attr('version_id');
									var form = $('.addFormatForm[version_id='+version_id+'] .form');
									var jsonData = [];
									$('input', form).each(function(){
										var label = $(this).attr('name');
										var value = $(this).val();
										if(value!='' && value){
											jsonData.push({name:label, value:value});
										}
									});
									var type = $('.inputAddFormatType[version_id='+version_id+']').val();
									var doAdd = true;

									if(type=='file'){//uploading file
										var data = new FormData();
										$.each($('input.addFormatUploadValue[version_id='+version_id+']')[0].files, function(i, file) {
											data.append('userfile', file);
											jsonData.push({name:'value',value:file.name})
										});
										$.ajax({
										    url: 'vocab_service/test',
										    data: data,
										    cache: false,
										    contentType: false,
										    processData: false,
										    type: 'POST',
										    success: function(data){
										        if(data.status!='OK'){
										        	doAdd = false;
										        	logErrorOnScreen(data.message);
										        }else{
										        	$.ajax({
														url:'vocab_service/addFormat/'+version_id, 
														type: 'POST',
														data: jsonData,
														success: function(data){	
															load_vocab(vocab_id);
														}
													});
										        }
										    }
										});
									}else{
										$.ajax({
											url:'vocab_service/addFormat/'+version_id, 
											type: 'POST',
											data: jsonData,
											success: function(data){	
												load_vocab(vocab_id);
											}
										});
									}
									
								});

								$('.deleteFormat').click(function(){
									var format_id = $(this).attr('format_id');
									$.ajax({
										url:'vocab_service/deleteFormat/'+format_id, 
										type: 'POST',
										success: function(data){	
											if(data.status=='OK'){
												$('tr.formatRow[format_id='+format_id+']').fadeOut('200', function(){
													$(target).qtip('reposition');
												});
											}else{
												logErrorOnScreen(data);
											}
										}
									});
								});
							}
						}
					},
					position:{
						my:'right center',
						at: 'left center'
					},
					show: {event: 'click'},
					hide: {event: 'unfocus'},
					events: {},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
				});
			});

			$('.addVersion').each(function(){
				var vocab_id = $(this).attr('vocab_id');
				var content = $('#add-version-to-vocab').html();
				$(this).qtip({
					content:{
						text:content
					},
					position:{
						my:'right center',
						at: 'left center'
					},
					show: {event: 'click'},
					hide: {event: 'unfocus'},
					events: {},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
				});
			});

			$('.addVersionButton').die().live({
				click:function(e){
					e.preventDefault();
					var jsonData = [];
					$(this).button('loading');
					var form = $(this).parents('form');
					
					var vocab_id = $(form).attr('vocab_id');
					jsonData.push({name:'vocab_id', value:vocab_id});
					
					$('input[type=text]', form).each(function(){
						var label = $(this).attr('name');
						var value = $(this).val();
						if(value!='' && value){
							jsonData.push({name:label, value:value});
						}
					});
					if($('input[type=checkbox]', form).is(":checked")){
						jsonData.push({name:'makeCurrent', value:true});
					}
					$.ajax({
						url:'vocab_service/addVersion/'+vocab_id, 
						type: 'POST',
						data: jsonData,
						success: function(data){	
							load_vocab(vocab_id);
							return false;
						},
						error: function(data){
							logErrorOnScreen(data);
						}
					});
					$(this).button('reset');
				}
			});

			
		}
	});
	return false;
}

/*
 * Load a vocab edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params vocab__id
 * @return [void]
 */
function load_vocab_edit(vocab_id){
	$('#edit-vocab').html('Loading');
	$('#browse-vocab').slideUp(500);
	$('#view-vocabs').slideUp(500);
	$.ajax({
		url: 'vocab_service/getVocab/'+vocab_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);
			var template = $('#vocab-edit-template').html();
			var output = Mustache.render(template, data);
			$('#edit-vocab').html(output);
			$('#edit-vocab').fadeIn(500);
			
			
			
			
			//get the associated versions			
			/*$.ajax({
				url: 'vocab_service/getVocabVersions/'+vocab_id,
				type: 'GET',
				contentType: 'application/json; charset=utf-8',
				dataType: 'json',
				success: function(data){
					//console.log(data);				
					//var template = $('#vocab-version-edit-template').html();
	
					var template ='<fieldset><legend>Vocabulary Versions</legend></fieldset><div class="" id="vocab_edit_container"'+			
				'</div><table class="table-bordered" background="#ffffff" width="800">'+
					'<thead><tr align="left"><th style="padding-left:10px">Title</th><th style="padding-left:10px">Status</th><th style="padding-left:10px">Format</th><th style="padding-left:10px">Edit</th></tr></thead><tbody><tr>{{#items}}'+	
					'{{#title}}'+
					'<td style="padding-left:10px">{{title}}</td>'+
					'{{/title}}'+					
					'{{#status}}'+
					'<td style="padding-left:10px">{{status}}</td>'+
					'{{/status}}'+					
					'<td style="padding-left:10px">{{#formats}}<a href="{{#value}}{{value}}{{/value}}" target="blank">{{#format}}{{format}}{{/format}}</a> '+		
					'{{/formats}}</td><td style="padding-left:10px" vocab_id="'+vocab_id+'"><div class="btn-group item-control"><button class="btn version-edit" version_id="{{#id}}{{id}}{{/id}}"><i class="icon-edit"></i></button></div></div></td></tr>'+
					'{{/items}}</tbody></table>'+	
					'</div><br /><br />'+
					'<div class="modal hide" id="versionModal">'+
					 	'<div class="modal-header">'+
					 	'<button type="button" class="close" data-dismiss="modal">×</button>'+
					 	'<h3>Edit Version</h3>'+
					 	'</div>'+
					 	'<div class="modal-body" id="modal-form" vocab_id="'+vocab_id+'"></div>'+
					 	'<div class="modal-footer"></div>'+
					 '</div>';
					var output = Mustache.render(template, data);
					$('#edit-vocab-version').html(output);
					$('#edit-vocab-version').fadeIn(500);

				}
			});*/	
		}
	});
	return false;
}

function showEditVersionModal(vocab_id,version_id)
{
	
	$.ajax({
		url: 'vocab_service/getVocabVersion/'+version_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);				
			var template = $('#vocab-version-edit-template').html();
			var output = Mustache.render(template, data);
			$('#versionModal').modal();
			$('#modal-form').html(output);
		}
	});
	
}


function load_vocab_add(){

	$('#browse-vocab').slideUp(500);
	$('#view-vocabs').slideUp(500);

	var template = $('#vocab-add-template').html();
	$('#add-vocab').html(template);
	$('#add-vocab').fadeIn(500);
	return false;
}

function deleteVersionFormat(format_id,vocab_id){
	
	confirm("Do you really want to delete the format of vocab " + format_id );
	$.ajax({
		url: 'vocab_service/deleteFormat/'+format_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){

				//$.modal.close();
				changeHashTo('edit/'+vocab_id);
				
			},
			error: function(data)
			{
				console.log(data)
				$('#myModal').modal();
				logErrorOnScreen("An error occured deleting your format!", $('#myModal .modal-body'));
			//	$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
			}
			
	
			});
	
}

function addVersionFormat(version_id,vocab_id)
{
	var jsonData = [];
	jsonData.push({name:'versionFormat', value:$('#versionFormat').val()});
	jsonData.push({name:'versionFormatType', value:$('#versionFormatType').val()});	
	if($('#versionFormatType').val()=='file')
		{
		 	var theFile=document.getElementById('versionFormatValue').files[0];
		 
		 	var uri = "vocab_service/uploadFile";
		 	var xhr = new XMLHttpRequest();
		 	var fd = new FormData();
          
		 	xhr.open("POST", uri, true);
		 	xhr.onreadystatechange = function() {
             if (xhr.readyState == 4 && xhr.status == 200) {
                 // Handle response.
     			// console.log(xhr.responseText + " is the responseText"); // handle response.
             }
		 	};
		 	fd.append('theFile', theFile);
		 	// Initiate a multipart/form-data upload
		 	xhr.send(fd);
		 
		 	jsonData.push({name:'versionFormatValue', value:theFile['name']});

		}else{
			jsonData.push({name:'versionFormatValue', value:$('#versionFormatValue').val()});
		}
	$.ajax({
		url: 'vocab_service/addFormat/'+version_id,
		type: 'POST',
		data: jsonData,
		enctype: 'multipart/form-data',
		success: function(data){
				//console.log(data)
				//$.modal.close();
				changeHashTo('edit/'+vocab_id);
				
			},
			error: function(data)
			{
				console.log(data)
				$('#myModal').modal();
				logErrorOnScreen("An error occured adding your format!", $('#myModal .modal-body'));
			//	$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
			}
			
	
			});	
}

$('#save-edit-form').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');
		var vocab_id = $('#edit-form').attr('vocab_id');
		jsonData.push({name:'vocab_id', value:vocab_id});
		$('#edit-vocab #edit-form input, #edit-vocab #edit-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();
			if(value!='' && value){
				jsonData.push({name:label, value:value});
			}
		});
		$.ajax({
			url:'vocab_service/updateVocab', 
			type: 'POST',
			data: jsonData,
			success: function(data){
				if(data.status=='OK'){
					$('#save-edit-form-message').html(data.message);
				}else{
					$('#myModal').modal();
					logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
					$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
				}
			},
			error: function(){
				$('#myModal').modal();
				logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
				$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
			}
		});
		$(this).button('reset');
	}
});

$('#save-add-form').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');
		jsonData.push({name:'vocab_id', value:'0'});
		$('#add-vocab #add-form input, #add-vocab #add-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();
			if(value!='' && value){
				jsonData.push({name:label, value:value});
			}
		});

		$.ajax({
			url:'vocab_service/addVocab', 
			type: 'POST',
			data: jsonData,
			success: function(data){		
					if (!data.status == "OK")
					{				
						$('#myModal').modal();
						logErrorOnScreen("An error occured whilst adding your vocab!", $('#myModal .modal-body'));
						$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
					}
					else
					{
						var decodedResp = JSON.parse(data);
						changeHashTo('view/'+decodedResp.id);
						createGrowl("Your Vocabulary was successfully added");
						updateGrowls();
					}
			},
			error: function()
			{
				$('#myModal').modal();
				logErrorOnScreen("An error occured whilst adding your vocab!", $('#myModal .modal-body'));
				$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
			}
		});

		$(this).button('reset');
	}
});

