$(document).ready(function() {

	//Featured Research Domain
	$.getJSON(default_base_url+'registry/services/rda/getSpotlightPartners/',initSpotlight);
	function initSpotlight(data){
		var template = $('#spotlight_template').html();
		var output = Mustache.render(template, data);
		$('#spotlight').html(output);
		$('.flexslider').flexslider({
	    animation: "fade",
	    controlNav: true,
	    slideshowSpeed: 4500,
	    directionNav:false,
	    pauseOnHover:true
	  });
	}

	$('#show_who_contributes').qtip({
		content: {
			text: $('#who_contributes')
		},
		show:{solo:true,event:'click'},
	    hide:{delay:1000, fixed:true,event:'unfocus'},
	    position:{my:'bottom right', at:'top center', viewport:$(window)},
	    style: {
	        classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
	        width: 650
	    }
	});

});