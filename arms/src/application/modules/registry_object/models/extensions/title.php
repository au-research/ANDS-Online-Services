<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Title_Extension extends ExtensionBase
{
	
	const DEFAULT_TITLE = "(no name/title)";
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
		 
	function updateTitles()
	{
		$list_title = self::DEFAULT_TITLE;
		$display_title = self::DEFAULT_TITLE;
		
		$sxml = simplexml_load_string($this->ro->getXML());
		if ($sxml)
		{
			// Pick a name, given preference to the primary name
			$name = '';
			$names = $sxml->xpath('//'.$this->ro->class.'/name[@type="primary"]');
			if (count($names) == 0)
			{
				$names = $sxml->xpath('//'.$this->ro->class.'/name');
			}
			
			if (count($names) > 0)
			{
				// Pick the first one (this used to be undeterministic, 
				// but this could result in multiple SLUGs which is stupid)
				$name = $names[0];
				
			}
			
			if ($name && $this->ro->class != 'party')
			{
				// Join together the name parts with spaces
				// N.B. Order is not explicitly defined!
				$parts_accumulator = array();
				foreach($name->namePart AS $np)
				{
					$parts_accumulator[] = (string) $np;
				}
				$name = trim(implode(" ", $parts_accumulator));
				if ($name != '')
				{
					$list_title = $name;
					$display_title = $name;
				}
			}
			elseif ($name && $this->ro->class == 'party') 
			{
				// Ridiculously complex rules for parties
				// First lets accumulate all the name parts into their types
				$partyNameParts = array();
				$partyNameParts['title'] = array();
				$partyNameParts['suffix'] = array();
				$partyNameParts['initial'] = array();
				$partyNameParts['given'] = array();
				$partyNameParts['family'] = array();
				$partyNameParts['user_specified_type'] = array();
				
				foreach($name->namePart AS $namePart)
				{
					if (in_array(strtolower((string) $namePart['type']), array_keys($partyNameParts)))
					{
						$partyNameParts[strtolower($namePart['type'])][] = trim($namePart);
					}
					else
					{
						$partyNameParts['user_specified_type'][] = trim($namePart);
					}
					
					
				}
				
				// Now form up the display title according to the ordering rules
				$display_title = 	trim((count($partyNameParts['title']) > 0 ? implode(" ", $partyNameParts['title']) . " " : "") .
									(count($partyNameParts['given']) > 0 ? implode(" ", $partyNameParts['given']) . " " : "") .
									(count($partyNameParts['initial']) > 0 ? implode(" ", $partyNameParts['initial']) . " " : "") .
									(count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) . " " : "") .
									(count($partyNameParts['suffix']) > 0 ? implode(" ", $partyNameParts['suffix']) . " " : "") .
									(count($partyNameParts['user_specified_type']) > 0 ? implode(" ", $partyNameParts['user_specified_type']) . " " : ""));
						
				// And now the list title			
				// initials first, get a full stop
				foreach ($partyNameParts['given'] AS &$givenName)
				{
					$givenName = (strlen($givenName) == 1 ? $givenName . "." : $givenName);
				}
				foreach ($partyNameParts['initial'] AS &$initial)
				{
					$initial = $initial . ".";
				}

				$list_title = 	trim((count($partyNameParts['family']) > 0 ? implode(" ", $partyNameParts['family']) : "") .
								(count($partyNameParts['given']) > 0 ? ", " . implode(" ", $partyNameParts['given']) : "") .
								(count($partyNameParts['initial']) > 0 ? " " . implode(" ", $partyNameParts['initial']) : "") .
								(count($partyNameParts['title']) > 0 ? ", " . implode(" ", $partyNameParts['title']) : "") .
								(count($partyNameParts['suffix']) > 0 ? ", " . implode(" ", $partyNameParts['suffix']) : "") .
								(count($partyNameParts['user_specified_type']) > 0 ? " " . implode(" ", $partyNameParts['user_specified_type']) . " " : ""));
				
			
			}
			
			// Some length checking...
			if (strlen($display_title) > 255) { $display_title = substr($display_title,0,252) . "..."; }
			if (strlen($list_title) > 255) { $list_title = substr($list_title,0,252) . "..."; }
			
		}

		$this->ro->title = $display_title;
		$this->ro->list_title = $list_title;
		$this->ro->save();
	}
}
	
	