var showModalId = '';

function checkModalId(form){
	if(showModalId!='')
	{
		setRelatedId(showModalId);
		closeSearchModal(showModalId);
		return false;
	}
	else{
		form.submit();
	}
}

function addRelatedObjectAutocomplete(field) {

	field = "#" + field;
	if(field.indexOf("object_institution_key")>0)
		{
			var showDraft="yes";
		}else{
			var showDraft="no";
		}
	var class_field = field.replace(/object_/,"select_");

	class_field = class_field.replace(/name/,"class");
	
	var cSelect = field.replace(/object/,"select");
	cSelect = cSelect.replace(/name/,"class");
	
	var dsSelect = cSelect.replace(/class/,"dataSource");
	var groupSelect = cSelect.replace(/class/,"group");
//alert($( groupSelect ).val() + " thegroup");
	$( field ).autocomplete({
		minLength: 2,
		source: function(request, response){
			$.getJSON( "../manage/process_registry_object.php?task=searchRelated", {sText:$.trim(request.term), oClass:$( cSelect ).val() ,dSourceKey:$( dsSelect ).val(), oGroup:$( groupSelect ).val()}, response );
		},
		focus: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		},
		select: function( event, ui ) {
			$( field ).val( ui.item.value );
			$( class_field ).val($(cSelect).val().toLowerCase());
			return false;
		}
	})
	.data( "autocomplete" )._renderItem = function( ul, item ) {
		if(showDraft=='yes')
		{
			return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a><b>" + item.label + "</b> " + (item.value != item.label ? "(" + item.value + ")" : "") + "<br><i>" + item.desc + "</i></a>" )
			.appendTo( ul );

		}	else {
			if(item.desc.indexOf("(PUBLISHED)")>0)
				{
				return $( "<li></li>" )
				.data( "item.autocomplete", item )
				.append( "<a><b>" + item.label + "</b> " + (item.value != item.label ? "(" + item.value + ")" : "") + "<br><i>" + item.desc + "</i></a>" )
				.appendTo( ul );
				}
		}

	};
	$(field).keypress(function(event){
		if(event.which == 13) event.preventDefault();
	});
}
function showSearchModal(id)
{
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
		showModalId = id;
		//Set heigth and width to mask to fill up the whole screen
		$('#mask_'+id).css({'width':maskWidth,'height':maskHeight});

		//transition effect		
		//$('#mask_'+id).fadeIn(1000);	
		$('#mask_'+id).fadeTo("fast",0.4);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
		//Set the popup window to center
		$("#searchDialog_"+id).css('position', 'fixed');
		$("#searchDialog_"+id).css('top',  winH/2-$("#searchDialog_"+id).height()/2);
		$("#searchDialog_"+id).css('left', winW/2-$("#searchDialog_"+id).width()/2);
	
		//transition effect
		$("#searchDialog_"+id).fadeIn(200); 

	//	$( "#" + id + "_name").val($("#" + id + "_value").val());
	//	$( "#" + id + "_name").autocomplete("search", $( "#" + id + "_value").val());

}


function closeSearchModal(id)
{
		$('#mask_' + id).hide();
		$("#searchDialog_" + id).hide();
		showModalId = '';
}
function addVocabComplete(field, type) {
	field = "#" + field;
	var button = field.replace(/object/,"button");
	$(button).click(function(event){
		  event.stopPropagation();
	});
	$( field ).autocomplete({
		minLength: 0,
		source: function(request, response){
			$.getJSON( "../manage/process_registry_object.php?task=getvocab", {vocab:type, term:request.term}, response );
		},
		open: function ( event, ui ) {
			$( button ).attr("src",$( button ).attr("src").replace(/in/,"out"));
			return false;
		},
		close: function ( event, ui ) {
			if (field == "#object_mandatoryInformation_type" || field == "#object_mandatoryInformation_group") {
				checkMandatoryFields(null);
			}
			if (field.indexOf("electronic") > 0 && field.indexOf("type") > 0)
			{
				testAnyURI(field.replace(/type/, "value_1_value").substr(1));				
			}
			$( button ).attr("src",$( button ).attr("src").replace(/out/,"in"));
			return false;
		},
		focus: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		},
		select: function( event, ui ) {
			$( field ).val( ui.item.value );
			return false;
		}
	})

	.data( "autocomplete" )._renderItem = function( ul, item ) {

		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a><b>" + item.label + "</b> " + (item.value != item.label ? "(" + item.value + ")" : "") + "<br><i>" + item.desc + "</i></a>" )
			.appendTo( ul );
	};
	$(field).keypress(function(event){
		if(event.which == 13) event.preventDefault();event.stopPropagation();
	});
}


function toggleDropdown(button) {
	button = "#" + button;
	if (/in/.test($(button).attr("src"))) {
		// menu closed, open it
		$("*").autocomplete( "close" );
		$( button.replace(/button/,"object") ).autocomplete( "search", "*" ); 
	} else {
		// menu open, close it
		$( button.replace(/button/,"object") ).autocomplete( "close" ); 
	}
}

function setRelatedId(id)
{	
	if($("#object_mandatoryInformation_key").val() != $("#" + id + "_name").val())
	{
		$( "#" + id + "_value").val($("#" + id + "_name").val());
		var warningField  = id.replace(/object/,"warnings");
		//getRelatedObjectClass(id + "_value");
		$('[name=\"'+ warningField  + '_value\"]').html('');
		$('[name=\"'+ warningField + '_value\"]').hide();
		$('#mask_' + id).hide();
		$("#searchDialog_" + id).hide();
	}
	else
	{
		alert("An object cannot be related to itself");
	}
}

function testRelatedKey(id)
{
	var warningField  = id.replace(/object/,"warnings");
	if($("#object_mandatoryInformation_key").val() == $("#" + id).val() && $("#" + id).val() != '')
	{
		$('[name=\"'+ warningField  + '\"]').html('');		
		SetWarnings(id.replace(/object/,"errors") , "An object cannot be related to itself.");
	}
	else
		{
		$('[name=\"'+ warningField  + '\"]').html('');
		$('[name=\"'+ warningField + '\"]').hide();
		}
}
function setTheDialog(theSelect,theClass)
{

	var theOptions = document.getElementById(theClass).options;
	for(i=0;i<theOptions.length;i++)
		{
		
		if(theOptions[i].value.toLowerCase()==theSelect.value)
			{
			theOptions[i].selected = true;
			}
		}
	
}

