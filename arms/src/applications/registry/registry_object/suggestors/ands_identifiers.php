<?php

/**
 * This suggestor links records which are similar to the record suggested
 *
 * @return array of items which will be displayed as suggested links
 */
class Suggestor_ands_identifiers implements GenericSuggestor
{
	

	public function getSuggestedLinksForRegistryObject(_registry_object $registry_object, $start, $rows)
	{
		// First, get published records with the same identifier as us
		// Note: whilst we can use SOLR to get the linked-to records, 
		//       we shouldn't use SOLR to get our own information, as 
		//       this would mean that DRAFT requests fail (drafts NOT 
		// 		 in SOLR index).

		$sxml = $registry_object->getSimpleXML();

		// Identifier matches (if another object has the same identifier)
		$my_identifiers = array('');
		foreach($sxml->{strtolower($registry_object->class)}->identifier AS $identifier)
		{
			$my_identifiers[] = '"' . (string) $identifier . '"';
		}
		$identifier_search_query = implode(" +identifier_value:", $my_identifiers);

		// But exclude already related objects
		$my_relationships = array_map(function($elt){ return '"' . $elt . '"'; }, $registry_object->getRelationships());
		$my_relationships[] = $registry_object->key;
		array_unshift($my_relationships, ''); // prepend an element so that 
		$relationship_search_query = " " . implode(" -key:", $my_relationships);

		$suggestions = $this->getSuggestionsBySolrQuery($relationship_search_query . " AND " . $identifier_search_query, $start, $rows);

		return $suggestions;
	}



	private function getSuggestionsBySolrQuery($search_query, $start, $rows)
	{
		$CI =& get_instance();
		$CI->load->library('solr');
		$CI->solr->init();
		$CI->solr->setOpt("q", $search_query);
		$CI->solr->setOpt("start", $start);
		$CI->solr->setOpt("rows", $rows);
		$result = $CI->solr->executeSearch(true);
		$suggestions = array();

		if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0)
		{
			$links = array();

			foreach($result['response']['docs'] AS $doc)
			{
				$links[] = array("url"=>portal_url($doc['slug']),
								"title"=>$doc['display_title'],
								"class"=>$doc['class'],
								"slug"=>$doc['slug']);
			}

			$suggestions = array(
				"count" => $result['response']['numFound'],
				"links" => $links
			);
		}
		return $suggestions;
	}



	/* May be necessary for future use?? */
	public function getSuggestedLinksForString($query_string, $start, $rows)
	{
		return array();
	}


}