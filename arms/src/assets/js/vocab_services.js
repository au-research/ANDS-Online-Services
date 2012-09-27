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
					case 'add'		: load_vocab_add(words[1]);break;
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
	$('.item-control .btn').live({
		click: function(e){
			e.preventDefault();
			var vocab_id = $(this).parent().parent().attr('vocab_id');
			if($(this).hasClass('view')){
				changeHashTo('view/'+vocab_id);
			}else if($(this).hasClass('edit')){
				changeHashTo('edit/'+vocab_id);
			}else if($(this).hasClass('delete')){
				changeHashTo('delete/'+vocab_id);
			}else if($(this).hasClass('add')){
				alert(vocab_id)
			changeHashTo('add/'+vocab_id);
			}			
		}
	});

	//data source chooser event
	$('#vocab-chooser').live({
		change: function(e){
			changeHashTo('view/'+$(this).val());
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
		$('#browse-vocabs').slideDown();
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
		//	console.log(data);
			var template = $('#vocab-view-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-vocab');
			$('#view-vocab').html(output);
			$('#view-vocab').fadeIn(500);
			
			//get the associated versions			
			$.ajax({
				url: 'vocab_service/getVocabVersions/'+vocab_id,
				type: 'GET',
				contentType: 'application/json; charset=utf-8',
				dataType: 'json',
				success: function(data){
				//	console.log(data);				
					var template = $('#vocab-version-view-template').html();
					var output = Mustache.render(template, data);
					$('#view-vocab-version').html(output);
					$('#view-vocab-version').fadeIn(500);

				}
			});

			//get the associated changes
			$.ajax({
				url: 'vocab_service/getVocabChanges/'+vocab_id,
				type: 'GET',
				contentType: 'application/json; charset=utf-8',
				dataType: 'json',
				success: function(data){
				//	console.log(data);	
					var template = $('#vocab-changes-view-template').html();
					var output = Mustache.render(template, data);
					$('#view-vocab-changes').html(output);
					$('#view-vocab-changes').fadeIn(500);

				}
			}); 

		}
	});
	return false;
}

/*
 * Draw Charts
 * Use the jqplot library, currently a dud
 *
 * @TODO: refactor
 *
 * 
 * @params [void]
 * @return [void]
 */
function drawCharts(){
	$('#ro-progression').height('350').html('');
	$.jqplot('ro-progression',  [[[1, 2],[3,5.12],[5,13.1],[7,33.6],[9,85.9],[11,219.9]]]);
}

/*
 * Load a vocab edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params vocab__id
 * @return [void]
 */
function load_vocab_edit(vocab_id, active_tab){
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
			if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
				$('.nav-tabs li a[href=#'+active_tab+']').click();
			}
			
			$('#edit-vocab  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:true,
					onChange:function(){
						$(this).find('input').attr('checked', 'checked');
					}
				});
			});
			
			$("#edit-vocab .chzn-select").chosen().change(function(){
				var input = $('#'+$(this).attr('for'));
				$(input).val($(this).val());
			});
			$('#edit-vocab .chzn-select').each(function(){
				var input = $('#'+$(this).attr('for'));
				$(this).val($(input).val());
				$(this).chosen().trigger("liszt:updated");
			});
		}
	});
	return false;
}
function load_vocab_add(vocab_id, active_tab){
//	$('#add-vocab').html('Loading');
	$('#browse-vocab').slideUp(500);
	$('#view-vocabs').slideUp(500);


/*	$.ajax({
		url: 'vocab_service/getVocab/'+vocab_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data); */
			var template = $('#vocab-add-template').html();
		//	var output = Mustache.render(template,'{item:0}');
		//	alert(output);
			$('#add-vocab').html(template);
			$('#add-vocab').fadeIn(500);
	/*		if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
				$('.nav-tabs li a[href=#'+active_tab+']').click();
			}
			
			$('#edit-vocab  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:true,
					onChange:function(){
						$(this).find('input').attr('checked', 'checked');
					}
				});
			});
			
			$("#edit-vocab .chzn-select").chosen().change(function(){
				var input = $('#'+$(this).attr('for'));
				$(input).val($(this).val());
			});
			$('#edit-vocab .chzn-select').each(function(){
				var input = $('#'+$(this).attr('for'));
				$(this).val($(input).val());
				$(this).chosen().trigger("liszt:updated");
			});
		}
	});*/
	return false;
}
$('#save-edit-form').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');
		jsonData.push({name:'vocab_id', value:$('#vocab_view_container').attr('vocab_id')});
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
					if (!data.status == "OK")
					{
						
						$('#myModal').modal();
						logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
						$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
					}
					else
					{
						//console.log(data);
						changeHashTo('view/'+$('#vocab_view_container').attr('vocab_id'));
						createGrowl("Your Vocabulary was successfully updated");
						updateGrowls();
					}
			},
			error: function()
			{
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
		jsonData.push({name:'vocab_id', value:$('#vocab_view_container').attr('vocab_id')});
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
						console.log(data);
						changeHashTo('view/'+$('#vocab_view_container').attr('vocab_id'));
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