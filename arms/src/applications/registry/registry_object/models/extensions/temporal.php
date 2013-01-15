<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Temporal_Extension extends ExtensionBase
{
		
	private $minYear = 9999999;
	private $maxYear = 0;
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
		
	function processTemporal()
	{
		
		$this->minYear = 9999999;
		$this->maxYear = 0;
		$temporalArray = array();
		$sxml = $this->ro->getSimpleXML();		
		$temporals = $sxml->xpath('//date');
		foreach ($temporals AS $temporal)
		{
			$type = (string)$temporal["type"];
			$value = $this->getWTCdate((string)$temporal);
			if($value)
			$temporalArray[] = array('type'=>$type,'value'=>$value);
		}
		return $temporalArray;
	}
	
	function getEarliestAsYear()
	{
		//TODO: write the function :-)
		return $this->minYear;
	}

	function getLatestAsYear()
	{
		//TODO: write the function :-)
		return $this->maxYear;
	}

	function getWTCdate($value)
	{
		date_default_timezone_set('UTC');
		if (($timestamp = strtotime($value)) === false) {
	    	return false;
		} else {
	     	//return date('Y-m-d\TH:i:sP', $timestamp);
	     	//
	     	$date = getDate($timestamp);
	     	if($date['year'] > $this->maxYear)
	     		$this->maxYear = $date['year'];
	     	if($date['year'] < $this->minYear)
	     		$this->minYear = $date['year'];
	     	return date('Y-m-d\TH:i:s\Z', $timestamp);
		}
	}
}