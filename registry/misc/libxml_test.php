<?php

 $registryObjects = new DOMDocument();
<<<<<<< HEAD
 $registryObjects->loadXML('<?xml version="1.0" ?><registryObjects><registryObject group="Hello World"></registryObject></registryObjects>');
=======
 $registryObjects->loadXML('<registryObjects><registryObject group="Hello World"></registryObject></registryObjects>');
>>>>>>> b587b7a8d6fa3441bff9b1be8d1fe6d574a7b00a
 $registryObjects->schemaValidate('http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd');
 
$errors = error_get_last();
if( $errors )
{
	echo "Document Validation Error: ".$errors['message']."\n";
}
<<<<<<< HEAD
 
=======
 
>>>>>>> b587b7a8d6fa3441bff9b1be8d1fe6d574a7b00a
