var dsKey;
var dsName;
 
$(document).ready(function() {
	$(".chzn-select").chosen(); 
	$(".chzn-select-deselect").chosen({allow_single_deselect:true});

	bindTagEvent();
	function bindTagEvent(){
		$('.tag-list a').each(function(){
			var tagID = $(this).attr('tagID');
			$(this).qtip({
				content:{text:'<a href="javascript:;" class="confirmedDelete" tagID="'+tagID+'">Confirm Delete</a>'},
				position:{
					my:'bottom center',
					at: 'top center'
				},
				show: {event: 'click'},
				hide: {event: 'unfocus'},
				events: {
					show: function(event, api) {
						//console.log(api.id, button);
					}
				},
				style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
			});
		})
	}

	$('.confirmedDelete').live('click', function(){
		var tagID = $(this).attr('tagID');
		alert('Deleting tag id = '+tagID);
	});
});//end