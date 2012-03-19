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
$gORCA_REGISTRY_OBJECT_WRAPPER .='                 xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects '.gRIF2_SCHEMA_URI.'">'."\n";	
$gORCA_REGISTRY_OBJECT_WRAPPER_END = '</registryObjects>';

$typeArray = array();
$typeArray['collection'] = array(
	"describes" => "Describes",
	"hasAssociationWith" => "Associated with",
	"hasCollector" => "Aggregated by",
	"hasPart" => "Contains",
	"isDescribedBy" => "Described by",
	"isLocatedIn" => "Located in",
	"isLocationFor" => "Location for",
	"isManagedBy" => "Managed by",
	"isOutputOf" => "Output of",
	"isOwnedBy" => "Owned by",
	"isPartOf" => "Part of",
	"supports" => "Supports"
);
$typeArray['party'] = array(
	"hasAssociationWith" => "Associated with",
	"hasMember" => "Has member",
	"hasPart" => "Has part",
	"isCollectorOf" => "Collector of",
	"isFundedBy" => "Funded by",
	"isFunderOf" => "Funds",
	"isManagedBy" => "Managed by",
	"isManagerOf" => "Manages",
	"isMemberOf" => "Member of",
	"isOwnedBy" => "Owned by",
	"isOwnerOf" => "Owner of",
	"isParticipantIn" => "Participant in",
	"isPartOf" => "Part of",
);
$typeArray['service'] = array(
	"hasAssociationWith" => "Associated with",
	"hasPart" => "Includes",
	"isManagedBy" => "Managed by",
	"isOwnedBy" => "Owned by",
	"isPartOf" => "Part of",
	"isSupportedBy" => "Supported by",
);
$typeArray['activity'] = array(
	"hasAssociationWith" => "Associated with",
	"hasOutput" => "Produces",
	"hasPart" => "Includes",
	"hasParticipant" => "Undertaken by",
	"isFundedBy" => "Funded by",
	"isManagedBy" => "Managed by",
	"isOwnedBy" => "Owned by",
	"isPartOf" => "Part of",
);	
// Slug defaults
define('NO_NAME_OR_TITLE_SLUG', 'no-nametitle');

?>