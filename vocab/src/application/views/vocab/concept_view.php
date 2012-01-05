<?php
$this->load->view('vocab/rdf_header');
?>

<?php 
if (!isset($concept_schemes)) $concept_schemes = array();
foreach($concept_schemes AS $concept): 

	echo '    <rdf:Description rdf:about="' .$concept->purl. '">' . "\n";
	echo '        <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#ConceptScheme"/>' . "\n";
	
	foreach($concept->attributes AS $attribute):

		if ($attribute->value != ''):
			echo '        <' . ($attribute->attribute_ns ? $attribute->attribute_ns . ':' : '') . $attribute->attribute_name . '>' .
			 				$attribute->value . 
			     	'</' . ($attribute->attribute_ns ? $attribute->attribute_ns . ':' : '') . $attribute->attribute_name . '>' . "\n";
		else:
			echo '        <' . ($attribute->attribute_ns ? $attribute->attribute_ns . ':' : '') . $attribute->attribute_name . '/>' . "\n";
		endif; 
		
	endforeach; 
	
	foreach($concept->relationships AS $relationship):

		echo '        <' . ($relationship->relationship_ns ? $relationship->relationship_ns . ':' : '') . $relationship->relationship_type . ' rdf:resource="' . $relationship->purl . '"/>' . "\n";
		
	endforeach; 
	
	echo '    </rdf:Description>' . "\n";

endforeach; 
?>
<?php 
if (!isset($concepts)) $concepts = array();
foreach($concepts AS $concept): 

	echo '    <rdf:Description rdf:about="' .$concept->purl. '">' . "\n";
	echo '        <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>' . "\n";
	
	foreach($concept->attributes AS $attribute):

		if ($attribute->value != ''):
			echo '        <' . ($attribute->attribute_ns ? $attribute->attribute_ns . ':' : '') . $attribute->attribute_name . '>' .
			 				$attribute->value . 
			     	'</' . ($attribute->attribute_ns ? $attribute->attribute_ns . ':' : '') . $attribute->attribute_name . '>' . "\n";
		else:
			echo '        <' . ($attribute->attribute_ns ? $attribute->attribute_ns . ':' : '') . $attribute->attribute_name . '/>' . "\n";
		endif; 
		
	endforeach; 
	
	foreach($concept->relationships AS $relationship):

		echo '        <' . ($relationship->relationship_ns ? $relationship->relationship_ns . ':' : '') . $relationship->relationship_type . ' rdf:resource="' . $relationship->purl . '"/>' . "\n";
		
	endforeach; 
	
	echo '    </rdf:Description>' . "\n";

endforeach; 
?>
<?php
$this->load->view('vocab/rdf_footer');
?>
        