<?php

class ContributorData_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/* This should be a loader for classes in a seperate directory called "suggestors" 

		Workflow should be:

			- check if there is a file with the name of the suggestor in our suggestors directory
			- instantiate that class and pass it the reference to this registry object
			- have the logic for each suggestor in it's own file and class to avoid clutter
			- the suggester's ->suggest() method returns an array of suggested links (not sure what format this object should be in?)

	*/
	function getContributorData()
	{
		
	
		$contributorData['contents'] = array('collections'=>34,'activities'=>24,'services'=>33,'parties'=>24);
		$contributorData['subjects'] = array('subject 1'=>34,'subject 2'=>24,'subject 3'=>33,'subject 4'=>24);
		return $contributorData;

	}


}