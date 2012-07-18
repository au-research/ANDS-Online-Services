/********************************************************************************
$Date: 2011-12-08 10:48:04 +1100 (Thu, 08 Dec 2011) $
$Revision: 1665 $
*******************************************************************************/
var clearPressed = false;
var cosiImageRoot = rootAppPath + "_images/";
var orcaImageRoot = rootAppPath + "orca/_images/";


function setHarvestMethodDependents()
{
   var tableRow = 'table-row';
   if( !browserSupportsTableRowCSS() )
   {
       tableRow = 'block';
   }

   var objHarvestMethod = getObject('harvest_method');
   var strHarvestMethod = objHarvestMethod.value;

   // Harvest methods.
   // These values are from orca_init.php and so any changes there will require
   // corresponding changes here.
   // define('gORCA_HARVEST_METHOD_DIRECT', 'DIRECT');
   // define('gORCA_HARVEST_METHOD_HARVESTER_DIRECT', 'GET');
   // define('gORCA_HARVEST_METHOD_HARVESTER_OAIPMH', 'RIF');

   // Only show the OAI Set field if the Harvest Method is gORCA_HARVEST_METHOD_HARVESTER_OAIPMH.
   var rowDisplay = tableRow;
   if( strHarvestMethod != 'RIF' )
   {
       rowDisplay = 'none';
   }
   getObject('oai_set_row').style.display = rowDisplay;

   // Only show the Harvest Date and Harvest Frequency fields if the harvester
   // is being used (i.e. not a DIRECT harvest).
   rowDisplay = 'none';
   if( strHarvestMethod != 'DIRECT' && strHarvestMethod != '')
   {
       rowDisplay = tableRow;
   }
   getObject('harvest_date_row').style.display = rowDisplay;
   getObject('harvest_frequency_row').style.display = rowDisplay;
}

function showHideTableRowGroup(id)
{
   var tableRowGroup = 'table-row-group';
   if( !browserSupportsTableRowCSS() )
   {
       tableRowGroup = 'block';
   }

   var rowGroup = getObject(id);

   var icon = null;
   if( getObject(id+'_icon') )
   {
       icon = getObject(id+'_icon');
   }

   if( rowGroup.style.display == tableRowGroup )
   {
       rowGroup.style.display = 'none';
       if( icon )
       {
           icon.className = 'menuLink';
           icon.title = 'Show details';
       }
   }
   else
   {
       rowGroup.style.display = tableRowGroup;
       if( icon )
       {
           icon.className = 'menuLinkOpen';
           icon.title = 'Hide details';
       }
   }
}

function setFormView(form, id)
{
   var action = form.getAttribute('action');
   action += "#" + id;
   form.setAttribute('action', action);
}

var vcImagesRootPath = '';
var vcOpenControl = null;

function vcSetImagePath(path)
{
   vcImagesRootPath = path;
}

function vcDisplayVocabControl(inputFieldId, controlId)
{
   var control = getObject(controlId);
   var inputField = getObject(inputFieldId);
   var iconId = controlId + '_vcIcon';
   var icon = getObject(iconId);

   if( inputField && control )
   {
       if( !isObjectDisplayed(control) )
       { // The control is not displayed.
           // Close any already open vocab controls.
           if( vcOpenControl )
           {
               vcCloseVocabControl(vcOpenControl.id);
           }
           // Make the control hidden.
           control.visibility = "hidden";
           // Display the control so that we can calculate widths and update the icon.
           displayObjectNear(control, icon, RELATIVE_POSITION_BELOW_RIGHT);
           // Initialise the control.
           vcInitControl(inputFieldId, controlId);
           // Make the control visible
           control.visibility = "visible";
           // Update the vocab icon.
           updateImage(iconId, vcImagesRootPath + 'vc_icon_active.gif', 'Suggested vocabulary');
           // Set this as the currently open vocab control.
           vcOpenControl = control;
       }
       else
       { // The control is displayed, so hide it.
           vcCloseVocabControl(controlId);
       }
   }
}

function vcInitControl(inputFieldId, controlId)
{
   var inputField = getObject(inputFieldId);
   var currentValue = inputField.value;

   var control = getObject(controlId);
   var divList = control.getElementsByTagName("DIV");

   var lastSelectedTerm = null;
   var contentDiv = null;
   var scrollPaneDiv = null;

   if( divList != null )
   {
       // Highlight any selected terms.
       for( var i=0; i < divList.length; i++ )
       {
           if( divList[i].title != '' )
           {
               divList[i].className = 'vcTerm';
               if( divList[i].title == currentValue )
               {
                   divList[i].className = 'vcTermSelected';
                   lastSelectedTerm = divList[i];
               }
           }
       }

       // Get the control components needed to set up the layout.
       for( var i=0; i < divList.length; i++ )
       {
           if( divList[i].className == 'vcContent' )
           {
               contentDiv = divList[i];
           }
           if( divList[i].className == 'vcScrollPane' )
           {
               scrollPaneDiv = divList[i];
           }
       }

       if( !contentDiv.style.width )
       {
           // Setup the content width and control size for overflow.
           contentDiv.style.width = contentDiv.offsetWidth + 'px';
           scrollPaneDiv.style.minWidth = '170px';
           scrollPaneDiv.style.maxWidth = '350px';
       }
    }

   if( lastSelectedTerm )
   {
       // Scroll to the last selected term in the list.
       scrollPaneDiv.scrollTop = lastSelectedTerm.offsetTop - 40;
   }
}

function vcCloseVocabControl(controlId)
{
   var control = getObject(controlId);
   var iconId = controlId + '_vcIcon';

   displayObject(control, 'none');
   updateImage(iconId, vcImagesRootPath + 'vc_icon_inactive.gif', 'Suggested vocabulary');
   vcOpenControl = null;
}

function vcUpdateInputFieldValue(inputFieldId, controlId, value)
{
   var control = getObject(controlId);
   var inputField = getObject(inputFieldId);

   inputField.value = value;
   vcCloseVocabControl(controlId);
}

function updateAROTab(tab)
{
   var AroTabText;
   var AroTabTextContainer;
   for(var i = 1; i <10; i++)
   {
       aroTabText = getObject('aro-tab-'+i);
       aroTabTextContainer = getObject('aro-tab-'+i+'-content');
       aroTabText.className = 'aro-tab';
       aroTabTextContainer.style.display  = 'none';
   }
   aroTabText = getObject(tab);
   aroTabTextContainer = getObject(tab +'-content');
   aroTabText.className = 'aro-tab-active';
   aroTabTextContainer.style.display  = '';
}



function setClear()
{
   clearPressed = true;
}


function runQualityCheck()
{
   
   $.get($('#quality_report_url').val() + '&data_source=' + $('#data_source_key').val(), function(data){
	   $('#qualityCheckresult').html(data).fadeIn(100);
	   $('#printableReportContainer').html('<a target="_blank" id="printable_report" href="data_source_report.php?type=quality&standalone=true&data_source=' + $('#data_source_key').val()  +'" class="right">printable report</a>');
	   
   })
}

function showDeleteModal()
{

   var maskHeight = $(document).height();
       var maskWidth = $(window).width();
       var idVal = "#delete_warning_box";
       //Set heigth and width to mask to fill up the whole screen

       $('#mask').css({'width':maskWidth,'height':maskHeight});

       //transition effect
       //$('#mask_'+id).fadeIn(1000);
       $('#mask').fadeTo("fast",0.4);

       //Get the window height and width
       var winH = $(window).height();
       var winW = $(window).width();
       //Set the popup window to center
       $(idVal).css('position', 'fixed');
       $(idVal).css('top',  winH/2-$(idVal).height()/2);
       $(idVal).css('left', winW/2-$(idVal).width()/2);
       //transition effect
       $(idVal).fadeIn(200);

}


function closeDeleteModal()
{
       $('#mask').hide();
       $("#delete_warning_box").hide();
}

function getRIFCSHistory(anchor, data_source, key, version)
{
   $(anchor).attr("src",rootAppPath + "orca/_images/ajax_small.gif");
   $.get(rootAppPath + 'orca/manage/process_registry_object.php?task=fetch_record&data_source='+encodeURIComponent(data_source)+'&key='+encodeURIComponent(key)+'&version='+encodeURIComponent(version),
       function(data) {
         $('#rifcs_display_content').html(data);
         $('#rifcs_display_block').show();
         $(anchor).attr("src",rootAppPath + "orca/_images/arrow_top_right.png");
       }
   );
}

function recoverRIFCS(anchor, data_source, key, version)
{
   if (confirm('This will create a draft record called RECOVERED_' + key + ' - any existing draft with this name will be overwritten.\n\nAre you sure you wish to continue recovering this record?'))
   {
       $.get(rootAppPath + 'orca/manage/process_registry_object.php?task=recover_record&data_source='+encodeURIComponent(data_source)+'&key='+encodeURIComponent(key)+'&version='+encodeURIComponent(version),
               function(data) {
                 if (data == "END")
                 {
                     window.location = rootAppPath + 'orca/manage/list_registry_objects.php';
                 }
                 else
                 {
                    alert("ERROR: Unexpected Data\n\n" + data);
                 }
               }
           );
   }
}

function confirmSubmit(message){
	var agree=confirm(message);
	if(agree){
		return true;
	}else return false;
}

function doSolrSearch()
{
   var solrURL = $('#solrUrl').val();
   //alert(solrURL);

   /*if(window.location.href.indexOf('https')==0){
       solrURL='https'+solrURL;
   }else{
       solrURL='http'+solrURL;
   }*/
   var search_term = $('#search').val();
   if (search_term=='') search_term = '*:*';
   var oClass = '';
   if($('#collections').attr('checked') == true){oClass += $('#collections').val();}
   if($('#parties').attr('checked') == true){oClass += $('#parties').val();}
   if($('#services').attr('checked') == true){oClass += $('#services').val();}
   if($('#activities').attr('checked') == true){oClass += $('#activities').val();}
   oClass = oClass.substring(0, oClass.length -1);
   var oGroup = $('#object_group').val();
   var oDS = $('#source_key').val();
   var oStatus = $('#status').val();
   var oSubject = 'All';
   var oPage = $('#page').val();

   $('#search-result').hide();
   //alert('query='+search_term+'&class='+oClass+'&page='+oPage+'&group='+oGroup+'&subject='+oSubject);
   $.ajax({
         type: 'POST',
         url: solrURL,
         data: 'query='+search_term+'&class='+oClass+'&page='+oPage+'&group='+oGroup+'&subject='+oSubject+'&source_key='+oDS+'&status='+oStatus,
         success: function(msg){
             //alert(msg);
             $('#search-result').html(msg);
             $('#search-result').fadeIn();
         },
         error: function(msg){
             //alert('ERROR'+msg);
            // console.log(msg);
         }
       });
   //alert(search_term);
}



function getGoldRecords()
{
   var solrURL = $('#solrUrl').val();
   alert(solrURL);

   /*if(window.location.href.indexOf('https')==0){
       solrURL='https'+solrURL;
   }else{
       solrURL='http'+solrURL;
   }*/
   var oPage = $('#page').val();
   $('#search-result').hide();
   //alert('query='+search_term+'&class='+oClass+'&page='+oPage+'&group='+oGroup+'&subject='+oSubject);
   $.ajax({
         type: 'POST',
         url: solrURL,
         data: 'query=fish&page='+oPage,
         success: function(msg){
             alert("response"+msg);
             $('#search-result').html(msg);
             $('#search-result').fadeIn();
         },
         error: function(msg){
             //alert('ERROR'+msg);
             console.log(msg);
         }
       });
}

var dsIsBGTaskQueued = false;

function checkDataSourceScheduleTask(){
    var content = 'A background task is currently in progress for this data source. Please try reloading the screen again shortly.';
    var dataSourcekey = $('#dataSourceKey').length;
    var dsKey = $('#dataSourceKey').val();
    //console.log(dataSourcekey);
    if(dataSourcekey==1){
     
      //console.log(rootAppPath+"orca/manage/process_registry_object.php?task=dataSourceBackgroundStatus&data_source="+dsKey);
      $.ajax({
        type:"POST",   
        url:rootAppPath+"orca/manage/process_registry_object.php?task=dataSourceBackgroundStatus&data_source="+dsKey,   
        success:function(msg){
          
          if(msg=='1'){
          	dsIsBGTaskQueued = true;
            $('#dataSourceStatus').show();
            $('#dataSourceStatus').html(content);
          }else{
          	if (dsIsBGTaskQueued == true)
          	{
          		location.reload();	
          	}
            $('#dataSourceStatus').hide();
          }
        }
    });
    }
  }

$().ready(function(){

  

	/*
	 * CC-47
	 * RIFCS-BUTTON on view page
	 */
	$('#rifcs_button').click(function(e){
		$('#rifcs_popup').show();
		e.stopPropagation();
	});
	
	$('body').click(function(){
		$('#rifcs_popup').hide();
	});
	
	$('#rifcs_view').click(function(){
		var key = $('#key').html();
		$.get(rootAppPath + 'orca/services/getRegistryObject.php?key='+encodeURIComponent(key)+'&type=plain',
	       function(data) {
			$('#rifcs_plain_content').val(data);
	        $.blockUI({
	            message: $('#rifcs_plain'),
	            css: {
	                width: '600px',
	                top:'20%',
	                left:'20%',
	                textAlign: 'left',
	                padding: '10px'
	                },
	                overlayCSS: { backgroundColor: '#000', opacity:   0.6}
            	});
            $('.blockOverlay').attr('title','Click to unblock').click($.unblockUI);
            $('.closeBlockUI').click($.unblockUI);
	       }
	   );
		$('#rifcs_popup').hide();
	});
	
	$('#rifcs_download').click(function(){
		$('#rifcs_popup').hide();
	});
	
	
	function htmlEntities(str) {
	    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}
	
	
	
	
	/*
	 * SOLR on Search page
	 */
   $('#solr-input').click(function(){
       $('#page').val('1');
       doSolrSearch();
   });

   $('#search').keypress(function(e){
       if(e.which==13){//press enter
           $('#page').val('1');
           doSolrSearch();
       }
   }).keyup(function(){//on typing
       //doSolrSearch();
   });


   $('.subjectFilter').live('click', function(){
       var solrURL = $('#solrUrl').val();
       var oSubject = $(this).attr('name');
       $.ajax({
         type: 'POST',
         url: solrURL,
         data: 'query=*:*&class=All&page=1&group=All&subject='+oSubject,
         success: function(msg){
             //console.log(msg);
             $('#search-result').html(msg);
         },
         error: function(msg){
             alert('ERROR'+msg);
         }
       });
   });

   $('.gotoPage').live('click', function(){
       var nextPage = $(this).attr('id');
       $('#page').val(nextPage);
       doSolrSearch();
   });

   $('.pagination-page').live('click', function(){
       var direction = $(this).attr('id');

       var currentPage = parseInt($('#page').val());
       if(direction == 'next'){
           nextPage = currentPage + 1;
       }else if(direction == 'prev'){
           nextPage = currentPage - 1;
       }
       $('#page').val(nextPage);
       doSolrSearch();
   });

   $('.infoIcon').click(function(e){
       e.stopImmediatePropagation();
       var id = $(this).attr('id');
       var logTypeID = id.replace(/_info/,"_type");
       var logDescriptionID = id.replace(/_info/,"_desc");
       var description = $('#'+logDescriptionID).html();
       var title = $('#'+logTypeID).html();
       description = formatErrorDesciption(description, title);
       $.blockUI({
       message: description,
       css: {
           top:  ($(window).height() - 800) /2 + 'px',
           left: ($(window).width() - 400) /2 + 'px',
           width: '600px',
           textAlign: 'left',
            padding: '10px'},
       overlayCSS: { backgroundColor: '#000', opacity:   0.6}
       });
       $('.blockOverlay').attr('title','Click to unblock').click($.unblockUI);
   });

   //chosen on data source;
   if($('select[name=data_source_key]').length>0)$('select[name=data_source_key]').chosen();

});

function formatErrorDesciption(description, title)
{

descContent = '<HR><h3>Error Description:</h3><HR><img src="../_images/error_icon.png" onClick="$.unblockUI();" alt="CLOSE" title="CLOSE" style="cursor:pointer; position:absolute; top:5px; right:5px; width:16px;" /><br>';

   if(title == 'DOCUMENT_LOAD_ERROR')
   {
       descContent += '<div align="center" width="100%"><img src="../_images/Load_Error.jpg" width="600"/></div><br>';
       descContent += 'Title: <b><i>XML Document failed to load</i></b>';

       if(description.indexOf("I/O warning") > 0)
       {
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;There was a problem communicating with the datasource provider. Host could not be found.';
           descContent += '<p>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure you have specified the correct URI.';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("Extra content at") > 0 /*|| description.indexOf("Opening and ending tag mismatch") > 0*/)
       {
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to load, as the xml document contains additional content that does not validate against the xml schema.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- All XML tags must be nested properly.';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your records are structured correctly';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure all your parent and child nodes are correctly formatted (open and closed objects)';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://www.w3.org/TR/1998/REC-xml-19980210.html\'>- XML Specifications</a>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://ands.org.au/guides/cpguide/cpgrelatedobject.html\'>- Content Providers Guide (Related Objects)</a>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://ands.org.au/guides/content-providers-guide.html\'>- Content Providers Guide</a>';
           descContent += '<p><br>';
           descContent += '<b><i>External References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://www.tizag.com/xmlTutorial/xmlparent.php\'>- XML Parent information</a>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://www.tizag.com/xmlTutorial/xmlchild.php\'>- XML Child information</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("Premature end of data") > 0)
       {
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to load, as the XML may not be correctly formed.';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document contains possible elements with no closing tags';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your objects have been closed correctly. For example if you have an open tag \'&lt;key&gt;\' ensure you have a closed tag \'&lt;/key&gt;\'';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://ands.org.au/guides/content-providers-guide.html\'>- Content Providers Guide</a>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>- RIF-CS Documentation</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("Opening and ending tag mismatch") > 0)
       {
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to load, as the xml document contains an opening element tag that does not match it\'s corresponding closing tag.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your opening and closing tags are correctly formatted and titled';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>RIF-CS Documentation</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("expected") > 0 || description.indexOf("Start tag expected") > 0)
       {
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to load, as the xml document contains an error that could not be defined.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your opening and closing tags are correctly formatted and titled';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your records are structured correctly';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>RIF-CS Documentation</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("Namespace prefix xsi") > 0)
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to load, as the xml document does not contain an xml Namespace.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that \'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"\' has been defined prior to the xml schemaLocation, within your XML document.';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else
       {
           
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to load due to an indeterminable error.';
           descContent += '<p>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Review your xml document and ensure that the xml is correctly formed.';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>RIF-CS Documentation</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
           
           //descContent += description;
       }
   }
   else if(title == 'DOCUMENT_VALIDATION_ERROR')
   {
       descContent += '<div align="center" width="100%"><img src="../_images/Validation_Error.jpg" width="600"/></div><br>';
       descContent += 'Title: <b><i>Document Validation Error</i></b>';
       if(description.indexOf("Character content other than whitespace is not allowed") > 0)
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to validate against the RIF-CS schema, the document contains characters that are not valid.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your xml file does not contain any invalid characters.<br />';
           descContent += '<HR>';
       }
       else if(description.indexOf("required but missing") > 0 || description.indexOf("is not allowed") > 0)
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to validate against the RIF-CS schema, the document contains an element attribute that is required but is missing.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your records within your xml document have all required elements and their associated attributes.';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all registry Object attributes are correctly spelled and labeled.';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://ands.org.au/guides/cpguide/cpgrelatedobject.html\'>- Content Providers Guide (Related Objects)</a>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://ands.org.au/guides/content-providers-guide.html\'>- Content Providers Guide</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("Character content other than whitespace is not allowed because") > 0) // done
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to validate against the RIF-CS schema, the document contains characters that are not valid.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that your xml file does not contain any invalid characters';
           descContent += '<p><br>';
           descContent += '<HR>';
       }
       else if(description.indexOf("element is not expected") > 0)
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to validate against the RIF-CS schema, the document contains an element that does not exist.<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your records within your document have valid elements specified.';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>- RIF-CS Documentation</a>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://ands.org.au/guides/content-providers-guide.html\'>- Content Providers Guide</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
       }

       else if(description.indexOf("Missing child element") > 0)
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to validate against the RIF-CS schema, the document is missing a child element<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that all your records within your xml document have valid elements</li>';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<br>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>- RIF-CS Documentation</a>';
           descContent += '<p><br>';
           descContent += '<b><i>External References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://www.tizag.com/xmlTutorial/xmlchild.php\'>- XML Child information</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
           
       }

       else
       {
           
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;Your xml document failed to validate against the RIF-CS schema, due to an indeterminable error.';
           descContent += '<p>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Review your xml document and ensure that the xml is correctly formed.';
           descContent += '<p><br>';
           descContent += '<b><i>Internal References:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;<a target=\'_blank\' href=\'http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html\'>RIF-CS Documentation</a>';
           descContent += '<p><br>';
           descContent += '<HR>';
           
           //descContent += description;
       }


   }
   else if(title == 'HARVESTER_ERROR')
   {
       descContent += '<div align="center" width="100%"><img src="../_images/Harvester_Error.jpg" width="600"/></div><br>';
       descContent += 'Title: <b><i>Harvester Error</i></b>';

       if(description.indexOf("UnknownHostException:") > 0)
       {
           descContent += '<p>'
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;There was a problem communicating with the datasource provider. Host could not be found<br/>';
           descContent += '<p><br>';
           descContent += '<b><i>Resolution Suggestion:</i></b>';
           descContent += '<p>';
           descContent += '&nbsp;&nbsp;&nbsp;&nbsp;- Ensure that the url you provided is valid and available';
           descContent += '<p><br>';
           descContent += '<HR>';
          
       } else {
          
           descContent += '<p>' + description + '</p>';
       }
   }
   else
   {
       descContent = '<h3>'+title+'</h3>';

   }
   descContent += description;
return descContent;
}

function setInstitutionalPage(theValue, theGroups, theDataSource)
{
	
	var groups=theGroups.split(":::");
	
	if(theValue.value == '0')
	{
				for(i=0;i<groups.length;i++)
		{
			var thePage = document.getElementById("group"+(i+1)+"page");
			if(thePage)
			{
				thePage.innerHTML = '';
			}
		}
	}
	else if(theValue.value=='1')
	{

		for(i=0;i<groups.length;i++)
		{
			var thePage = document.getElementById("group"+(i+1)+"page");
			if(thePage)
			{
				thePage.innerHTML = '';
			}
		}
	}
	else
	{
		for(i=0;i<groups.length;i++)
		{
			
			datasources = groups[i].split("|||");
			groups[i] = datasources[0];
			
			var dsNum = datasources.length;

			var datasourceStr = '';
			for(j=1;j<dsNum;j++)
			{
				datasourceStr += '<option value="'+datasources[j]+'">'+datasources[j]+'</option>'
			}
			
			var thePage = document.getElementById("group"+(i+1)+"page");
			var theValue = document.getElementById('object_institution_key_'+(i+1)+'_current');
			if(!theValue){theValue='';}
			var searchStr = '<div id="searchDialog_object_institution_key_'+(i+1)+'" class="window" } \' > ';
			searchStr += '<img src="../_images/error_icon.png" onClick=\'closeSearchModal("object_institution_key_'+(i+1)+'");\' style="cursor:pointer; position:absolute; top:5px; right:5px; width:16px;" />';
			searchStr += '<ul id="guideNotes_relatedObject" class="guideNotes" style="display: block; ">';
			searchStr += '<li>The name search will only return the first 10 entries found in the database.<br/> To narrow down the returned results please ensure your text entries are as specific as possible.</li>';
			searchStr += '</ul>';
			searchStr += '<table class="rmdElementContainer" style="font-weight:normal;">';
			searchStr += '<tbody class="formFields andsorange">';
			searchStr += '<tr><td>Search by name:</td><td><input type="text" id="object_institution_key_'+(i+1)+'_name" autocomplete="on" name="object_institution_key_'+(i+1)+'_name" maxlength="512" size="30"/></td></tr>';
			searchStr += '<tr><td>Select object class:</td><td><span style="color:#666666">Party</span><input type="hidden" id="select_institution_key_'+(i+1)+'_class" value = "Party"/></td></tr>';
			searchStr += '<tr><td>Data source:<input type="hidden" id="select_institution_key_'+(i+1)+'_group" value="'+datasources[0]+'"/><input type="hidden" id="select_institution_key_'+(i+1)+'_dataSource" value="'+theDataSource+'"/></td><td>'+theDataSource+'</td></tr>';
			searchStr += '<tr><td><input type="button" value="Choose Selected" onClick=\'setRelatedId("object_institution_key_'+(i+1)+'");\'/></td><td></td></tr>';
			searchStr += '</table>';				
			searchStr += '</div>'; 
			searchStr += '<div class="mask" onclick="closeSearchModal(\'object_institution_key_'+(i+1)+'\')" id="mask_object_institution_key_'+(i+1)+'"></div>';
			
			var inputStr = searchStr + '<input type="hidden" name="group_'+(i+1)+'" value="'+groups[i]+'"><input type="text" name="institution_key_'+(i+1)+'" id="object_institution_key_'+(i+1)+'_value" size="25" maxlength="128" value="'+theValue.value+'" />';
			inputStr +='<img name="relatedImg" src="../_images/preview.png" onClick=\'showSearchModal("object_institution_key_'+(i+1)+'"); \' style="cursor:pointer; display:inline; margin-left:8px; vertical-align:bottom; height:16px; width:16px;" />';
			if(thePage){
				thePage.innerHTML = inputStr;
				addRelatedObjectAutocomplete("object_institution_key_"+(i+1)+"_name");
			}
			
		}
	
	}
		
}
