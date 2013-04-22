<?php
/**
 * [format_relationship description]
 * @param  [type] $from_class        [description]
 * @param  [type] $to_class          [description]
 * @param  [type] $relationship_type [description]
 * @param  [type] $reverse           [description]
 * @return [type]                    [description]
 */
function format_relationship($from_class, $relationship_type, $origin=false){
	$typeArray['collection'] = array(
		"describes" => array("Describes", "Described by"),
		"hasAssociationWith" => array("Associated with", "Associated with"),
		"hasCollector" => array("Aggregated by", "Collector of"),
		"hasPart" => array("Contains", "Part of"),
		"isDescribedBy" => array("Described by","Describes"),
		"isLocatedIn" => array("Located in", "Location for"),
		"isLocationFor" => array("Location for","Located in"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOutputOf" => array("Output of","Outputs"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isPartOf" => array("Part of","Contains"),
		"supports" => array("Supports", "Supported by"),
		"enriches" =>array("Enriches", "Enriched by"),
		"isEnrichedBy" =>array("Enriched by", "Enriches"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isPresentedBy" =>array("Presented by", "Presents"),
		"presents" =>array("Presents", "Presented by"),
		"isDerivedFrom" =>array("Derived from", "Derives"),
		"hasDerivedCollection" =>array("Derives", "Derived From"),
		"supports" =>array("Supports", "Supported by"),	
		"isAvaiableThrough" =>array("Available through", "Makes available"),	
		"isProducedBy" =>array("Produced by", "Produces"),
		"produces" =>array("Produces", "Produced by"),
		"isOperatedOnBy" =>array("Operated on", "Operates on"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
		"hasValueAddedBy" =>array("Value added by", "Adds value"),
	);
	$typeArray['party'] = array(
		"hasAssociationWith" => array("Associated with", "Associated with"),
		"hasMember" => array("Has member", "Member of"),
		"hasPart" => array("Has part", "Part of"),
		"isCollectorOf" => array("Collector of","Collected by"),
		"isFundedBy" => array("Funded by","Funds"),
		"isFunderOf" => array("Funds","Funded by"),
		"isManagedBy" => array("Managed by","Manages"),
		"isManagerOf" => array("Manages","Managed by"),
		"isMemberOf" => array("Member of","Has memeber"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isOwnerOf" => array("Owner of", "Owned by"),
		"isParticipantIn" => array("Participant in","Part of"),
		"isPartOf" => array("Part of","Participant in"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isEnrichedBy" =>array("Enriched by", "Enriches"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigatorOf" =>array("Principal investigator of", "Principal investigator"),
	);
	$typeArray['service'] = array(
		"hasAssociationWith" =>  array("Associated with", "Associated with"),
		"hasPart" => array("Includes", "Part of"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isPartOf" => array("Part of","Has part"),
		"isSupportedBy" => array("Supported by","Supports"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"isPresentedBy" =>array("Presented by", "Presents"),
		"presents" =>array("Presents", "Presented by"),
		"produces" =>array("Produces", "Produced by"),
		"isProducedBy" =>array("Produced by", "Produces"),
		"operatesOn" =>array("Operates on", "Operated by"),
		"isOperatedOnBy" =>array("Operated on", "Operates on"),		
		"addsValueTo" =>array("Adds value to", "Value added by"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
	);
	$typeArray['activity'] = array(
		"hasAssociationWith" =>   array("Associated with", "Associated with"),
		"hasOutput" => array("Produces","Output of"),
		"hasPart" => array("Includes","Part of"),
		"hasParticipant" => array("Undertaken by","Has participant"),
		"isFundedBy" => array("Funded by","Funds"),
		"isManagedBy" => array("Managed by","Manages"),
		"isOwnedBy" => array("Owned by","Owns"),
		"isPartOf" => array("Part of","Includes"),
		"enriches" =>array("Enriches", "Enriched by"),
		"makesAvailable" =>array("Makes available", "Available through"),
		"hasPrincipalInvestigator" =>array("Principal investigator", "Principal investigator of"),
		"isPrincipalInvestigator" =>array("Principal investigator of", "Principal investigator"),
	);	
	if($origin!='EXPLICIT' && $origin!='CONTRIBUTOR'){//reverse
		return (isset($typeArray[$from_class][$relationship_type]) ? $typeArray[$from_class][$relationship_type][1] : $relationship_type);
	}else return (isset($typeArray[$from_class][$relationship_type]) ? $typeArray[$from_class][$relationship_type][0] : $relationship_type);
}

/*
 * escapeSolrValue
 * escaping sensitive items in a solr query
 */
function escapeSolrValue($string){
    //$string = urldecode($string);
    $match = array('\\','&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', '"', ';');
    $replace = array('\\\\','&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\*', '\\?', '\\:', '\\"', '\\;');
    $string = str_replace($match, $replace, $string);
    return $string;
}