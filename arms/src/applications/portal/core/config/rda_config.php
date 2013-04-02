<?php

// The endpoint on the registry from which data is retrieved
$config['registry_endpoint'] =  dirname(base_url()) . "/registry/services/rda/";

$config['topics_datafile'] =  dirname(base_url()) . "/registry/assets/topics/topics.json";

$config['banned_images'] = array(
	'http://services.ands.org.au/documentation/logos/nhmrc_stacked.jpg'
);


//======================================
// SUBJECT CATEGORIES for FACET SEARCHING
$config['subjects_categories'] = array(
	'keywords' 
		=> array(
			'display' => 'Keywords',
			'list'=> array('anzlic-theme', 'australia', 'caab', 'external_territories', 'cultural_group', 'DEEDI eResearch Archive Subjects', 'ISO Keywords', 'iso639-3', 'keyword', 'Local', 'local', 'marlin_regions', 'marlin_subjects', 'ocean_and_sea_regions', 'person_org', 'states/territories', 'Subject Keywords')
			),
	'scot' 
		=> array(
			'display' => 'Schools of Online Thesaurus',
			'list' => array('scot')
			),
	'pont' 
		=> array(
			'display' => 'Powerhouse Museum Object Name Thesaurus',
			'list' => array('pmont', 'pont')
			),
		
	'psychit' 
		=> array(
			'display' => 'Thesaurus of psychological index terms',
			'list' => array('Psychit', 'psychit')
			),
	'anzsrc' 
		=> array(
			'display' => 'ANZSRC',
			'list' => array('ANZSRC', 'anzsrc', 'anzsrc-rfcd', 'anzsrc-seo', 'anzsrc-toa')
			),
	'apt' 
		=> array(
			'display' => 'Australian Pictorial Thesaurus',
			'list' => array('apt')
			),
	'gcmd' 
		=> array(
			'display' => 'GCMD Keywords',
		'list' => array('gcmd')
			),
	'lcsh' 
		=> array(
			'display' => 'LCSH',
			'list' => array('lcsh')
			)
		
);