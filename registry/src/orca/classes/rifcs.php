<?php
class RifCsProcessor
{
  private $dom;
  private $registryObjects;
  
  function RifCsProcessor()
  {
  	  $this->$dom = new DOMDocument(); 	
  }
    
  function loadRifcs($registryObjectString)
  {      
      $this->$dom->loadXml($registryObjectString);  
  	  $this->$registryObjects = new registryObjects();
  	  $this->$registryObjects->load($dom);
      return $this;
  }
  
  function loadRifcsFile($filePath)
  { 
      $this->$dom->load($filePath);  
  	  $this->$registryObjects = new registryObjects();
  	  $this->$registryObjects->load($dom);
      return $this;
  	  
  }
  
  function createRegistryObjects()
  {
  	  $this->$registryObjects = new RegistryObjects($dom);
  	  $this->$currentElement = $this->$RegistryObjects;
  	  return $this->$RegistryObjects;  
  }
   
  function getString()
  {  	
 	  return $dom->saveXML();  	
  }
  
  function saveToFile($filePath)
  {  	
  	  $dom->formatOutput = true;
  	  return $dom->save($filePath);  	
  }
  
}

class registryObjects
{
	
	var $registryObjects;
	var $registryObjectArray;
	var $dom;

	function registryObjects()
	{
		$this->$registryObjectArray = Array();
		return $this;
	}
	
	function registryObjects($domDocument)
	{
		$this->$dom = $domDocument;
		$this->$registryObjectArray = Array();
		$this->$registryObjects = $domDocument->createElement('registryObjects');
		return $this;
	}
		
	function load($domDocument)
	{
		$this->$RegistryObjects = $domDocument->getElementsByTagname("registryObjects")->item(0);
	  	$registryObjectList = $this->$RegistryObjects->getElementsByTagname("registryObject");
  	  	for($i = 0; $i < $registryObjectList->length; $i++)
  	  	{
  	  	    $ro = new registryObject();
  	  		$this->$registryObjectArray.push($ro->load($registryObjectList->item($i)));
  	  	}
	}
			
	function addRegistryObject($domElement)
	{
		$this->$registryObjectArray.push(new registryObject($domElement));		
	}
}

class registryObject
{
	// $caps = collection, activity, party, service
	private $eRegistryObject;
	private $CAPS;
	var $dom;
	private $eKey;
	
	function registryObject()
	{	
		return $this;
	}
		
	function registryObject($domDocument, $roKey, $roGroup, $originatingSource, $originatingSourceType)
	{
		$this->$dom = $domDocument;
		$this->$eRegistryObject = $domDocument->createElement('registryObject');
		$group = $domDocument->createAttribute('group');
		$group->value = $roGroup;
		$this->$eRegistryObject->appendChild($group);
		$this->$key = $domDocument->createElement('key',$roKey);
		$this->$eRegistryObject->appendChild($this->$key);
		$eOriginatingSource = $domDocument->createElement('originatingSource',$originatingSource);
		if($originatingSourceType != '')
		{
			$aOriginatingSourceType = $domDocument->createAttribute('type');
		    $aOriginatingSourceType->value = $originatingSourceType;
			$eOriginatingSource->appendChild($aOriginatingSourceType);
		}
		$this->$eRegistryObject->appendChild($eOriginatingSource);
		return $this;
	}
	
	function load($domElement)
	{
		$this->$dom = $domElement->ownerDocument;
		$this->$eRegistryObject = $domElement;
		$this->$key = $domElement->getElementsByTagname('key')->item(0)->nodeValue;	
		if($domElement->getElementsByTagname('collection'))
		{
			$this->$CAPS = new collection();
			$this->$CAPS->load($domElement->getElementsByTagname('collection')->item(0));			
		}
		if($domElement->getElementsByTagname('activity'))
		{
			$this->$CAPS = new activity();
			$this->$CAPS->load($domElement->getElementsByTagname('activity')->item(0));			
		}
		if($domElement->getElementsByTagname('party'))
		{
			$this->$CAPS = new party();
			$this->$CAPS->load($domElement->getElementsByTagname('party')->item(0));			
		}
		if($domElement->getElementsByTagname('service'))
		{
			$this->$CAPS = new service();
			$this->$CAPS->load($domElement->getElementsByTagname('service')->item(0));			
		}
		return $this;
	}
	
	function registryObject($domElement)
	{
		$this->$dom = $domElement->ownerDocument;
		$this->$eRegistryObject = $domElement;
		$this->$key = $domElement->getElementsByTagname('key')->item(0)->nodeValue;		
		return $this;
	}
	
	
	
	function getElement()
	{
		return $this->$eRegistryObject;
	}
	

}


class collection
{
	var $dom = null;
	private $collection = null;
	
	function activity()
	{
		return $this;
	}
	
	function activity($domDocument, $type, $dateModified)
	{
		$this->$dom = $domDocument;
		$this->$eCaps = $domDocument->createElement('collection');
		return $this;
	}
	
	function load($domElement)
	{
		$this->$dom = $domElement->ownerDocument;
		$this->$activity = $domElement;		
		return $this;
	}

}



class activity
{
	var $dom = null;
	private $activity = null;
	
	function activity()
	{
		return $this;
	}
	
	function activity($domDocument, $type, $dateModified)
	{
		$this->$dom = $domDocument;
		$this->$eCaps = $domDocument->createElement('activity');
		return $this;
	}
	
	function load($domElement)
	{
		$this->$dom = $domElement->ownerDocument;
		$this->$activity = $domElement;		
		return $this;
	}

}

?>