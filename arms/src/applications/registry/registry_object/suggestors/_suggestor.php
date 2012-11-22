<?php

interface GenericSuggestor
{
	// Bare minimum necessary to suggest "links"
	public function getSuggestedLinksForString($query_string, $limit, $offset);
	public function getSuggestedLinksForRegistryObject($registry_object, $limit, $offset);

}