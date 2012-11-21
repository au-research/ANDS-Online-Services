<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * 
 * XXX:
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/registryobject
 * 
 */

class Rifcs extends CI_Model {
		
	public function getRecordTitle(SimpleXMLElement $ro)
	{
		// XXX: Extend this!
		if($name = $ro->xpath('.//name[@type="primary"]'))
		{
			$title = '';
			foreach ($name[0]->namePart AS $part)
			{
				$title .= " " . $part;
			}
		};
		return trim($title);	
	} 
	
	
	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
	}	
		
}
