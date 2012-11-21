<?php

class Suggestedlinks_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/* XXX: This should be a loader for classes in a seperate directory called "suggestors" 

		Workflow should be:

			- check if there is a file with the name of the suggestor in our suggestors directory
			- instantiate that class and pass it the reference to this registry object
			- have the logic for each suggestor in it's own file and class to avoid clutter
			- the suggester's ->suggest() method returns an array of suggested links (not sure what format this object should be in?)

	*/
	function getSuggestedLinks($suggestor, $start=0, $rows=20)
	{
		$suggested_links = array();
		if (method_exists($this,"_suggest_".$suggestor))
		{
			$suggested_links = $this->{"_suggest_" . $suggestor}($start, $rows);
		}
		else
		{
			throw new Exception("Unsupported Suggestor in suggestedlinks.php");
		}

		return $suggested_links;
	}
	

	function _suggest_ands_links($start=0, $rows=20)
	{
		return array();
		// XXX: TODO: do some stuff here to get ANDS suggested links... (SEE ABOVE FOR REFACTOR)
	}

	function _suggest_datacite($start=0, $rows=20)
	{
		return array();
		// XXX: TODO: do some stuff here...  (SEE ABOVE FOR REFACTOR)
	}
}