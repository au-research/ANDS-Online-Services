<?php
/*
Copyright 2009 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*******************************************************************************/

// Registry Object Wrappers (XML)
$gORCA_REGISTRY_OBJECT_WRAPPER = '<?xml version="1.0"?>'."\n";
$gORCA_REGISTRY_OBJECT_WRAPPER .='<registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" '."\n";
$gORCA_REGISTRY_OBJECT_WRAPPER .='                 xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" '."\n";
$gORCA_REGISTRY_OBJECT_WRAPPER .='                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n";
$gORCA_REGISTRY_OBJECT_WRAPPER .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF_SCHEMA_URI.'">'."\n";	
$gORCA_REGISTRY_OBJECT_WRAPPER_END = '</registryObjects>';

$typeArray = array();
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
	"isOperatedOn" =>array("Operated on", "Operates on"),	
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
	"operatesOn" =>array("Operates on", "Operated by"),
	"addsValueTo" =>array("Adds value to", "Value added by"),
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
);	
// Slug defaults
define('NO_NAME_OR_TITLE_SLUG', 'no-nametitle');

?>