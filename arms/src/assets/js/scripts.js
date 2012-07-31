$('button.show_nav').click(function(){
	$('#main-nav').slideToggle();
});


$('.btn-group').live({
	dblclick: function(e){
		e.stopPropagation();
	}
});



$('.select_all_btn').click(function(){
	if($(this).attr('name')=='select_all'){
		$(this).attr('name', 'deselect_all');
		$(this).text('Deselect all');
		$('.item').addClass('selected');
	}else{
		$(this).attr('name', 'select_all');
		$(this).text('Select All');
		$('.item').removeClass('selected');
	}
	updateItemsInfo();
});


$('#items')
	.drag("start",function( ev, dd ){
		return $('<div class="selection" />')
			.css('opacity', .65 )
			.appendTo( document.body );
	})
	.drag(function( ev, dd ){
		$( dd.proxy ).css({
			top: Math.min( ev.pageY, dd.startY ),
			left: Math.min( ev.pageX, dd.startX ),
			height: Math.abs( ev.pageY - dd.startY ),
			width: Math.abs( ev.pageX - dd.startX )
		});
	})
	.drag("end",function( ev, dd ){
		$( dd.proxy ).remove();
	});

$.drop({ multi: true });
$('#items').sortable({
	delay: 300,       
    start: function(e, ui){
        $(ui.placeholder).hide(300);
    },
    change: function (e,ui){
        $(ui.placeholder).hide().show(300);
    }
});

function changeHashTo(hash){
	window.location.hash = suffix+hash;
}

function updateItemsInfo(){
	var totalSelected = $('#items .selected').length;
	if(totalSelected > 1){
		$('#items_info').slideDown();
		var message = '<b>'+totalSelected + '</b> registry objects has been selected';
		$('#items_info').html(message);
	}else{
		$('#items_info').slideUp();
	}
}

function logErrorOnScreen(error){
	var template = $('#error-template').html();
	var output = Mustache.render(template, error);
	$('#main-content').append(output);
	$('section').hide();
}

jQuery.fn.extend({
  slideRightShow: function(duration) {
    return this.each(function() {
        $(this).show('slide', {direction: 'right'}, duration);
    });
  },
  slideLeftHide: function(duration) {
    return this.each(function() {
      $(this).hide('slide', {direction: 'left'}, duration);
    });
  },
  slideRightHide: function(duration) {
    return this.each(function() {
      $(this).hide('slide', {direction: 'right'}, duration);
    });
  },
  slideLeftShow: function(duration) {
    return this.each(function() {
      $(this).show('slide', {direction: 'left'}, duration);
    });
  }
});


$.ajaxSetup({
    error: function(err) {
        //do stuff when things go wrong
        console.error(err);
        logErrorOnScreen(err.responseText);
    }
});

// implement JSON.stringify serialization
JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    }
    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; t = typeof(v);
            if (t == "string") v = '"'+v+'"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};


