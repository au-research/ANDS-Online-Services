var VOCABULARY_XML = "";
var VOCABULARY_MENU_XML = "";
var VOCABULARY_XSLT = "";
var VOCABULARY_XSLT_MENU = "";
var VOCABULARY_BASE_URL = "http://devl.ands.org.au/vocab/getConcepts/ANZSRC-FOR/2008/";
var VOCABULARY_XSLT_URL = "http://devl.ands.org.au/workareas/ben/ci/ands/_xsl/skos2table.xsl";
var VOCABULARY_XSLT_MENU_URL = "http://devl.ands.org.au/workareas/ben/ci/ands/_xsl/skos2tree.xsl";

$(document).ready(function(){
	
	//Enable multiword searching
	$.expr[':'].jstree_contains_multi = function(a,i,m){

        var word, words = [];
        var searchFor = m[3].toLowerCase().replace(/^\s+/g,'').replace(/\s+$/g,'');
        if(searchFor.indexOf(' ') >= 0) {
            words = searchFor.split(' ');
        }
        else {
            words = [searchFor];
        }
        for (var i=0; i < words.length; i++) {
            word = words[i];
            if((a.textContent || a.innerText || "").toLowerCase().indexOf(word) >= 0) {
                return true;
            }
        }
        return false;

    };

    $.expr[':'].jstree_contains = function(a,i,m){

        return (a.textContent || a.innerText || "").toLowerCase().indexOf(m[3].toLowerCase())>=0;

    };
	
    
	$("#vocab_tree a").live("click", function(e) {
		displayResult($(this).parent().attr('id'));
	});

	$('#vocab_tree').jstree({
		"core" : {
			"html_titles" : true,
		},
		"ui" : {
			"select_limit" : 1,
		},
		"json_data" :
		{
			"ajax" : { 
				"url" : function(id)
				{
					return "http://devl.ands.org.au/vocab/getMenus/ANZSRC-FOR/2008/" 
							+ (id != -1 && $(id).attr("id") ? $(id).attr("id") : -1);
				}  
			}
		},
		"types" : {
			"valid_children" : "all",
			"max_depth" : -2, // disable for performance increase
			"max_children" : -2, // and again
			"types" : {
				"term" : {
					"icon" : { 
						"image" : "http://devl.ands.org.au/workareas/ben/ci/ands/_img/term_icon.jpg" 
					},
					"max_depth" : -2,
					//"hover_node" : false,
					"select_node" : function (key, args) { loadConcept(key.attr('id')); return true; }
				},
				"default" : {
					"select_node" : function (key, args) { loadVocabulary(key.attr('id')); return true; }
				}
			}
		},
		"search" : {
			"ajax" : {
				"url" : "http://devl.ands.org.au/vocab/searchMenus/"
			}
		},
		"plugins" : [ "themes", "json_data", "ui", "types", "search" ],
	});	

});

function loadVocabulary(id)
{
	$('#vocab_concept_viewer').html("Loading vocabulary: " + id);
}

function loadConcept(id)
{
	$('#vocab_concept_viewer').html("Loading concept: " + id);
}

function loadXML()
{
	$.get(VOCABULARY_BASE_URL, function(data) {
		  VOCABULARY_XML = data;
		  
		  $.get(VOCABULARY_XSLT_URL, function(data) {
			  VOCABULARY_XSLT = data;
			  buildTree();
			  displayResult('http://purl.org/au-research/vocabulary/anzsrc-for/2008/01');
			}, 'xml');
		 
		  $.get(VOCABULARY_XSLT_MENU_URL, function(data) {
			  VOCABULARY_XSLT_MENU = data;
			  buildTree();
			}, 'xml');
		},'xml');
}

function displayTree()
{
	$("#vocab_tree")
		.jstree({ 
			
			"xml_data" : {
				"data" : VOCABULARY_MENU_XML,
			},
			"types" : {
				"valid_children" : [ "root" ],
				"types" : {
					"root" : {
						"icon" : { 
							"image" : "http://static.jstree.com/v.1.0rc/_docs/_drive.png" 
						},
						"valid_children" : [ "default" ],
						"max_depth" : 3,
						"hover_node" : false,
						"open_node" : function (data) {displayResult($(data).attr("id"));}
					},
					
					"leaf" : {
						"icon" : { 
							"image" : "http://static.jstree.com/v.1.0rc/_docs/_drive.png" 
						},
						"valid_children" : [  ],
						"max_depth" : 0,
						"hover_node" : false,
						"open_node" : function (data) {displayResult($(data).attr("id"));}
					},
					
					"default" : {
						"valid_children" : [ "default" ],
						"open_node" : function (data) {displayResult($(data).attr("id"));}
					}
				}
			},

			"plugins" : [ "themes", "xml_data", "types" ]
		});	
}

function buildTree()
{
xml=VOCABULARY_XML;
xsl=VOCABULARY_XSLT_MENU;

//$(xsl).find('param').each(function(){ $(this).attr("select", "'"+target+"'"); });

// code for IE
if (window.ActiveXObject)
  {
  ex=xml.transformNode(xsl);
  VOCABULARY_MENU_XML = ex.xml;
  }
// code for Mozilla, Firefox, Opera, etc.
else if (document.implementation && document.implementation.createDocument)
  {
  xsltProcessor=new XSLTProcessor();
  xsltProcessor.importStylesheet(xsl);
  resultDocument = xsltProcessor.transformToFragment(xml,document);
  VOCABULARY_MENU_XML = (new XMLSerializer()).serializeToString(resultDocument);
  }
}

function displayResult(target)
{
xml=VOCABULARY_XML;
xsl=VOCABULARY_XSLT;

$(xsl).find('param').each(function(){ $(this).attr("select", "'"+target+"'"); });

// code for IE
if (window.ActiveXObject)
  {
  ex=xml.transformNode(xsl);
  $('#vocab_concept_viewer').html(ex);
  }
// code for Mozilla, Firefox, Opera, etc.
else if (document.implementation && document.implementation.createDocument)
  {
  xsltProcessor=new XSLTProcessor();
  xsltProcessor.importStylesheet(xsl);
  resultDocument = xsltProcessor.transformToFragment(xml,document);
  $('#vocab_concept_viewer').html(resultDocument);
  }
}