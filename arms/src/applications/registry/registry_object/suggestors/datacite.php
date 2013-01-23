<?php

/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_datacite implements GenericSuggestor
{

	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $start, $rows)
	{
		$CI =& get_instance();
		return array("XXX: TODO: This is from Datacite suggested links for RO ID: " . $registry_object->id);
	}



	/* May be necessary for future use?? */
	public function getSuggestedLinksForString($query_string, $start, $rows)
	{
		return array();
	}

}