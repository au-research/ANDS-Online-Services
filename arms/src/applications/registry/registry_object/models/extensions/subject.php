<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Subject_Extension extends ExtensionBase
{
		
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
		
	function processSubjects()
	{
		$subjectsResolved = array();
		$this->_CI->load->library('vocab');
		$sxml = $this->ro->getSimpleXML();		
		$subjects = $sxml->xpath('//subject');
		
		foreach ($subjects AS $subject)
		{
			$type = (string)$subject["type"];
			$value = (string)$subject;
			if(!array_key_exists($value, $subjectsResolved))
			{
				$resolvedValue = $this->_CI->vocab->resolveSubject($value, $type);
				$subjectsResolved[$value] = array('type'=>$type, 'value'=>$value, 'resolved'=>$resolvedValue['value'], 'uri'=>$resolvedValue['about']);
			}
		}
		$broaderArray = $this->_CI->vocab->getBroaderSubjects();
		$allSubjects = array_merge($subjectsResolved,$broaderArray);
		return $allSubjects;
	}
	
}
	
	