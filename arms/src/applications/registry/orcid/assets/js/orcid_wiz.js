$(function(){
	
});

$(document).on('click', '#view_xml', function(){
	var xml = load_orcid_xml();
	$('#myModal .modal-header h3').html('<h3>ORCID XML:</h3>');
	$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
	prettyPrint();
	$('#myModal').modal();
}).on('click', '#start_import', function(){
	$('#alert-msg').slideUp();
	$('#error-msg').slideUp();
	console.log(xml);
	var xml = load_orcid_xml();
	$(this).button('loading');
	var button = this;
	$.ajax({
	   type:"POST",
	   url:base_url+"orcid/push_orcid_xml",
	   data:{xml:xml},
	   success:function(data){
	     if(data=='1'){
	     	$(button).button('reset');
	     	$('#alert-msg').slideDown();
	     }else{
	     	$(button).button('reset');
	     	$('#error-msg').html(data).slideDown();
	     }
	   },
	   error: function(data){
	   	$(button).button('reset');
	   	$('#error-msg').html(data.responseText).slideDown();
	   }
	});
}).on('click', '.remove', function(){
	$(this).parent().fadeOut();
}).on('click', '.add', function(){
	$(this).parent().appendTo('#works ul');
}).on('submit', '.form-search', function(e){
	e.preventDefault();
	e.stopPropagation();
	var term = $(this).find('.search-query').val();
	if(term!=''){
		$.ajax({
		   type:"GET",
		   async:false,
		   url:base_url+"services/registry/search/?query="+encodeURIComponent(term),
		   success:function(data){
		      var template = '<ul>{{#.}}<li class="to_import" ro_id="{{id}}"><a href="'+base_url+'registry_object/view/{{id}}" target="_blank">{{value}}</a>  <a href="javascript:;" class="add"><i class="icon icon-plus"></i></a></li>{{/.}}</ul>';
			  var output = Mustache.render(template, data);
			  $('#result').html(output);
		   }
		});
	}
});

function load_orcid_xml(){
	var ids=[];
	$('#works li.to_import').each(function(){
		ids.push($(this).attr('ro_id'));
	});
	return xml = $.ajax({
	   type:"POST",
	   async:false,
	   url:base_url+"orcid/get_orcid_xml",
	   data:{ro_ids:ids},
	   success:function(data){
	     // console.log(data);
	   }
	}).responseText;
}