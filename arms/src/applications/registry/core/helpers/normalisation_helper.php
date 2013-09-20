<?php
/* These functions are intended to bv
/* Takes a RIFCS location/address element and formats it into a concatenated string format */
function normalisePhysicalAddress(SimpleXMLElement &$sxml)
{
	$address = "";
	if(isset($sxml->addressPart))
	{
		foreach ($sxml->addressPart AS $a)
		{
			$address .= (string) $a . NL;
		}
		$address = trim($address);
	}
	return $address;
}

/* Takes a RIFCS location/address element and formats it into an array of identifier elements */
function normaliseIdentifier(SimpleXMLElement &$sxml)
{
	$_orcidPrefix = "http://orcid.org/";
	$_nlaPrefix = "http://nla.gov.au/";

	if (!$sxml) { return ""; }

	if (strtolower($sxml['type']) == "orcid")
	{
		if (strpos($sxml, $_orcidPrefix) === FALSE)
		{
			return $_orcidPrefix . (string) $sxml;
		}
		else
		{
			return (string) $sxml;
		}
	}
	else if ($sxml['type'] == "AU-ANL:PEAU")
	{
		if (strpos($sxml, $_nlaPrefix) === FALSE)
		{
			return $_nlaPrefix . (string) $sxml;
		}
		else
		{
			return (string) $sxml;
		}
	}
	else if (in_array(strtolower($sxml['type']), array("uri","purl")))
	{
		return (string) $sxml;
	}
	else 
	{
		return (strtolower((string) $sxml['type']) . ": " . (string) $sxml);
	}

	return "";
}


/* Takes a RIFCS relatedInfo element and normalise it into a concatenated string  */
function normalisePublicationRelatedInfo(SimpleXMLElement &$sxml)
{
	if (!$sxml || $sxml['type'] != 'publication') { return ""; }
	$normalised_string = "";

	if (isset($sxml->title) && $sxml->title)
	{
		$normalised_string .= $sxml->title;
	}

	if (isset($sxml->identifier) && $sxml->identifier)
	{
		$normalised_string .= " <" . normaliseIdentifier($sxml->identifier) . ">";
	}

	if (isset($sxml->notes) && $sxml->notes)
	{
		$normalised_string .= " (" . $sxml->notes . ")";
	}

	return $normalised_string;
}