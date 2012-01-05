<?php
$this->load->view('vocab/rdf_header');
?>

<?php foreach($concept_schemes AS $scheme): 

	echo '    <rdf:Description rdf:about="' .$scheme->purl. '">' . "\n";
	echo '        <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#ConceptScheme"/>' . "\n";
	//echo '        <skos:ConceptScheme rdf:nodeID="' .$scheme->purl. '">' . "\n";
	
	foreach($scheme->attributes AS $attribute):

		if ($attribute->value != ''):
			echo '        <' . $attribute->attribute_ns . ':' . $attribute->attribute_name . '>' .
			 			    	$attribute->value . 
			     	      '</' . $attribute->attribute_ns . ':' . $attribute->attribute_name . '>' . "\n";
		else:
			echo '        <' . $attribute->attribute_ns . ':' . $attribute->attribute_name . '/>' . "\n";
		endif; 
		
	endforeach; 
	
	//echo '        </skos:ConceptScheme>';
	echo '    </rdf:Description>';

 endforeach; ?>

<?php
$this->load->view('vocab/rdf_footer');
?>
        