<?php

/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_ands_links implements GenericSuggestor
{
	
	public function getSuggestedLinksForRegistryObject($registry_object, $start, $rows)
	{
		$CI =& get_instance();
		return array("XXX: TODO: This is from ANDS suggested links for RO ID: " . $registry_object->id);
	}



	/* May be necessary for future use?? */
	public function getSuggestedLinksForString($query_string, $start, $rows)
	{
		return array();
	}

}