<?php

 $registryObjects = new DOMDocument();
 $registryObjects->loadXML('<?xml version="1.0" ?><registryObjects><registryObject group="Hello World"></registryObject></registryObjects>');
 $registryObjects->schemaValidate('http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');
 
$errors = error_get_last();
if( $errors )
{
	echo "Document Validation Error: ".$errors['message']."\n";
}
 
