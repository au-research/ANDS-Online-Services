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
	     	log(data);
	     }
	   }
	});
});

function load_orcid_xml(){
	var ids=[];
	$('.to_import').each(function(){
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