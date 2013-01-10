$(document).ready(function() {

	//Featured Research Domain
	$.getJSON(default_base_url+'registry/services/rda/getSpotlightPartners/',initSpotlight);
	function initSpotlight(data){
		console.log(data);
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
});